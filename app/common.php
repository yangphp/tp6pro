<?php
// 应用公共文件
function get_ip(){
    $forwarded = request()->header("x-forwarded-for");
    if($forwarded){
        $ip = explode(',',$forwarded)[0];
    }else{
        $ip = request()->ip();
    }
    return $ip;
}