<?php


namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\VerifyCode\Conf;

class VerifyCode extends Controller
{
    function index()
    {
        $params = $this->request()->getRequestParam();
        $key = $params['key'];

        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $this->response()->withHeader('Content-Type','image/png');
        $drawResult = $code->DrawCode();
        $imageByte = $drawResult->getImageByte();
        $captchaCode = $drawResult->getImageCode();
        echo 'VerifyCode::' . $drawResult->getImageCode() . PHP_EOL;

        \EasySwoole\RedisPool\Redis::invoke('redis', function (\EasySwoole\Redis\Redis $redis) use ($key,$captchaCode){
            $redis->set('Code'.$key, $captchaCode,1000);
        });

        $this->response()->write($imageByte);
    }

    function getBase64(){
        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $this->response()->write($code->DrawCode()->getImageBase64());
    }
}