<?php


namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

class UserModel extends AbstractModel
{
    protected $tableName = 'user';
    protected $primaryKey = 'id';

    public function getAll(Array $where = [], int $pageNo = 1, int $pageSize = 10): array
    {
        $skip = $pageSize * ($pageNo - 1);
        $list = $this->limit($skip, $pageSize)->order($this->primaryKey, 'DESC')->withTotalCount()->all($where);
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }

    public function getOne(int $id = 0):?UserModel
    {
        $info = $this->get(['id' => $id]);
        return $info;
    }

    public function getUserByUsername(string $username = null) :? UserModel
    {
        if (empty($username)) {
            return null;
        }
        $user = $this->get(['username' => $username]);
        return $user;
    }

    public function likeSearch(array $columns, string $search)
    {
        if (empty($columns)) {
            return null;
        }
        $res = $this->all(function(QueryBuilder $queryBuilder) use ($columns, $search){
            $search = '%' . $search .'%';
            foreach ($columns as $key => $column) {
                if ($key == 0) {
                    $queryBuilder->where($column, $search, 'like');
                    continue;
                }
                $queryBuilder->orWhere($column, $search, 'like');// 各种特殊操作符  between like != 等等都可以完成
            }
            $queryBuilder->orderBy('id', 'DESC');
        });
        return $res;
    }
}