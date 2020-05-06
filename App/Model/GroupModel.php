<?php


namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

class GroupModel extends AbstractModel
{
    //【注意】group是MySql的关键字，这里要用`号（与~号同一个按键）包含起来。
    //不然会报错：SQLSTATE[42000] [1064] You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'group' at line 1
    protected $tableName = '`group`';
    protected $primaryKey = 'id';

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