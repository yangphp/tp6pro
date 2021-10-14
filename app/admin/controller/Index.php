<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;



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
