<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('checkdate', 'CommonController@checkdate');


// 发送短信验证码
Route::post('sendSms', 'CommonController@sendSms');

// 获取验证码
Route::post('sms', 'CommonController@getSms');

// Auth路由
Route::group(['namespace' => 'Auth'], function()
{
    // 用户登录
    Route::post('login', 'LoginController@login');
    // 获取登陆用户角色信息
    Route::get('user/info', 'LoginController@getUserInfo');
    // 验证手机号是否注册
	Route::post('/check/phone', 'LoginController@checkPhone');
    // 修改密码
    Route::post('/changepwd', 'ForgotPasswordController@changepwd');
});

// 需要jwt.token验证 role:admin验证
Route::group(['middleware' => ['jwt.auth','role:admin'], 'providers' => 'jwt'], function (){
	// 获取用户列表
	Route::get('/users', 'UserController@list');
	// 获取用户信息
	Route::get('/users/{userId}', 'UserController@show');
	// 编辑用户
	Route::put('/users/{userId}/edit', 'UserController@edit');
	// 重置密码
	Route::put('/users/{userId}/reset', 'UserController@reset');
	// 获取用户登录日志
	Route::get('/users/{userId}/logs/login', 'UserController@loginLogs');
	// 获取用户操作日志
	Route::get('/users/{userId}/logs/operation', 'UserController@profileLogs');
	// 升级用户账号
	Route::put('/users/{userId}/upgrade', 'UserController@upgrade');

	// 获取单位列表
	Route::get('/organizations', 'OrganizationController@list');
	// 编辑单位
	Route::put('/organizations/{organizationId}', 'OrganizationController@edit');

	// 获取项目列表
	Route::get('/projects', 'ProjectController@projects');
	// 获取项目详情
	Route::get('/projects/{projectId}', 'ProjectController@details');
	// 编辑项目信息
	Route::put('/projects/{projectId}', 'ProjectController@edit');

});