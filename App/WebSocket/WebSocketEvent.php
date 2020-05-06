<?php
namespace App\WebSocket;

use App\Model\FriendModel;
use App\Model\OfflineMessageModel;
use App\Model\SystemMessageModel;
use App\Model\UserModel;
use EasySwoole\FastCache\Cache;

class WebSocketEvent
{

    /**
     * 打开了一个链接
     * @param \Swoole\Websocket\Server $server
     * @param \Swoole\Http\Request $request
     */
    public function onOpen(\Swoole\Websocket\Server $server, \Swoole\Http\Request $request)
    {
        echo 'onOpen::fd:' . $request->fd . PHP_EOL;
        $token = $request->get["token"];

        if(!isset($token)){
            $data = [
                "type" => "token_expire"
            ];
            $server->push($request->fd, json_encode($data));
            return;
        }

        $redis = \EasySwoole\RedisPool\Redis::defer('redis');

        $user = $redis->get('User_token_'.$token);
        $user = json_decode($user,true);
        if($user == null){
            $data = [
                "type" => "token_expire"
            ];
            $server->push($request->fd, json_encode($data));
            return;
        }

        //绑定fd变更状态
        Cache::getInstance()->set('uid'.$user['id'], ["value"=>$request->fd],3600);
        Cache::getInstance()->set('fd'.$request->fd, ["value"=>$user['id']],3600);
        $userModel = new UserModel();
        $userModel->update(['status' => 'online'], ['id' => $user['id']]);//标记为在线
        //给好友发送上线通知，用来标记头像去除置灰
        $friendModel = new FriendModel();
        $friendList = $friendModel->where(['user_id' => $user['id']])->all();
        $data = [
            "type"  => "friendStatus",
            "uid"   => $user['id'],
            "status"=> 'online'
        ];
        foreach ($friendList as $friend) {
            $fd = Cache::getInstance()->get('uid'.$friend['friend_id']);//获取接受者fd
            if ($fd){
                $server->push($fd['value'], json_encode($data));//发送消息
            }
        }
        //获取未读消息盒子数量
        $systemMessageModel = new SystemMessageModel();
        $count = $systemMessageModel->where(['user_id' => $user['id'], 'read' => 0])->count();
        $data = [
            "type"      => "msgBox",
            "count"     => $count
        ];
        //检查离线消息
        $offlineMessageModel = new OfflineMessageModel();
        //【注意，字段data与父类中的data同名了，会出现意想不到的bug。取个别名content】
        $offlineMessages = $offlineMessageModel->field('id,user_id,data as content,status')->where(['user_id' => $user['id'], 'status' => 0])->all();
//        var_dump($offlineMessageModel->lastQuery()->getLastQuery());
        foreach ($offlineMessages as $message) {
            $fd = Cache::getInstance()->get('uid' . $user['id']);//获取接受者fd
            if (!$fd) {
                continue;
            }

//            echo 'push fd:'. $fd['value']. PHP_EOL;
//            var_dump($message->content);
//            echo 'message data::' . $message['content'] . PHP_EOL;
            $server->push($fd['value'], $message['content']);//发送消息
            $offlineMessageModel->update(['status' => 1], ['id' => $message['id']]);
        }
        $server->push($request->fd, json_encode($data));
    }
    /*
    * 关闭事件
    *
    * @param \swoole_server $server
    * @param int            $fd
    * @param int            $reactorId
    */
    public function onClose(\Swoole\WebSocket\Server $server, int $fd, int $reactorId)
    {
        echo 'onClose::fd:' . $fd. PHP_EOL;
        echo 'onClose::reactorId:' . $reactorId. PHP_EOL;
        /** @var array $info */
        $info = $server->getClientInfo($fd);
        /**
         * 判断此fd 是否是一个有效的 websocket 连接
         * 参见 https://wiki.swoole.com/wiki/page/490.html
         */
        if ($info && $info['websocket_status'] === WEBSOCKET_STATUS_FRAME) {
            /**
             * 判断连接是否是 server 主动关闭
             * 参见 https://wiki.swoole.com/wiki/page/p-event/onClose.html
             */
            if ($reactorId < 0) {
                echo "server close \n";
            }
        }

        //给好友发送下线通知，用来标记头像置灰
        $uid = Cache::getInstance()->get('fd'.$fd);
        $friendModel = new FriendModel();
        $friendList = $friendModel->where(['user_id' => $uid['value']])->all();

        $data = [
            "type"  => "friendStatus",
            "uid"   => $uid['value'],
            "status"=> 'offline'
        ];

        foreach($friendList as $friend) {
            $fd = Cache::getInstance()->get('uid' . $friend['friend_id']);// 获取接受者fd
            if ($fd) {
                $server->push($fd['value'], json_encode($data));// 发送消息
            }
        }

        Cache::getInstance()->unset('uid'.$uid['value']);// 解绑uid映射
        Cache::getInstance()->unset('fd' . $fd['value']);// 解绑fd映射
        $userModel = new UserModel();
        $userModel->update(['status' => 'offline'], ['id' => $uid['value']]);
    }


    /**
     * 握手事件
     * 【设置 onHandShake 回调函数后不会再触发 onOpen 事件，需要应用代码自行处理】
     * @param \swoole_http_request  $request
     * @param \swoole_http_response $response
     * @return bool
     */
    public function onHandShake(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        echo 'onHandShake::' . PHP_EOL;
        /** 此处自定义握手规则 返回 false 时中止握手 */
        if (!$this->customHandShake($request, $response)) {
            $response->end();
            return false;
        }

        /** 此处是  RFC规范中的WebSocket握手验证过程 必须执行 否则无法正确握手 */
        if ($this->secWebsocketAccept($request, $response)) {
            $response->end();
            return true;
        }

        $response->end();
        return false;
    }

    /**
     * 自定义握手事件
     *
     * @param \swoole_http_request  $request
     * @param \swoole_http_response $response
     * @return bool
     */
    protected function customHandShake(\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool
    {
        /**
         * 这里可以通过 http request 获取到相应的数据
         * 进行自定义验证后即可
         * (注) 浏览器中 JavaScript 并不支持自定义握手请求头 只能选择别的方式 如get参数
         */
        $headers = $request->header;
        $cookie = $request->cookie;

        // if (如果不满足我某些自定义的需求条件，返回false，握手失败) {
        //    return false;
        // }
        return true;
    }

    /**
     * RFC规范中的WebSocket握手验证过程
     * 以下内容必须强制使用
     *
     * @param \swoole_http_request  $request
     * @param \swoole_http_response $response
     * @return bool
     */
    protected function secWebsocketAccept(\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool
    {
        // ws rfc 规范中约定的验证过程
        if (!isset($request->header['sec-websocket-key'])) {
            // 需要 Sec-WebSocket-Key 如果没有拒绝握手
            var_dump('shake fai1 3');
            return false;
        }
        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $request->header['sec-websocket-key'])
            || 16 !== strlen(base64_decode($request->header['sec-websocket-key']))
        ) {
            //不接受握手
            var_dump('shake fai1 4');
            return false;
        }

        $key = base64_encode(sha1($request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $headers = array(
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive'             => 'off',
        );

        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        // 发送验证后的header
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        // 接受握手 还需要101状态码以切换状态
        $response->status(101);
        var_dump('shake success at fd :' . $request->fd);
        return true;
    }
}