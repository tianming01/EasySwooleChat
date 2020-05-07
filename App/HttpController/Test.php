<?php


namespace App\HttpController;


use App\Model\ChatRecordModel;
use App\Model\GroupMemberModel;
use App\Model\GroupModel;

class Test extends Base
{
    public function websocket()
    {
        $domain = \EasySwoole\EasySwoole\Config::getInstance()->getConf('DOMAIN');
        $port = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MAIN_SERVER.PORT');
        $hostName = 'ws://'.$domain.':' . $port;
        $this->render('websocket', ['server' => $hostName]);
    }

    public function join()
    {
        $data = (new ChatRecordModel())->getFriendChatRecords(1, 10013, 10014);
        $this->writeJson(200, 'ok', $data);
    }
}