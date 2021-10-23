<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;



class Index extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }
    
    /**
     * 显示控制台
     */
    public function index()
    {
       $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

       //根据权限获取所有菜单
       
       $data_list = Db::name('yphp_admin_power')->where("pstatus",1)->where("ptype",1)->order('porder', 'desc')->select()->toArray();
        foreach ($data_list as $key => $val) 
        {
            //获取二级菜单
            $data_list[$key]['child'] = Db::name('yphp_admin_power')->where("pstatus",1)->where("parent_id",$val['id'])->where("ptype",2)->order('porder', 'desc')->select()->toArray();
            
        }
        $return_data['power_list'] = $data_list;

        //获取用户权限
        $return_data['powers_arr'] = array();
        if($this->admin_info['admin_role_id'] == 0)
        {
            $return_data['powers'] = 'all';
        }
        else
        {
            $role_powers = Db::name('yphp_admin_role')->where("role_id",$this->admin_info['admin_role_id'])->value('role_powers');
            if($role_powers == 'all')
            {
                $return_data['powers'] = 'all';
            }
            else
            {
                $return_data['powers'] = 'custom';
                $return_data['powers_arr'] = explode(",",$role_powers);
            }
        }
        

       return view("index/index",$return_data);
    }

    
    public function welcome()
    {

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id,
            'think_version'=>App::version(),
        );

       return view("index/welcome",$return_data);
    }

    //退出登录
    public function loginout()
    {
        //记录客户退出登录
        $this->adminModel->addLoginLog($this->admin_id,3);
        //清空session 和 cookie 
        session(null);
        cookie('admin_id',null);
        cookie('admin_shell',null);

        return redirect('/admin/login/index');
    }

    //修改密码
    public function changePass()
    {
        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

       return view("admin/change_password",$return_data);
    }
    //修改密码操作
    public function changePassAct()
    {
       $data = request()->param();

       if ($data['pass'] != $data['repass'])  return json(array('status'=>'FAIL','msg'=>'两次密码输入不一致！'));
       if (empty($data['pass']))  return json(array('status'=>'FAIL','msg'=>'密码不能为空!'));



       $res = $this->adminModel->changePassAct($this->admin_id,$data['pass']);
       return json($res);
    }

     /**
     * 管理员个人信息
     */
    public function profile()
    {
        
        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

       return view("admin/profile",$return_data);
    }
    
}
