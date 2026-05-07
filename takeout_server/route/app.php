<?php

use think\facade\Route;

// 404 page
Route::miss('index/pageNotFound');

// home page
Route::get('', 'index/index');

// phpinfo
Route::get('phpinfo_fuckyou', 'index/phpinfo');
