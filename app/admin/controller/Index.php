<?php
namespace app\admin\controller;

use app\admin\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return "app\admin\controller";
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
