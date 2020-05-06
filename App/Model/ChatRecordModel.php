<?php


namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

class ChatRecordModel extends AbstractModel
{
    protected $tableName = 'chat_record';
    protected $primaryKey = 'id';

    public function getFriendChatRecords(int $pageNo, int $userId, int $friendId)
    {
        $this->alias('cr')
            ->withTotalCount()
            ->join('user as u', 'u.id=cr.user_id')
            ->field('u.nickname as username,u.id,u.avatar,time as timestamp,cr.content,cr.friend_id')
            ->where('(cr.user_id=' . $userId . ' AND cr.friend_id=' . $friendId . ') OR (cr.user_id='. $friendId . ' AND cr.friend_id=' . $userId.')')
            ->order('time', 'DESC')
            ->limit(($pageNo -1) * 20, 20);
        // 列表数据
        $list = $this->all();
//        var_dump($this->lastQuery()->getLastQuery());
        // 总记录数
        $result = $this->lastQueryResult();
        $total = $result->getTotalCount();
        return ['list' => $list, 'total' => $total];
    }

    public function getGroupChatRecords(int $pageNo, int $groupId)
    {
        $this->alias('cr')
            ->withTotalCount()
            ->join('user as u', 'u.id = cr.user_id', 'INNER')
            ->where(['cr.group_id' => $groupId])
            ->field('u.nickname as username,u.id,u.avatar,time as timestamp,cr.content')
            ->order('time', 'DESC')
            ->limit(($pageNo -1) * 20, 20);
        // 列表数据
        $list = $this->all();
//        var_dump($this->lastQuery()->getLastQuery());
        // 总记录数
        $result = $this->lastQueryResult();
        $total = $result->getTotalCount();
        return ['list' => $list, 'total' => $total];
    }

}