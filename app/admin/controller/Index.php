<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;



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
            'admin_id'   => $this->admin_id
        );

       return view("index/welcome",$return_data);
    }

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

    //添加管理员
    public function addAdmin()
    {
        $add_data = array(
            'admin_name' => 'admin01', 
            'admin_pwd'  => password_hash("a123456",PASSWORD_DEFAULT),
            'admin_truename' => '管理员01',
            'admin_mobile'   => '13888888888',
            'admin_dept'     => '技术部',
            'admin_role_id'  => 0,
            'admin_role_name'=> '超级管理员',
            'add_datetime'   => date("Y-m-d H:i:s")
        );

        $userId = Db::name('yphp_admin')->insertGetId($add_data);

        echo $userId;

        
    }

    
}
