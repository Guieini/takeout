<?php

namespace app\controller\api;

use app\common\CoreController;
use app\controller\ApiResponse;
use app\service\api\WechatService;
use think\facade\Log;

class WechatController extends CoreController
{
    protected $wechatService;
    public function initialize()
    {
        parent::initialize();
        $this->wechatService = new WechatService();
    }

    /**
     * 获取微信用户列表并存入数据库表
     * URL: /api/wechat/users
     *
     * @datetime 2024-04-18
     */
    public function getWechatUsers()
    {
        $result = $this->wechatService->getWechatUsesrs();
        ApiResponse::success();
    }

    /**
     * 创建微信菜单
     * URL: /api/wechat/menu
     * Method: GET
     *
     * @datetime 2024-04-18
     */
    public function getMenu()
    {
        $result = $this->wechatService->getMenu();
        ApiResponse::success($result);
    }

    /**
     * 获取素材列表
     * URL: /api/wechat/material
     * Method: POST
     *
     * @datetime 2024-04-23
     * @return void
     */
    public function getMaterial()
    {
        $data = $this->request->param();
        $result = $this->wechatService->getMaterial($data);
        ApiResponse::success($result);
    }

    /**
     * 获取草稿列表
     * URL: /api/wechat/draft
     * or URL: /api/wechat/draft/:media_id
     * Method: GET
     *
     * @datetime 2024-11-19
     * @return void
     */
    public function getDraft($media_id = null)
    {
        if ($media_id) {
            $result = $this->wechatService->getDraftById($media_id);
        } else {
            $result = $this->wechatService->getDraft();
        }
        ApiResponse::success($result);
    }

    /**
     * 新建草稿
     * URL: /api/wechat/draft
     * Method: POST
     * 
     * @datetime 2024-11-19
     * @return void
     */
    public function createDraft()
    {
        $data = $this->request->param();
        $result = $this->wechatService->createDraft($data);
        ApiResponse::success($result);
    }

    /**
     * 发布接口
     * URL: /api/wechat/publish
     * Method: POST
     * 
     * @datetime 2024-11-19
     * @return void
     */
    public function publish()
    {
        $data = $this->request->param();
        $result = $this->wechatService->publish($data);
        ApiResponse::success($result);
    }

    /**
     * 群发接口
     * URL: /api/wechat/mass
     * Method: POST
     * 
     * @datetime 2024-11-20
     * @return void
     */
    public function mass()
    {
        $data = $this->request->param();
        $result = $this->wechatService->mass($data);
        ApiResponse::success($result);
    }

    /**
     * 创建微信菜单
     * URL: /api/wechat/menu
     * Method: POST
     *
     * @datetime 2024-04-18
     */
    public function createMenu()
    {
        $data = $this->request->param();
        $result = $this->wechatService->createMenu($data);
        ApiResponse::success();
    }

}
