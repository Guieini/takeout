<?php

namespace app\service\api;

use app\controller\ApiResponse;
use app\service\CoreService;

class LoginService extends CoreService
{
    public function initialize()
    {
        $this->currentModel = new \app\model\UserModel();
    }

    public function login($params)
    {
        // check if the user exists
        $username = $params['username'];
        $userQuery = $this->currentModel->where('username', $username)->field(['id', 'username', 'password'])->find();
        if (empty($userQuery)) {
            ApiResponse::fail([], 1100);
        }
        $encryPassword = (new \app\common\Common())->encryptPassword($params['password']);
        if ($encryPassword != $userQuery['password']) {
            ApiResponse::fail([], 1100);
        }
        return ['token' => 'token-admin'];;
    }
}
