<?php
namespace app\index\controller;

use app\index\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return "app\index\controller";
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }

    public function say($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
