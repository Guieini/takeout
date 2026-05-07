<?php

namespace app\controller\api;

use app\common\CoreController;
use app\controller\ApiResponse;
use app\service\api\UserService;

class UserController extends CoreController
{
    /**
     * The current service object
     *
     * @var object
     */
    protected $currentService;

    /**
     * Initialize the current service object
     */
    public function initialize()
    {
        parent::initialize();
        $this->currentService = new UserService();
    }

    /**
     * Common entry method
     *
     * @return void
     */
    public function anyAction($id = null)
    {
        $mehod = $this->request->method();
        switch ($mehod) {
            case 'GET':
                $this->get();
                break;
            case 'POST':
                $this->add();
                break;
            case 'PUT':
                $this->update();
                break;
            case 'DELETE':
                if ($id) {
                    $this->delete($id);
                } else {
                    ApiResponse::fail([], 1001, 'The id is required');
                }
                break;
        }
        ApiResponse::success([]);
    }

    /**
     * Get method for getting page data
     */
    public function get()
    {
        $params = $this->request->param();
        $this->validator($params, 'User.get');
        $result = $this->currentService->get($params);
        ApiResponse::success($result);
    }

    /**
     * Adding a new data method
     */
    public function add()
    {
        $data = $this->request->param();
        $this->validator($data, 'User.add');
        $result = $this->currentService->add($data);
        ApiResponse::success($result);
    }

    /**
     * Update data method
     */
    public function update()
    {
        $data = $this->request->param();
        $this->validator($data, 'User.update');
        $result = $this->currentService->update($data);
        ApiResponse::success([]);
    }

    /**
     * Delete data method
     */
    public function delete($id)
    {
        $data['id'] = $id;
        $result = $this->currentService->delete($data);
        ApiResponse::success($result);
    }

    /**
     * 刷新密码，全部恢复成默认的密码（仅初始化以及修改密码盐之后使用）
     * URL: /api/admin/refreshPassword
     *
     * @datetime 2024-04-26
     */
    public function refreshPassword($id = null)
    {
        $mark = $this->request->header('SpecialMark');
        if ($mark !== 'refreshPassword') {
            ApiResponse::fail([], 1001, 'illegal operation');
        }
        $countUser = $this->currentService->refreshPassword($id);
        ApiResponse::success(['count' => $countUser]);
    }

    /**
     * get user info
     * URL: /api/admin/info
     *
     * @datetime 2024-04-22
     */
    public function info()
    {
        // $params = $this->request->param();
        // $result = $this->currentService->info($params);
        $result = [
            'username' => 'admin',
            'roles' => ['admin'],
        ];
        ApiResponse::success($result);
    }
}
