<?php
namespace EasySwoole\EasySwoole;


use App\WebSocket\WebSocketEvent;
use App\WebSocket\WebSocketEvents;
use App\WebSocket\WebSocketParser;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Config as DbConfig;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Socket\Dispatcher;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        /**
         * REDIS协程连接池
         */
        $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS');
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig($config);
        \EasySwoole\RedisPool\Redis::getInstance()->register('redis', $redisConfig);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        // Swoole静态文件处理器
        $server->set([
            'document_root' => EASYSWOOLE_ROOT,//'/data/wwwroot/easyswoole-chat', // v4.4.0以下版本, 此处必须为绝对路径
            'enable_static_handler' => true,
        ]);
        // 热启动
        $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
        $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
        $hotReload->attachToServer($server);

        // 数据库
        $dbConfig = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new Connection($dbConfig));
        $register->add($register::onWorkerStart, function (){
            //链接预热
            DbManager::getInstance()->getConnection()->getClientPool()->keepMin();
        });

        // 注册FastCache
        Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());
        /**
         * **************** websocket控制器 **********************
         */
        // 创建一个 Dispatcher 配置
        $socketConfig = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $socketConfig->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $socketConfig->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($socketConfig);

        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });

        //自定义事件
        $websocketEvent = new WebSocketEvent();
//        $register->add(EventRegister::onHandShake, [$websocketEvent, 'onHandShake']);//设置 onHandShake 回调函数后不会再触发 onOpen 事件，需要应用代码自行处理
        $register->add(EventRegister::onOpen, [$websocketEvent, 'onOpen']);
        $register->add(EventRegister::onClose, [$websocketEvent, 'onClose']);
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}