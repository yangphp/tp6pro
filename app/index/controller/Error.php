<?php
namespace app\index\controller;

use app\BaseController;

/**
 * 空控制器
 */
class Error extends BaseController
{
    public function __call($method, $args)
    {
        //$method   string 当前访问的操作方法名称
        //$args     array  当前访问的参数
        return " Controller Not Found  !";
    }
}
