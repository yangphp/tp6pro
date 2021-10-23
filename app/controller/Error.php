<?php
declare (strict_types = 1);

namespace app\controller;

class Error 
{
    public function __call($method, $args)
    {

        if(request()->url() == "/admin/")
        {
          echo "<script type='text/javascript'>window.location='/admin/index/index.html'</script>";
        }

        return 'Error，您请求的控制器找不到!';
    }
}
