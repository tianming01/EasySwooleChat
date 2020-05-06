<?php


namespace App\Model;


use EasySwoole\ORM\AbstractModel;

class FriendGroupModel extends AbstractModel
{
    protected $tableName = 'friend_group';
    protected $primaryKey = 'id';

    public function getAll(Array $where = [], int $pageNo = 1, int $pageSize = 10): array
    {
        $skip = $pageSize * ($pageNo - 1);
        $list = $this->limit($skip, $pageSize)->order($this->primaryKey, 'DESC')->withTotalCount()->all($where);
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }

}