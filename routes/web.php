<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('/login/login','login\LoginController@login');
Route::any('/login/do_login','login\LoginController@do_login');

//----------------------------微信扫码登录

Route::any('/login/wechatlogin','login\LoginController@wechatlogin');
Route::any('/login/do_wechatlogin','login\LoginController@do_wechatlogin');



Route::group(['middleware'=>['Login']],function(){
    Route::any('/admin/index','admin\AdminController@index');
    Route::any('/login/logout','login\LoginController@logout');
});

//----------------------------配置微信的接入

Route::any('/wechat/index','wechat\WechatController@index');

//获取二维码
Route::any('/code/code','code\CodeController@code');




