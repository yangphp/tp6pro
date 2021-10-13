<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\captcha\facade\Captcha;
use app\admin\model\AdminModel;
use think\facade\View;

class Login
{

    //显示登录模板
    public function index()
    {

        $view_data['admin_title'] = "yangphp管理后台";
        
        return view("login",$view_data);
    }

    //登录操作
    public function loginAct()
    {
        //获取前端传入参数
        $data = request()->param();

        // 检测输入的验证码是否正确
        if( !captcha_check($data['vercode']))
        {
            return json(array('status'=>'FAIL','msg'=>'验证输入错误'));
        }

        //登录验证
        $adminModel = new AdminModel();

        $res = $adminModel->checkLogin($data);
        return json($res);
    }

    //验证码
    public function loginYzm()
    {
         return Captcha::create(); 
    }
}
