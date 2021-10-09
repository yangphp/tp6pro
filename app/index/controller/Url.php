<?php
namespace app\index\controller;

use app\index\BaseController;

class Url extends BaseController
{
    public function index()
    {
        return "app\index\controller\url";
    }

    public function t001()
    {
        return 'hello this is Url class t001 action ';
    }
    //必选参数示例
    public function t002($name = 'ThinkPHP6')
    {
        return 'hello this is Url class t002 action with name =  '.$name;
    }
    //可选参数示例
    public function t003($name = 'ThinkPHP6')
    {
        return 'hello this is Url class t003 action with name =  '.$name;
    }
    //额外参数示例
     public function t004($id,$status,$app_id)
    {
        echo "status:".$status."<br />";
        echo "app_id:".$app_id."<br />";
        return 'hello this is Url class t004 action with id =  '.$id;
    }

    //路由标识
     public function t005($name)
    {
        echo url('url5',['name'=>$name]);
        echo "<br />";

        return url('Url/t005',['name'=>$name]);
    }






}
