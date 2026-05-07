<?php

namespace app\controller\api;

use app\common\CoreController;
use app\controller\ApiResponse;
use app\BaseController;

class CreateMenuController
{
    protected $wechatService;
    protected $request;

    //http://vx.aicanv.com/api/create  访问地址
    public function create ()
    {
//        $menuData = [
//            "button" => [
//                [
//                    "type" => "view",
//                    "name" => "美团券",
//                    "url"  => "https://tb.jiuxinban.com/c?w=1021235&c=19896&i=44581&pf=m&e=&t=https://runion.meituan.com/generateLink/minimedia"
//                ],
//                [
//                    "type" => "view",
//                    "name" => "饿了么",
//                    "url"  => "https://tb.j5k6.com/8pXAj"
//                ],
//                [
//                    "type"     => "click",
//                    "name"     => "分享好友",
////                    点击分享好友功能服务器会自动生成并发送一个带有当前用户openid的当前公众号二维码
////                    有人通过扫描二维码进行关注公众号自行记录下来 并保存到数据库中
//                    "key" => "USER_SHARE_QRCODE"
//                ]
//            ]
//        ];

        $menuData = $this->request->param();
        $result = $this->wechatService->createMenu($menuData);
        ApiResponse::success();
    }

}
