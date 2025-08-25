<?php declare(strict_types=1);

namespace linron\thinkdto\traits;

use linron\thinkdto\attributes\RequestBody;
use linron\thinkdto\BaseDto;
use think\Request;
use think\Collection;
use think\helper\Str;

/**
 * 请求请求中间件的 Trait
 */
trait ThinkMvcTrait
{
    /**
     * 解析控制器和方法
     * @param Request $request
     * @return array
     */
    public function parseControllerAndMethod(Request $request): array
    {
        $controller = $request->controller();
        $action = $request->action();
        $moduleName = app('http')->getName();
        $moduleName = $moduleName ? sprintf("\%s", $moduleName) : "";
        if (str_contains($request->controller(), ".")) {
            [$lv, $control] = explode(".", $controller);
            $controllerClass = sprintf("app%s\controller\%s\%s", $moduleName, $lv, $control);
        } else {
            $controllerClass = sprintf("app%s\controller\%s", $moduleName, $controller);
        }

        $actionMethod = $action;
        return [$controllerClass, $actionMethod];
    }

    /**
     * 解析控制器里面的方法参数
     * @param \ReflectionMethod $refMethod
     * @param array $requestData
     * @param string $group
     * @param string $subType
     * @return array
     */
    public function parseParams(\ReflectionMethod $refMethod, array $requestData, string $group, string $subType): array
    {
        $params = $refMethod->getParameters();
        $args = [];
        foreach ($params as $param) {
            $paramType = $param->getType();
            $paramName = $param->getName();
            if ($paramType && !$paramType->isBuiltin()) {
                $className = $paramType->getName();
                if ($className === Collection::class) {
                    if (!$subType) {
                        throw new \RuntimeException("Please add [subType] param at RequestBody Attribute");
                    }
                    $args[] = $this->parseCollection($subType, $requestData, $group);
                } else {
                    $args[] = $this->parseDto($className, $requestData, $group);
                }
            } else {
                $args[] = $requestData[$paramName] ?? null;
            }
        }
        return $args;
    }

    /**
     * 解析 RequestBody 注解
     * @param \ReflectionMethod $refMethod
     * @return array
     */
    public function parseRequestBody(\ReflectionMethod $refMethod): array
    {
        $attributes = $refMethod->getAttributes(RequestBody::class);
        $subType = '';
        $group = '';
        if (!empty($attributes)) {
            $attribute = $attributes[0];
            $subType = $attribute->getArguments()['subType'] ?? '';
            $group = $attribute->getArguments()['group'] ?? '';
        }

        return [$group, $subType];
    }

    public function parseCollection(string $subType, array $data, string $group): Collection
    {
        return Collection::make(array_map(function ($vo) use ($subType, $group) {
            return $this->parseDto($subType, $vo, $group);
        }, $data));
    }

    /**
     * 解析请求实体
     * @param string $className
     * @param array $data
     * @param string $group
     * @return BaseDto
     * @throws \ReflectionException
     */
    public function parseDto(string $className, array $data, string $group): BaseDto
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Class {$className} does not exist");
        }
        /** @var BaseDto $dto */
        $dto = new $className($data);
        $dto->validate($group);

        $refDtoClass = new \ReflectionClass($dto);
        $props = $refDtoClass->getProperties();
        foreach ($props as $prop) {
            $typeName = $prop->getType()->getName();
            $propName = $prop->getName();
            $getter = "get" . Str::studly($prop->getName());
            if ($typeName === Collection::class) {
                /** @var Collection $items */
                //$items = $dto->$getter();
                $items = $dto->$propName;
                $items?->each(function (BaseDto $vo) use ($group) {
                    $refObj = new \ReflectionObject($vo);
                    $this->parseDto($refObj->getName(), $vo->toArray(), $group);
                });
            } elseif (!in_array($typeName, ['int', 'float', 'array', 'string', 'bool', 'mixed', Collection::class])) {
                /** @var BaseDto $dto */
                //$dto = $dto->$getter();
                $dto = $dto->$propName;
                $this->parseDto($typeName, $dto->toArray(), $group);
            }
        }

        return $dto;
    }

}