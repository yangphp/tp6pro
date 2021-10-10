<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


//最简单的路由 闭包路由
Route::get('hello$', function() {
    return 'Hello,ThinkPHP';
});

//闭包定义参数
Route::get('hello/:name$', function($name) {
    return 'Hello,' . $name;
});


//路由到控制器方法
Route::get('tr001/:name', 'Troute/t001');

//可选参数
Route::get('tr002/[:name]', 'Troute/t002');

//重定向路由
Route::redirect('tr003', 'https://www.kancloud.cn/thinkphp/thinkphp6-quickstart/1352495', 301);

//路由到模板,以及路由传参
Route::view('tr004', 'index/tr004',['name'=>'yangphp','age'=>30]);

//路由分组
Route::group('troute', function() {
    Route::get('/tr005', 'Troute/t005');
    Route::get('/tr006', 'Troute/t006');
})->ext('html')->pattern(['id' => '\d+']);

//生成路由
Route::get('tr007', 'Troute/t007');