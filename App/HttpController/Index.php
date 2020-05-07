<?php


namespace App\HttpController;


use App\Model\UserModel;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Validate\Validate;

class Index extends Base
{

    public function index()
    {
        $token =  $this->request()->getRequestParam('token');
        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $user = $redis->get('User_token_'.$token);
        var_dump($user);
        if (!$user) {
            $this->response()->redirect("/user/login");
        }

        $user = json_decode($user,true);
        $domain = \EasySwoole\EasySwoole\Config::getInstance()->getConf('DOMAIN');
        $port = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MAIN_SERVER.PORT');
//        $hostName = 'ws://'.$this->request()->getUri()->getHost().':9501';// 这里的getHost()获取到的是服务器IP地址
        $hostName = 'ws://'.$domain . ':' . $port;
        $this->render('index', [
            'server' => $hostName,'token'=>$token,'user'=>$user
        ]);
    }

    /**
     * 登录
     */
//    public function login()
//    {
//        // 非POST请求直接渲染login页面
//        if ($this->request()->getMethod() != 'POST') {
//            $this->render('login');
//            return;
//        }
//        // 否则就是POST的逻辑了。
//        $validate = new Validate();
//        $validate->addColumn('username')->required('用户名必填');
//        $validate->addColumn('password')->required('密码必填');
//
//        if ($this->validate($validate)) {
//            $params = $this->request()->getRequestParam();
//
//            $UserModel = new UserModel();
//            $user = $UserModel->getUserByUsername($params['username']);
//            if (!$user) {
//                return $this->writeJson(10001,'用户不存在');
//            }
//
//            if(!password_verify ( $params['password'] , $user['password'])){
//                return $this->writeJson(10001,'密码输入不正确!');
//            };
//
//            $token = uniqid().uniqid().$user['id'];
//            echo 'TOKEN:' . $token . PHP_EOL;
//
//            \EasySwoole\RedisPool\Redis::invoke('redis', function (\EasySwoole\Redis\Redis $redis) use ($token,$user){
//                $redis->set('User_token_'.$token,json_encode($user),36000);
//                echo 'SET TOKEN' . PHP_EOL;
//            });
//
//            return $this->writeJson(200, '登录成功',['token'=>$token]);
//        } else {
//            return $this->writeJson(10001, $validate->getError()->__toString(), 'fail');
//        }
//    }



    public function user()
    {
        $userModel = new UserModel();
        $userList = $userModel->getAll();
        $this->response()->write(json_encode($userList));
    }
}