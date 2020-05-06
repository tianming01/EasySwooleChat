<?php


namespace App\HttpController;


use App\Model\ChatRecordModel;
use App\Model\FriendGroupModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupModel;
use App\Model\SystemMessageModel;
use App\Model\UserModel;
use EasySwoole\Validate\Validate;

class User extends Base
{
    /**
     * 注册
     */
    public function register()
    {
        if ($this->request()->getMethod() != 'POST') {
            $code_hash = uniqid().uniqid();
            $this->render('register',
                ['code_hash'=>$code_hash]
            );
            return ;
        }

        $validate = new Validate();
        $validate->addColumn('username')->required('用户名必填');
        $validate->addColumn('password')->required('密码必填');
        $validate->addColumn('nickname')->required('昵称必填');
        $validate->addColumn('code')->required('验证码必填');

        if (!$this->validate($validate)) {
            return $this->writeJson(10001, $validate->getError()->__toString(), 'fail');
        }

        $params = $this->request()->getRequestParam();

        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $codeCache = $redis->get('Code'.$params['key']);

        if ($codeCache != $params['code']){
            return $this->writeJson(10001, '验证码错误',$codeCache);
        }

        $userModel = new UserModel();
        $user = $userModel->getUserByUsername($params['username']);
        if ($user) {
            return $this->writeJson(10001,'用户名已存在');
        }

        $data = [
            'username' => $params['username'],
            'password' => password_hash($params['password'], PASSWORD_DEFAULT),
            'nickname' => $params['nickname'],
            'avatar' => $params['avatar'],
            'sign' => $params['sign'],
        ];

        $user_id = $userModel::create($data)->save();
        if (!$user_id) {
            return $this->writeJson(10001,'注册失败');
        }

        $friendGroupModel = new FriendGroupModel();
        $friendGroupModel::create([
            'user_id' => $user_id,
            'groupname' => '默认分组'
        ])->save();

        $groupMemberModel = new GroupMemberModel();
        $groupMemberModel::create([
            'user_id' => $user_id,
            'group_id' => 10001
        ])->save();
        return $this->writeJson(200, '注册成功');
    }
    /**
     * 登录
     */
    public function login()
    {
        // 非POST请求直接渲染login页面
        if ($this->request()->getMethod() != 'POST') {
            $this->render('login');
            return;
        }
        // 否则就是POST的逻辑了。
        $validate = new Validate();
        $validate->addColumn('username')->required('用户名必填');
        $validate->addColumn('password')->required('密码必填');

        if ($this->validate($validate)) {
            $params = $this->request()->getRequestParam();

            $UserModel = new UserModel();
            $user = $UserModel->getUserByUsername($params['username']);
            if (!$user) {
                return $this->writeJson(10001,'用户不存在');
            }

            if(!password_verify ( $params['password'] , $user['password'])){
                return $this->writeJson(10001,'密码输入不正确!');
            };

            $token = uniqid().uniqid().$user['id'];
            echo 'TOKEN:' . $token . PHP_EOL;

            \EasySwoole\RedisPool\Redis::invoke('redis', function (\EasySwoole\Redis\Redis $redis) use ($token,$user){
                $redis->set('User_token_'.$token,json_encode($user),36000);
                echo 'SET TOKEN' . PHP_EOL;
            });

            return $this->writeJson(200, '登录成功',['token'=>$token]);
        } else {
            return $this->writeJson(10001, $validate->getError()->__toString(), 'fail');
        }
    }

    /**
     * 退出登录
     */
    public function loginout()
    {
        $token =  $this->request()->getRequestParam('token');

        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $redis->del('User_token_'.$token);
        $this->response()->redirect("/user/login");
    }

    protected function getTokenUser()
    {
        $token =  $this->request()->getRequestParam('token');

        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $user = $redis->get('User_token_'.$token);

        if (!$user) {
            return null;
        }
        $user = json_decode($user,true);
        return $user;
    }

    /**
     *  初始化用户信息
     */
    public function userinfo()
    {
//        $token =  $this->request()->getRequestParam('token');
//
//        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
//        $user = $redis->get('User_token_'.$token);
//
//        if (!$user) {
//            return $this->writeJson(10001,"获取用户信息失败");
//        }
//
//        $user = json_decode($user,true);
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }

        $groupMemberModel = new GroupMemberModel();
        $groupModel = new GroupModel();
        $groupMembers = $groupMemberModel->all(['user_id' => $user['id']]);
        $groups = [];
        foreach ($groupMembers as $key => $value) {
            $group = $groupModel->get(['id' => $value->group_id]);
            if (empty($group)) {
                continue;
            }
            $groups[] = [
                'groupname' =>  $group['groupname'] . '(' . $group['id'] . ')',
                'avatar' => $group['avatar'],
                'id' => $group['id'],
            ];
        }
        $friendGroupModel = new FriendGroupModel();
        $friendGroups = $friendGroupModel->where(['user_id' => $user['id']])->all();
        $friendGroupsList = [];
        $friendModel = new FriendModel();
        foreach ($friendGroups as $key => $friendGroup) {
            $where = [
                'user_id' => $user['id'],
                'friend_group_id' => $friendGroup['id'],
            ];
            $friends = $friendModel->where($where)->all();
            $friendList = [];
            foreach ($friends as $friend) {
                $friendList[] = [
                    'id' => $friend->friend_id,
                    'username' => $friend->friendUser->nickname,
                    'avatar' => $friend->friendUser->avatar,
                    'sign' => $friend->friendUser->sign,
                    'status' => $friend->friendUser->status,
                ];
            }
            $friendGroupsList[$key]['id'] = $friendGroup['id'];
            $friendGroupsList[$key]['groupname'] = $friendGroup['groupname'];
            $friendGroupsList[$key]['list'] = $friendList;
        }

        $data = [
            'mine'      => [
                'username'  => $user['nickname'].'('.$user['id'].')',
                'id'        => $user['id'],
                'status'    => $user['status'],
                'sign'      => $user['sign'],
                'avatar'    => $user['avatar']
            ],
            "friend"    => $friendGroupsList,
            "group"     => $groups
        ];

        return $this->writeJson(0,'success',$data);
    }

    /**
     *  更新个性签名
     */
    public function updateSign()
    {
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }
        $params =  $this->request()->getRequestParam();
        $updateData = ['sign' => $params['sign']];
        $userModel = new UserModel();
        $where = ['id' => $user['id']];
        $userModel->update($updateData, $where);
        return $this->writeJson(0,'更新个性签名成功',null);
    }

    /**
     *   获取群成员
     */
    public function groupMembers()
    {
        $params =  $this->request()->getRequestParam();
        $id =  $params['id'];

        $groupMemberModel = new GroupMemberModel();
        $groupMemberList = $groupMemberModel->alias('gm')
            ->join('user as u', 'u.id=gm.user_id', 'inner')
            ->where(['gm.group_id' => $id])
            ->field('u.id,u.username,u.avatar,u.sign')
            ->all();
        if (empty($groupMemberList)) {
            return $this->writeJson(10001,"获取群成员失败");
        }
        return $this->writeJson(0,"",['list' => $groupMemberList]);
    }

    /**
     *  查找页面
     */
    public function find()
    {
        $params =  $this->request()->getRequestParam();

        $type = isset($params['type']) ? $params['type'] : '';
        $wd = isset($params['wd']) ? $params['wd'] : '';
        $userList = [];
        $groupList = [];

        if ($type == 'user') {
            $userModel = new UserModel();
            $userList = $userModel->likeSearch(['id', 'username', 'nickname'], $wd);
        } elseif ($type == 'group') {
            $groupModel = new GroupModel();
            $groupList = $groupModel->likeSearch(['id', 'groupname'], $wd);
        }
        $this->render('find', ['user_list' => $userList,'group_list' => $groupList,'type' => $type,'wd' => $wd]);
    }

    /**
     * 消息盒子
     */
    public function messageBox()
    {
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }
        $params =  $this->request()->getRequestParam();
        $systemMessageModel = new SystemMessageModel();
        $systemMessageModel->update(['read' => 1], ['user_id' => $user['id']]);

        $list = $systemMessageModel->alias('sm')
            ->join('user as u', 'u.id = sm.from_id', 'INNER')
            ->where(['sm.user_id' => $user['id']])
            ->field('sm.id,u.id as uid,u.avatar,u.nickname,sm.remark,sm.time,sm.type,sm.group_id,sm.status')
            ->order('id', 'DESC')
            ->limit(0,50)
            ->all();

        foreach ($list as $k => $v) {
            $list[$k]['time'] = $this->formatTime($v['time']);
        }
        $this->render('message_box',['list' => $list]);
    }

    /**
     * 格式化时间
     * @param $the_time
     * @return false|string
     */
    private function  formatTime($the_time)
    {
        $now_time = time();
        $dur = $now_time - $the_time;
        if ($dur <= 0) {
            $mas =  '刚刚';
        } else {
            if ($dur < 60) {
                $mas =  $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    $mas =  floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        $mas =  floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) { //3天内
                            $mas =  floor($dur / 86400) . '天前';
                        } else {
                            $mas =  date("Y-m-d H:i:s",$the_time);
                        }
                    }
                }
            }
        }
        return $mas;
    }

    /**
     *  加入群
     */
    public function joinGroup()
    {
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }

        $validate = new Validate();
        $validate->addColumn('groupid', '群id')->required('群id不为空')->integer('必须是数字')->min(1,"必须大于0");
        if (!$this->validate($validate)) {
            return $this->writeJson(10001, $validate->getError()->__toString(), 'fail');
        }
        $params =  $this->request()->getRequestParam();
        $groupId = intval($params['groupid']);

        // 判断是否已经是群成员
        $groupMenberModel = new GroupMemberModel();
        $count = $groupMenberModel->where(['group_id' => $groupId, 'user_id' => $user['id']])->count();
        if ($count > 0) {
            return $this->writeJson(10001,"您已经是该群成员");
        }
        $insertResult = $groupMenberModel::create(['group_id' => $groupId, 'user_id' => $user['id']])->save();
        if (!$insertResult) {
            return $this->writeJson(10001,"加入群失败");
        }

        $groupModel = new GroupModel();
        $group = $groupModel->where(['id' => $groupId])->get();

        $data = [
            'type' => 'group',
            'avatar' => $group['avatar'],
            'groupname' => $group['groupname'],
            'id' => $group['id']
        ];
        return $this->writeJson(200,"加入成功",$data);
    }

    /**
     * 创建群
     */
    public function createGroup()
    {
        if ($this->request()->getMethod() != 'POST') {
            return $this->render('create_group');
        }
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }
        // 校验提交的数据
        $validate = new Validate();
        $validate->addColumn('groupname', '群名称')->required('请填写群名称')->lengthMin(2,'群名称不少于2个字');
        if (!$this->validate($validate)) {
            return $this->writeJson(10001, $validate->getError()->__toString(), 'fail');
        }
        // 插入数据
        $params =  $this->request()->getRequestParam();
        $groupData = [
            'groupname' => $params['groupname'],
            'user_id'   => $user['id'],
            'avatar'    => empty($params['avatar']) ? '' : $params['avatar']
        ];

        $groupId = GroupModel::create($groupData)->save();
        $groupMemberData = [
            'group_id' => $groupId,
            'user_id' => $user['id'],
        ];
        $groupMemberId = GroupMemberModel::create($groupMemberData)->save();

        if (!$groupId || !$groupMemberId) {
            return $this->writeJson(10001,"创建群失败！");
        }

        $data = [
            'type'      => 'group',
            'avatar'    => $params['avatar'],
            'groupname' => $params['groupname'],
            'id'        => $groupId
        ];
        return $this->writeJson(200,"创建群成功！",$data);
    }

    /**
     * 聊天记录
     */
    public function chatLog()
    {
        echo 'chatLog::'. PHP_EOL;
        if ($this->request()->getMethod() != 'POST') {
            $params =  $this->request()->getRequestParam();
            return $this->render('chat_log',['id' => $params['id'],'type' => $params['type']]);
        }

        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }
        echo 'user token::'. PHP_EOL;

        $params =  $this->request()->getRequestParam();

        $id = $params['id'];echo 'id::' . $id . PHP_EOL;
        $type = $params['type'];echo 'type::' . $type . PHP_EOL;
        $page = empty($params['page']) ? 1 : intval($params['page']);
        $chatRecordModel = new ChatRecordModel();

        if ($type == 'group') {
            $groupCharRecords = $chatRecordModel->getGroupChatRecords($page, $id);
            $list = $groupCharRecords['list'];
            $count = $groupCharRecords['total'];
        } else {
            $friendChatRecords = $chatRecordModel->getFriendChatRecords($page, $user['id'], $id);
            $list = $friendChatRecords['list'];
            $count = $friendChatRecords['total'];
        }
        foreach ($list as $k=>$v){
            $list[$k]['timestamp'] = $v['timestamp'] * 1000;
        }
        $result['data'] = $list;
        $result['last_page'] = intval($count / 20) + 1;
        return $this->writeJson(0,'',$result);
    }

    /**
     * 添加好友
     */
    public function addFriend()
    {
        echo 'addFriend::'. PHP_EOL;
        $user = $this->getTokenUser();
        if (empty($user)) {
            return $this->writeJson(10001,"获取用户信息失败");
        }
        $params =  $this->request()->getRequestParam();

        $id = $params['id'];

        $systemMessageModel = new SystemMessageModel();
        $systemMessage = $systemMessageModel->get(['id' => $id]);
        $friendModel = new FriendModel();
        $isFriend =  $friendModel->where(['user_id' => $systemMessage['user_id'], 'friend_id' => $systemMessage['from_id']])->count();

        if ($isFriend > 0) {
            return $this->writeJson(10001,'已经是好友了');
        }

        $data = [
            [
                'user_id' => $systemMessage['user_id'],
                'friend_id' =>$systemMessage['from_id'],
                'friend_group_id' => $params['groupid']
            ],
            [
                'user_id' =>$systemMessage['from_id'],
                'friend_id' => $systemMessage['user_id'],
                'friend_group_id' => $systemMessage['group_id']
            ]
        ];
        $res = FriendModel::create()->saveAll($data);
        if (!$res) {
            return $this->writeJson(10001,'添加失败');
        }
        $systemMessageModel->update(['status' => 1], ['id' => $id]);
        $userModel = new UserModel();
        $user = $userModel->get(['id' => $systemMessage['from_id']]);

        $data = [
            'type'      => "friend",
            'avatar'    => $user['avatar'],
            'username'  => $user['nickname'],
            'groupid'   => $params['groupid'],
            'id'        => $user['id'],
            'sign'      => $user['sign']
        ];

        $system_message_data = [
            'user_id'   => $systemMessage['from_id'],
            'from_id'   => $systemMessage['user_id'],
            'type'      => 1,
            'status'    => 1,
            'time'      => time()
        ];
        SystemMessageModel::create($system_message_data)->save();

        return $this->writeJson(200,'添加成功',$data);
    }

    /**
     * 拒绝添加好友
     */
    public function refuseFriend()
    {
        $params =  $this->request()->getRequestParam();
        $id = $params['id'];

        $systemMessageModel = new SystemMessageModel();
        $systemMessage = $systemMessageModel->where(['id' => $id])->get();

        $res1 = $systemMessageModel->update(['status' => 2], ['id' => $id]);

        $data = [
            'user_id'   => $systemMessage['from_id'],
            'from_id'   => $systemMessage['user_id'],
            'type'      => 1,
            'status'    => 2,
            'time'      => time()
        ];
        $res2 = SystemMessageModel::create($data)->save();

        if ($res1 && $res2){
            return $this->writeJson(200,"已拒绝");
        } else {
            return $this->writeJson(10001,"操作失败");
        }
    }
}