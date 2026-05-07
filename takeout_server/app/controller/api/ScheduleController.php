<?php

namespace app\controller\api;

use app\common\CoreController;
use app\service\api\WechatService;
use think\facade\Log;

class ScheduleController extends CoreController
{
    // rewrite middleware avoid to validate token
    protected $middleware = [];

    protected $wechatService;
    public function initialize()
    {
        parent::initialize();
        $this->wechatService = new WechatService();
    }

    /**
     * 定时任务创建草稿并发布
     * URL: /api/schedule/publish
     *
     * @datetime 2024-04-19
     */
    public function publish()
    {
        $params = $this->request->param();
        if (!isset($params['secret']) || $params['secret'] !== 'mJ4yJ9eK') {
            Log::channel('waimai')->error('请求定时任务创建草稿并发布失败，密钥错误');
            return;
        }
        Log::channel('waimai')->info('开始请求定时任务创建草稿并发布');
        $this->wechatService->autoPublish();
    }
}
