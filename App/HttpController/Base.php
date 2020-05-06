<?php


namespace App\HttpController;


use App\Lib\PlatesRender;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;
use EasySwoole\Template\Render;

/**
 * 基础控制器
 * Class Base
 * @package App\HttpController
 */
class Base extends Controller
{
    public function index()
    {
        $this->actionNotFound('index');
    }

    protected function render(string $template, array $vars = [])
    {
        $viewsPath = EASYSWOOLE_ROOT . '/App/Views';
        $platesRender = new PlatesRender($viewsPath);
        $templateRender = Render::getInstance();
        $templateRender->getConfig()->setRender($platesRender);
        $content = $platesRender->render($template, $vars);
        $this->response()->write($content);
    }

    protected function getConfigValue(string $keyPath, string $defaultValue)
    {
        $value = Config::getInstance()->getConf($keyPath);
        $value = is_null($value) ? $defaultValue : $value;
        return $value;
    }

    protected function writeJson($statusCode = 200, $msg = null,$result = null)
    {
        if ($this->response()->isEndResponse()) {
            return false;
        }
        $data = Array(
            "code" => $statusCode,
            "data" => $result,
            "msg" => $msg
        );
        $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response()->withStatus($statusCode);
        return true;
    }

    // 如果Router.php有setRouterNotFoundCallBack()则，路由的配置优先。
    protected function actionNotFound(?string $action): void
    {
        $this->writeJson(Status::CODE_NOT_FOUND, null, '找不到action');
    }

    protected function onException(\Throwable $throwable): void
    {
        if ($throwable instanceof ParamAnnotationValidateError) {
            $msg = $throwable->getValidate()->getError()->getErrorRuleMsg();
            $this->writeJson(400, null, "{$msg}");
        } else {
            if (Core::getInstance()->isDev()) {
                $this->writeJson(500, null, $throwable->getMessage());
            } else {
                Trigger::getInstance()->throwable($throwable);
                $this->writeJson(500, null, '系统内部错误，请稍后重试');
            }
        }
    }
}