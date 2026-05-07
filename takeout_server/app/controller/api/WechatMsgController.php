<?php

namespace app\controller\api;

use app\common\CoreController;
use app\controller\ApiResponse;
use app\service\api\WechatService;

class WechatMsgController extends CoreController
{
    protected $wechatService;

    // rewrite middleware avoid to validate token
    protected $middleware = [];

    public function initialize()
    {
        parent::initialize();
        $this->wechatService = new WechatService();
    }

    /**
     * 添加微信公众号时的验证
     * URL: /api/wechat/token
     *
     * @datetime 2024-04-16
     * @return string
     */
    public function valid(): string
    {
        $params = $this->request->param();
        return $this->wechatService->valid($params);
    }

    /**
     * 根据公众号推送的消息，进行回复或者处理
     * URL: /api/wechat/token
     * Method: POST
     *
     * @datetime 2024-04-18
     */
    public function responseMsg()
    {
        $postStr = file_get_contents('php://input');
        if (!empty($postStr)) {
            $result = $this->wechatService->responseMsg($postStr);

            return xml($result);
        } else {
            ApiResponse::fail([], 1002);
        }
    }
}
