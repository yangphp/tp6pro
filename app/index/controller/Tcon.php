<?php
namespace app\index\controller;

use app\BaseController;

/**
 * 控制器测试类
 */
class Tcon extends BaseController
{
    public function index()
    {
        return "app\index\controller\Tcon 控制器";
    }

    public function thalt()
    {
        halt(' 这是从halt助手函数中输出的内容');

        return 'test halt 调试';
    }

    public function tbase()
    {

        echo $action = $this->request->action();
        echo "<br />";
        echo $path = $this->app->getBasePath();

    }
}
