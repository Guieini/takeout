<?php

namespace app\controller\api;

use app\common\CoreController;
use app\controller\ApiResponse;
use app\service\api\LoginService;

class LoginController extends CoreController
{
    /**
     * The current service object
     *
     * @var object
     */
    protected $currentService;

    // rewrite middleware avoid to validate token
    protected $middleware = [];

    /**
     * Initialize the current service object
     */
    public function initialize()
    {
        parent::initialize();
        $this->currentService = new LoginService();
    }

    /**
     * get user info
     * URL: /api/admin/login
     *
     * @datetime 2024-04-22
     */
    public function login()
    {
        $params = $this->request->param();
        $this->validator($params, 'User.login');
        $result = $this->currentService->login($params);
        ApiResponse::success($result, '登录成功');
    }
}
