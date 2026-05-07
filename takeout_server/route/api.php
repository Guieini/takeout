<?php

use think\facade\Route;

Route::get('api/test', function () {
    return 'hello,ThinkPHP8!';
});

// database init
// Route::get('api/db/init', 'api.database/index');

/**
 * --------------------------------------------------------------------------------
 * 微信公众号接口
 * --------------------------------------------------------------------------------
 */

// 微信公众号主入口
Route::get('wechat/token', 'api.wechatMsg/valid');

// response message
Route::post('wechat/token', 'api.wechatMsg/responseMsg');

// refresh users info from wechat
// 直接获取微信的关注列表
Route::get('api/wechat/users', 'api.wechat/getWechatUsers');

// 查询菜单
Route::get('api/wechat/menu', 'api.wechat/getMenu');
// 创建菜单
Route::post('api/wechat/menu', 'api.wechat/createMenu');

// 获取素材列表
Route::post('api/wechat/material', 'api.wechat/getMaterial');

// 获取草稿列表
Route::get('api/wechat/draft/[:media_id]', 'api.wechat/getDraft');

// 新建草稿
Route::post('api/wechat/draft', 'api.wechat/createDraft');

// 发布接口
Route::post('api/wechat/publish', 'api.wechat/publish');

// 群发接口
Route::post('api/wechat/mass', 'api.wechat/mass');

// 定时任务创建草稿并发布
Route::get('api/schedule/publish', 'api.schedule/publish');

// 定时任务创建草稿并发布
Route::get('/api/create', 'api.createMenu/create');

// update user info from wechat
// 可以考虑用来给单个用户刷新微信的数据
// Route::post('api/wechat/user', 'api.wechat/updateUserInfo');

// update users info from wechat
// 可以考虑用来批量给用户刷新微信的数据
// Route::post('api/wechat/users', 'api.wechat/updateUsersInfo');

/**
 * --------------------------------------------------------------------------------
 * 后台管理系统接口
 * --------------------------------------------------------------------------------
 */
// 刷新所有用户的密码为默认值
Route::get('api/admin/refreshPassword/[:id]', 'api.user/refreshPassword');

// 用户登陆
Route::post('api/admin/login', 'api.login/login');
// 获取用户权限等信息
Route::get('api/admin/info', 'api.user/info');
// 获取用户列表
Route::get('api/admin/user', 'api.user/anyAction');
// 新增更新及删除用户
Route::post('api/admin/user', 'api.user/anyAction');
Route::put('api/admin/user', 'api.user/anyAction');
Route::delete('api/admin/user/:id', 'api.user/anyAction');
