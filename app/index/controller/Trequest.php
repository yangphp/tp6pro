<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Request;

/**
 * 请求对象测试
 */
class Trequest extends BaseController
{
    public function index()
    {
        //静态调用
        $username = Request::param('name');
       
       //助手函数
       $username = request()->param('name');


    }

    public function show()
    {
        echo "获取完整URL地址 不带域名：<br />";
        echo Request::url();
        echo "<br />";

        echo "获取完整URL地址 带域名：<br />";
        echo Request::url(true);
        echo "<br />";

        //获取当前控制器--获取不到什么鬼
        echo Request::controller(true);

        //获取当前操作--获取不到什么鬼
        echo request()->action();
    }

   
}
