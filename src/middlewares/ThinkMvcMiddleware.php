<?php

namespace linron\thinkdto\middlewares;

use linron\thinkdto\traits\InjectTrait;
use linron\thinkdto\traits\ThinkMvcTrait;
use think\Request;

/**
 * dto请求中间件
 */
class ThinkMvcMiddleware
{
    use ThinkMvcTrait;
    use InjectTrait;

    /**
     * @throws \ReflectionException
     */
    public function handle(Request $request, \Closure $next)
    {
        [$controllerClass, $actionMethod] = $this->parseControllerAndMethod($request);

        $requestData = $request->post();

        $refMethod = new \ReflectionMethod($controllerClass, $actionMethod);

        //解析 RequestBody 注解
        [$group, $subType] = $this->parseRequestBody($refMethod);

        //解析控制器方法参数
        $args = $this->parseParams($refMethod, $requestData, $group, $subType);

        //实例化控制器
        $controller = new $controllerClass(app());

        //解析 Inject 注解
        $this->parseInject($controller);

        return $refMethod->invokeArgs($controller, $args);
    }
}