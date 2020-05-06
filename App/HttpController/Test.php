<?php


namespace App\HttpController;


use App\Model\ChatRecordModel;
use App\Model\GroupMemberModel;
use App\Model\GroupModel;

class Test extends Base
{
    public function websocket()
    {
        $this->render('websocket');
    }

    public function join()
    {
        $data = (new ChatRecordModel())->getFriendChatRecords(1, 10013, 10014);
        $this->writeJson(200, 'ok', $data);
    }
}