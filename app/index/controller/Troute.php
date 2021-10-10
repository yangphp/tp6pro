<?php
namespace app\index\controller;

use app\BaseController;

class Troute extends BaseController
{
    public function index()
    {
        return "app\index\controller\Troute.php";
    }

    public function t001($name)
    {
        return 'hello this is Troute class t001 action ,name = '.$name;
    }
   
     public function t002($name='')
    {
        return 'hello this is Troute class t002 action ,name = '.$name;
    }

     public function t005()
    {
        return 'hello this is Troute class t005 action ';
    }

     public function t006()
    {

        return 'hello this is Troute class t006 action ';
    }

    public function t007()
    {
        echo "生成url不含域名<br />";
        echo  url('index/hello', ['name' => 'thinkphp' , 'extra' => 'test']);
        echo "<br />";

        echo "生成url含域名<br />";
        echo  url('index/hello', ['name' => 'thinkphp' , 'extra' => 'test'])->domain(true);
        echo "<br />";
 
    }


}
