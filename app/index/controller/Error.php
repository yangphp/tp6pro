<?php
declare (strict_types = 1);

namespace app\index\controller;

class Error 
{
    public function __call($method, $args)
    {
        return 'Error，您请求的控制器找不到!';
    }
}
