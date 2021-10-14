<?php
namespace app\admin\model;

use think\Model;
use think\facade\Db;

class AdminModel extends Model
{
	protected $name = 'yphp_admin';
	protected $pk 	= 'admin_id';

	// 模型初始化
    protected static function init()
    {
        //TODO:初始化内容
    }

    //插入数据
    public function addItem($data)
    {
        $data['admin_pwd'] 	= password_hash($data['pass'],PASSWORD_DEFAULT);
        $data['add_datetime'] = date("Y-m-d H:i:s");

        //判断账号是否重复
        $item = $this->where("admin_name",$data['admin_name'])->find();
        if (!empty($item)) 
        {
            return array('status'=>'FAIL','msg'=>'该管理员账号已存在，请重新输入');
        }

        $admin_id = $this->strict(false)->insertGetId($data);

    	return array('status'=>'SUCCESS','msg'=>'添加管理员成功','data'=>array('admin_id'=>$admin_id));
    }
    //修改数据
     public function updateItem($data)
    {   
        if (!empty($data['pass'])) 
        {
            $data['admin_pwd']  = password_hash($data['pass'],PASSWORD_DEFAULT);
        }
        $data['admin_id'] = $data['s_admin_id'];
        //判断账号是否重复
        $item = $this
            ->where("admin_name",$data['admin_name'])
            ->where("admin_id","<>",$data['admin_id'])
            ->find();

        if (!empty($item)) 
        {
            return array('status'=>'FAIL','msg'=>'该管理员账号已存在，请重新输入');
        }

        $admin_id = $this->strict(false)->update($data);

        return array('status'=>'SUCCESS','msg'=>'修改管理员成功');
    }

    /**
     * 管理员登录验证
     */
    public function checkLogin($data)
    {
    	//判断账号是否存在
    	$admin = $this->where("admin_name",$data['username'])->findOrEmpty();
    	if (empty($admin)) 
    	{
    		return array('status'=>'FAIL','msg'=>'登录失败,账号或密码错误！');
    	}

    	//判断密码是否正确
    	if (!password_verify($data['password'], $admin['admin_pwd'])) 
    	{
    		//记录登录日志
    		$this->addLoginLog($admin['admin_id'],2);

    		return array('status'=>'FAIL','msg'=>'登录失败,账号或密码错误！');
    	}
    	//判断账号状态
    	if($admin['admin_status'] == 2)
    	{
    		return array('status'=>'FAIL','msg'=>'登录失败,账号已被限制登录！');
    	}
    	elseif ($admin['admin_status'] == 3) 
    	{
    		return array('status'=>'FAIL','msg'=>'登录失败,账号已被冻结！');
    	}

    	//判断当天是否登录错误超过10次
    	$today = date("Y-m-d");

    	$fail_num = Db::table('yphp_admin_login')
    	->where("admin_id",$admin['admin_id'])
        ->where("login_status",2)
    	->whereLike("add_datetime",$today."%")->count();

    	if ($fail_num >= 10) {
    		return array('status'=>'FAIL','msg'=>'登录失败,当日错误次数超限！');
    	}

    	//记录登录日志
    	$this->addLoginLog($admin['admin_id'],1);

    	//记录Session 和 Cookie
    	session('admin_id', $admin['admin_id']);
    	session('admin_name', $admin['admin_name']);
    	session('admin_shell', md5("LJAF&AFA".$admin['admin_id'].$admin['admin_pwd']));
    	//保存密码，则保存cookie 15天 
    	if($data['remember'] == 1)
    	{
    		cookie('admin_id', $admin['admin_id'], 1296000);
    		cookie('admin_name', $admin['admin_name'], 1296000);
    		cookie('admin_shell', md5("LJAF&AFA".$admin['admin_id'].$admin['admin_pwd']), 1296000);
    	}

    	return array('status'=>'SUCCESS','msg'=>'登录成功！');
    }

    /**
     * 管理员登录验证
     */
    public function checkLoginStatus($admin_id,$admin_shell)
    {
    	$admin = $this->where("admin_id",$admin_id)->findOrEmpty();

    	if ($admin_shell == md5("LJAF&AFA".$admin['admin_id'].$admin['admin_pwd'])) {

    		return array('status'=>'SUCCESS','msg'=>'已登录');
    	}
    	else
    	{
    		return array('status'=>'FAIL','msg'=>'登录验证失败，请重新登录！');
    	}
    }

    /**
     * 修改密码
     */
    public function changePassAct($admin_id,$pass)
    {
        if (strlen($pass)<6 || strlen($pass) > 16) 
        {
            return array('status'=>'FAIL','msg'=>'修改失败：密码长度需控制在6-16个字符');
        }
        
        $admin_info = $this->where("admin_id",$admin_id)->find();
        if (password_verify($pass,$admin_info['admin_pwd'])) 
        {
            return array('status'=>'FAIL','msg'=>'修改失败：新密码必须和原密码不同');
        }

        $pass  = password_hash($pass,PASSWORD_DEFAULT);
        $update_arr = array('admin_pwd'=>$pass);
        $res = $this->where("admin_id",$admin_id)->update($update_arr);

        if ($res > 0) {
            return array('status'=>'SUCCESS','msg'=>'修改密码成功');
        }else{
            return array('status'=>'FAIL','msg'=>'修改密码失败');
        }

    }

   
    /**
     * 写入登录日志
     * @param [type] $admin_id     [description]
     * @param [type] $login_status  
     */
    public function addLoginLog($admin_id,$login_status)
    {
    	//当天超过10次登录失败，则当天禁止登录
    	$add_arr = array(
    		'admin_id' 	=> intval($admin_id),
    		'login_status'	=> $login_status,
    		'login_ip'		=> get_ip(),
    		'add_datetime'	=> date("Y-m-d H:i:s")
    	);

    	Db::table('yphp_admin_login')->insert($add_arr);
    }

     /**
     * 操作日志
     * @param [type] $admin_id     [description]
     * @param [type] $login_status  
     */
    public function addOpLog($admin_info)
    {
        $add_arr = array(
            'admin_id'      => intval($admin_info['admin_id']),
            'admin_name'    => $admin_info['admin_name'],
            'op_url'        => request()->url(),
            'op_param'      => json_encode(request()->param()),
            'op_controller' => request()->controller(),
            'op_action'     => request()->action(),
            'ip_address'    => get_ip(),
            'add_datetime'  => date('Y-m-d H:i:s')
        );

        Db::table('yphp_admin_op_log')->insert($add_arr);
    }

}