<?php

namespace app\common;

use app\BaseController;
use app\controller\ApiResponse;
use app\middleware\TokenValidation;
use think\exception\ValidateException;

class CoreController extends BaseController
{
    /**
     * Store the root path of the current site
     * 
     * @var string
     */
    private static $root;

    protected $middleware = [TokenValidation::class];

    /**
     * 验证器具
     * 
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * 
     * @return void
     */
    public function validator($data, $validate)
    {
        try {
            $this->validate($data, $validate);
        } catch (ValidateException $e) {
            ApiResponse::fail([], 1002, $e->getError());
        }
    }

    /**
     * 获取用户的ip地址
     *
     * @datetime 2024-04-10
     * @return string
     */
    public function getUserIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $userIP = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $userIP = $_SERVER['REMOTE_ADDR'];
        }
        return $userIP;
    }

    /**
     * Get the root path of the current site and remove index.php
     */
    public static function getRoot()
    {
        if (self::$root === null) {
            self::$root = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }
        return self::$root;
    }
}
