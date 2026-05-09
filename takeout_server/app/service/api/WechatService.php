<?php

namespace app\service\api;

use app\common\Tools;
use app\controller\ApiResponse;
use app\model\AccessToken;
use app\model\UserModel;
use app\service\CoreService;
use think\facade\Config;
use think\facade\Log;

class WechatService extends CoreService
{
    /**
     * 验证微信公众号的有效性
     *
     * @datetime 2024-04-18
     * @param array $params
     * @return string
     */
    public function valid(array $params): string
    {
        $signature = $params["signature"];
        //default timestamp is 2021-01-01 00:00:00
        $timestamp = $params["timestamp"];
        $nonce = $params["nonce"];

        $isVaild = $this->checkSignature($signature, $timestamp, $nonce);
        $returnMessages = $isVaild ? $params["echostr"] : "error";
        return $returnMessages;
    }

    /**
     * 验证微信公众号的签名
     *
     * @datetime 2024-04-18
     * @param string $signature
     * @param int $timestamp
     * @param int $nonce
     * @return bool
     */
    private function checkSignature(string $signature, int $timestamp, int $nonce): bool
    {
        $token = Config::get('wechat.token');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取微信公众号的用户信息并保存到数据库
     *
     * @datetime 2024-04-18
     * @return bool
     */
    public function getWechatUsers(): bool
    {
        $usersList = $this->getUsersListFromWechat();
        if (!$usersList) {
            ApiResponse::fail([], 2000, '获取用户列表失败');
        }
        if ($usersList['total'] == 0) {
            ApiResponse::fail([], 2000, '关注用户列表为空');
        }
        $openIdList = $usersList['data']['openid'];
        $usersInfo = $this->getUsersInfoFromWechat($openIdList);
        if (!$usersInfo) {
            ApiResponse::fail([], 2000, '批量获取用户信息失败');
        }
        if (isset($usersInfo['errcode'])) {
            ApiResponse::fail([], $usersInfo['errcode'], $usersInfo['errmsg']);
        }
        $saveData = [];
        if (isset($usersInfo['user_info_list'])) {
            foreach ($usersInfo['user_info_list'] as $userInfo) {
                $saveData[] = $this->filterUserInfo($userInfo);
            }
        } else {
            $saveData[] = $this->filterUserInfo($usersInfo);
        }

        $userModel = new UserModel();
        $saveResult = $this->saveData($saveData, $userModel, 'open_id');
        if (!$saveResult) {
            ApiResponse::fail([], 1005, '保存用户信息失败');
        }
        return $saveResult;
    }

    public function getMaterial($params)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $this->getAccessToken();
        $result = $this->httpPost($url, $params);
        $result = json_decode($result, true);
        return $result;
    }

    public function getMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=' . $this->getAccessToken();
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        return $result;
    }

    public function createMenu($menuData)
    {
        if (!empty($menuData) && is_array($menuData)) {
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessToken();
            $result = $this->httpPost($url, $menuData);
            $result = json_decode($result, true);
            if ($result['errcode'] == 0) {
                return true;
            } else {
                ApiResponse::fail([], $result['errcode'], $result['errmsg']);
            }
        } else {
            ApiResponse::fail([], 1002);
        }
    }

    public function getDraft()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/draft/batchget?access_token=' . $this->getAccessToken();
        $request_data = [
            'offset' => 0,
            'count' => 20,
            // 0: 返回内容，1: 不返回 content 字段
            'no_content' => 0
        ];
        Log::channel('waimai')->info('获取草稿列表，请求数据：' . json_encode($request_data));
        $result = $this->httpPost($url, $request_data);
        $result = json_decode($result, true);
        return $result;
    }

    public function getDraftById($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/draft/get?access_token=' . $this->getAccessToken();
        $request_data = [
            'media_id' => $media_id
        ];
        $result = $this->httpPost($url, $request_data);
        $result = json_decode($result, true);
        return $result;
    }

    public function createDraft($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/draft/add?access_token=' . $this->getAccessToken();
        $result = $this->httpPost($url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    public function publish($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/freepublish/submit?access_token=' . $this->getAccessToken();
        $result = $this->httpPost($url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    public function mass($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=' . $this->getAccessToken();
        $result = $this->httpPost($url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    public function autoPublish()
    {
        $jsonFilePath = root_path() . 'app' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'article.json';
        $article_data = (new Tools)->getJsonContent($jsonFilePath);
        $request_data = [
            'articles' => [$article_data]
        ];

        // 先创建草稿
        $draftResult = $this->createDraft($request_data);
        // 如果code不为0，说明创建草稿失败
        if (isset($draftResult['errcode']) && $draftResult['errcode'] != 0) {
            Log::channel('waimai')->info('创建草稿失败：' . json_encode($draftResult));
            return;
        }
        $media_id = $draftResult['media_id'];
        Log::channel('waimai')->info('创建草稿成功，media_id为：' . $media_id);
        // 默认情况下，阻止发布，仅创建草稿
        // FIXME： 注释掉下面这行开始发布草稿
        // dd($media_id);

        // 发布草稿
        $request_data = [
            'filter' => [
                'is_to_all' => true
            ],
            'mpnews' => [
                'media_id' => $media_id
            ],
            'msgtype' => 'mpnews'
        ];
        $sendAllResult = $this->mass($request_data);
        Log::channel('waimai')->info('已群发，结果为：' . json_encode($sendAllResult));
    }

    /**
     * 过滤用户信息为了方便插入数据库
     *
     * @datetime 2024-04-18
     * @param array $userInfo
     * @return array
     */
    private function filterUserInfo(array $userInfo): array
    {
        $data = [
            'open_id' => $userInfo['openid'],
            'gender' => $userInfo['sex'],
        ];
        return $data;
    }

    /**
     * 获取微信公众号的用户信息
     *
     * @datetime 2024-04-18
     * @param array $openIdList
     * @return array
     */
    public function getUsersInfoFromWechat(array $openIdList): array
    {
        $numberOfUsers = count($openIdList);
        if ($numberOfUsers == 0) {
            return [];
        }
        if ($numberOfUsers == 1) {
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->getAccessToken() . '&openid=' . $openIdList[0] . '&lang=zh_CN';
            $result = $this->httpGet($url);
            $result = json_decode($result, true);
        } else {
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=' . $this->getAccessToken();
            $postData = [
                'user_list' => []
            ];
            foreach ($openIdList as $openId) {
                $postData['user_list'][] = [
                    'openid' => $openId,
                    'lang' => 'zh_CN'
                ];
            }
            $result = $this->httpPost($url, $postData);
            $result = json_decode($result, true);
        }
        return $result;
    }

    /**
     * 获取微信公众号的关注用户列表
     *
     * @datetime 2024-04-18
     * @return array
     */
    private function getUsersListFromWechat(): array
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $this->getAccessToken();
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信公众号的 access_token
     *
     * @datetime 2024-04-18
     * @return string
     */
    private function getAccessToken(): string
    {
        $accessToken = $this->getAccessTokenFromDb();
        if ($accessToken) {
            return $accessToken;
        } else {
            $accessToken = $this->getAccessTokenFromWechat();
            $this->saveAccessToken($accessToken);
            return $accessToken['access_token'];
        }
    }

    /**
     * 保存 access_token 到数据库
     *
     * @datetime 2024-04-18
     * @param array $accessToken
     * @return void
     */
    private function saveAccessToken(array $accessToken): void
    {
        // only one record in the table
        $isExist = AccessToken::where('id', 1)->find();
        if ($isExist) {
            $accessToken = AccessToken::update([
                'access_token' => $accessToken['access_token'],
                // expires_at is in seconds, so we need to convert it to timestamp
                'expires_at' => date('Y-m-d H:i:s', time() + $accessToken['expires_in']),
                'update_time' => date('Y-m-d H:i:s')
            ], ['id' => 1]);
        } else {
            $accessToken = AccessToken::create([
                'access_token' => $accessToken['access_token'],
                'expires_at' => date('Y-m-d H:i:s', time() + $accessToken['expires_in']),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 从数据库获取 access_token
     *
     * @datetime 2024-04-18
     * @return string|null
     */
    private function getAccessTokenFromDb(): ?string
    {
        $accessToken = AccessToken::where('id', 1)->where('expires_at', '>', date('Y-m-d H:i:s'))->find();
        if ($accessToken) {
            return $accessToken->access_token;
        } else {
            return null;
        }
    }

    /**
     * 从微信获取 access_token
     *
     * @datetime 2024-04-18
     * @return array
     */
    private function getAccessTokenFromWechat(): array
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->getAppId() . '&secret=' . $this->getAppSecret();
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信公众号的 app_id
     *
     * @datetime 2024-04-18
     * @return string
     */
    private function getAppId(): string
    {
        return Config::get('wechat.app_id');
    }

    /**
     * 获取微信公众号的 app_secret
     *
     * @datetime 2024-04-18
     * @return string
     */
    private function getAppSecret(): string
    {
        return  Config::get('wechat.app_secret');
    }

    /**
     * 接收微信公众号的消息并回复
     *
     * @datetime 2024-04-18
     * @param string $postStr
     * @return string
     */
    public function responseMsg(string $postStr): string
    {
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $RX_TYPE = trim($postObj->MsgType);

        //用户发送的消息类型判断
        switch ($RX_TYPE) {
            case "text":    //文本消息
                $result = $this->receiveText($postObj);
                break;
            case "image":   //图片消息
                $result = $this->receiveImage($postObj);
                break;
            case "voice":   //语音消息
                $result = $this->receiveVoice($postObj);
                break;
            case "video":   //视频消息
                $result = $this->receiveVideo($postObj);
                break;
            case "location": //位置消息
                $result = $this->receiveLocation($postObj);
                break;
            case "link":    //链接消息
                $result = $this->receiveLink($postObj);
                break;
            case "event":   //事件
                $result = $this->receiveEvent($postObj);
                break;
            default:
                $result = "unknow msg type: " . $RX_TYPE;
                break;
        }
        return $result;
    }
    private function uploadQrToMedia($ticket) {
        $qrUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
        $accessToken = $this->getAccessToken();

        $ch = curl_init($qrUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $imgData = curl_exec($ch);
        curl_close($ch);

        if (!$imgData) {
            file_put_contents('debug.log', "Error: Failed to download QR image\n", FILE_APPEND);
            return '';
        }

        $uploadUrl = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=image";

        $tmpFile = tmpfile();
        fwrite($tmpFile, $imgData);
        $meta = stream_get_meta_data($tmpFile);
        $filePath = $meta['uri'];

        $curl = curl_init($uploadUrl);
        $data = ['media' => new \CURLFile($filePath, 'image/jpeg', 'qrcode.jpg')];

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resRaw = curl_exec($curl);
        $res = json_decode($resRaw, true);
        curl_close($curl);

        if (!isset($res['media_id'])) {
            file_put_contents('debug.log', "Upload Error: " . $resRaw . "\n", FILE_APPEND);
        }

        return $res['media_id'] ?? '';
    }

    private function transmitImage($object, $mediaId)
    {
        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[image]]></MsgType>
        <Image>
            <MediaId><![CDATA[%s]]></MediaId>
        </Image>
    </xml>";
        return sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $mediaId);
    }

    private function curlPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function getQRCodeTicket($openid) {
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $accessToken;

        $data = [
            "expire_seconds" => 2592000,
            "action_name" => "QR_STR_SCENE",
            "action_info" => ["scene" => ["scene_str" => $openid]]
        ];

        $jsonData = json_encode($data);
        $res = $this->curlPost($url, $jsonData);
        $resArr = json_decode($res, true);

        return $resArr['ticket'] ?? '';
    }

    private function receiveEvent($postObj)
    {
        $eventType = trim((string)$postObj->Event);

        switch ($eventType) {

            case 'subscribe':
                $fromOpenid = (string)$postObj->FromUserName;
                $eventKey   = (string)$postObj->EventKey;

                $this->saveUserInfo($fromOpenid);

                if (!empty($eventKey) && strpos($eventKey, 'qrscene_') === 0) {
                    $referrerOpenid = substr($eventKey, 8);
                    $inviterName = $this->bindReferrer($fromOpenid, $referrerOpenid);

                    $content = $inviterName ? '♥Hi，终于等到你啦！🎊
🧧点餐前记得领哟，每天都可以领，省钱！
-------------------------
🎁点击下方外卖天天神券领取入口，哪个大用哪个
<a href="https://tb.j5k6.com/8pXAj">👉点击领取 饿了么券(淘宝闪购红包)</a>
<a href="https://tb.jiuxinban.com/c?w=1021235&c=19896&i=44581&pf=m&e=&t=https://runion.meituan.com/generateLink/minimedia">👉点击领取 美团红包</a>
-------------------------
✨惊喜优惠等你来，千万别错过！
💖划重点：每天都能来领券哦~
🧧先领券再下单，更便宜！' : '欢迎关注我们的公众号！';
                } else {
                    $content = "欢迎关注我们的公众号！";
                }
                break;

            case 'SCAN':
                $fromOpenid      = (string)$postObj->FromUserName;
                $referrerOpenid  = (string)$postObj->EventKey;

                $inviterName = $this->bindReferrer($fromOpenid, $referrerOpenid);

                $content = $inviterName
                    ? "绑定成功，您的推荐人是：【{$inviterName}】"
                    : "您已关注，欢迎回来！";
                break;

            case 'unsubscribe':
                $fromOpenid = (string)$postObj->FromUserName;
                $this->unsubscribeUser($fromOpenid);
                return '';

            case 'CLICK':
                $eventKey = trim((string)$postObj->EventKey);

                if ($eventKey === 'USER_SHARE_QRCODE') {
                    $openid = (string)$postObj->FromUserName;

                    $ticket = $this->getQRCodeTicket($openid);
                    file_put_contents(
                        'debug.log',
                        date('Y-m-d H:i:s') . " - Ticket: {$ticket}\n",
                        FILE_APPEND
                    );

                    if (empty($ticket)) {
                        return $this->transmitText($postObj, '获取二维码失败，请稍后重试');
                    }

                    $mediaId = $this->uploadQrToMedia($ticket);
                    file_put_contents(
                        'debug.log',
                        date('Y-m-d H:i:s') . " - MediaID: {$mediaId}\n",
                        FILE_APPEND
                    );

                    if ($mediaId) {
                        return $this->transmitImage($postObj, $mediaId);
                    }

                    return $this->transmitText($postObj, '生成二维码图片失败，请稍后再试');
                }

                $content = '未知菜单操作';
                break;

            default:
                $content = "receive a new event: {$eventType}";
                break;
        }

        return $this->transmitText($postObj, $content);
    }

    private function bindReferrer($subOpenid, $refOpenid)
    {
        if (empty($subOpenid) || empty($refOpenid) || $subOpenid === $refOpenid) {
            return null;
        }

        \think\facade\Db::startTrans();
        try {
            // 1. 获取推荐人信息
            $referrer = UserModel::where('open_id', $refOpenid)->find();
            if (!$referrer) {
                \think\facade\Db::rollback();
                return null;
            }

            // 2. 获取当前用户信息
            $subUser = UserModel::where('open_id', $subOpenid)->find();
            if (!$subUser) {
                \think\facade\Db::rollback();
                return null;
            }

            // 3. 如果已有推荐人，则不再绑定
            if (!empty($subUser->inviter_user_id)) {
                \think\facade\Db::rollback();
                return null;
            }

            // 4. 更新当前用户的推荐人ID
            $subUser->save([
                'inviter_user_id' => $refOpenid
            ]);

            // 5. 增加推荐人的邀请计数 (修正点：使用字符串变量 $refOpenid)
            UserModel::where('open_id', $refOpenid)
                ->inc('invite_count')
                ->update();

            \think\facade\Db::commit();

            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - 绑定成功：{$subOpenid} -> {$refOpenid}\n", FILE_APPEND);

            return $referrer->username ?: '系统用户';

        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - bindReferrer异常：" . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    private function unsubscribeUser($openId)
    {
        if (empty($openId)) {
            return false;
        }

        \think\facade\Db::startTrans();

        try {
            $user = UserModel::where('open_id', $openId)->find();

            if (!$user) {
                \think\facade\Db::rollback();
                return false;
            }

            if ((int)$user->is_subscribe === 0) {
                \think\facade\Db::rollback();
                return true;
            }

            $user->save([
                'is_subscribe' => 0
            ]);

            if (!empty($user->inviter_user_id)) {
                UserModel::where('open_id', $user->inviter_user_id)
                    ->where('invite_count', '>', 0)
                    ->dec('invite_count')
                    ->update();
            }

            \think\facade\Db::commit();

            file_put_contents(
                'debug.log',
                date('Y-m-d H:i:s') .
                " - 用户取消关注：{$openId}\n",
                FILE_APPEND
            );

            return true;

        } catch (\Throwable $e) {
            \think\facade\Db::rollback();

            file_put_contents(
                'debug.log',
                date('Y-m-d H:i:s') .
                " - unsubscribeUser异常：" . $e->getMessage() . "\n",
                FILE_APPEND
            );

            return false;
        }
    }

    public function saveUserInfo($openId)
    {
        if (empty($openId)) {
            return false;
        }

        $user = UserModel::where('open_id', $openId)->find();

        if (!$user) {
            UserModel::create([
                'open_id'      => $openId,
                'is_subscribe' => 1,
            ]);
        } else {
            $user->save([
                'is_subscribe' => 1,
            ]);
        }

        return true;
    }

    public function unSaveUserInfo($openId)
    {
        $userModel = new UserModel();
        // update `is_subscribe` to 0 when user unsubscribe
        $userModel->where('open_id', $openId)->update(['is_subscribe' => 0]);
        return true;
    }

    /*
     * 接收文本消息
     */
    private function receiveText($object)
    {
        $content = "你发送的是文本，内容为：" . $object->Content;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收图片消息
     */
    private function receiveImage($object)
    {
        $content = "你发送的是图片，地址为：" . $object->PicUrl;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收语音消息
     */
    private function receiveVoice($object)
    {
        $content = "你发送的是语音，媒体ID为：" . $object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收视频消息
     */
    private function receiveVideo($object)
    {
        $content = "你发送的是视频，媒体ID为：" . $object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收位置消息
     */
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：" . $object->Location_X . "；经度为：" . $object->Location_Y . "；缩放级别为：" . $object->Scale . "；位置为：" . $object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收链接消息
     */
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：" . $object->Title . "；内容为：" . $object->Description . "；链接地址为：" . $object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 回复文本消息
     */
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }
}
