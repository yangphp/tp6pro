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


/*

Route::get('hello/:name', 'index/hello');
*/

// 注册路由到Url控制器的t001操作
Route::rule('url001','Url/t001');

// 必选变量示例
Route::rule('url002/:name','Url/t002');

// 可选变量示例
Route::rule('url003/[:name]','Url/t003');

// 额外参数示例
Route::get('url004/:id','Url/t004')
    ->append(['status' => 1, 'app_id' =>5]);

//路由标识
Route::rule('url005/:name','Url/t005')
    ->name('url5');

//路由到模板 传参数会有bug
Route::view('url006','url/t006');

#必须安装视图引擎
#composer require topthink/think-view

//重定向路由 多应用情况下无效
Route::redirect('blog/:id', 'http://blog.thinkphp.cn/read/:id', 302);

//路由到闭包
Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});