<?php


namespace App\Model;


use EasySwoole\ORM\AbstractModel;

class FriendModel extends AbstractModel
{
    protected $tableName = 'friend';
    protected $primaryKey = 'id';

    public function friendUser()
    {
        return $this->hasOne(UserModel::class, null, 'friend_id', 'id');
    }

}