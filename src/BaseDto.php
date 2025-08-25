<?php declare(strict_types=1);

namespace linron\thinkdto;

use linron\thinkdto\attributes\CollectionType;
use linron\thinkdto\attributes\DefaultVal;
use linron\thinkdto\attributes\Valid;
use linron\thinkdto\validates\Validate;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use think\Collection;
use think\exception\ValidateException;

/**
 * DTO基类
 */
abstract class BaseDto
{
    public function __construct(?array $data)
    {
        $refClass = new ReflectionClass($this);
        if (!$refClass->isInstantiable()) {
            throw new RuntimeException(sprintf('Class %s cannot be instantiated', $refClass->getName()));
        }

        $props = $refClass->getProperties();

        foreach ($props as $prop) {
            $refProp = new ReflectionProperty($this, $prop->name);

            $typeName = $refProp->getType()->getName();

            if ($refProp->isProtected()) {
                $refProp->setAccessible(true);
            }

            if (!isset($data[$prop->name])) {
                $refProp->setValue($this, null);
                $attributes = $refProp->getAttributes(DefaultVal::class);
                foreach ($attributes as $vo) {
                    $rs = $vo->getArguments();
                    $val = $rs['value'];
                    $refProp->setValue($this, $val);
                }
                continue;
            }

            $value = $data[$prop->name] ?? null;

            if ($typeName === Collection::class) {
                $attributes = $refProp->getAttributes(CollectionType::class);
                $item = [];
                foreach ($attributes as $vo) {
                    $rs = $vo->getArguments();
                    $typeName = $rs['type'];
                    $item = array_map(function ($v) use ($typeName) {
                        return new $typeName($v);
                    }, (array)$value);
                }
                $value = Collection::make($item);
            }elseif (!in_array($typeName, ['int', 'float', 'array', 'string', 'bool', 'mixed', Collection::class])) {
                $value = new $typeName($value);
            }elseif ($typeName == 'int' && is_string($value) && empty($value)) {
                $value = null;
            }
            $refProp->setValue($this, $value);
        }
    }

    /**
     * 数据验证，整合TP8验证器
     * @param string $group
     * @return bool
     * @throws ValidateException|\ReflectionException
     */
    public function validate(string $group): bool
    {
        $refClass = new ReflectionClass($this);

        $rules = [];
        $messages = [];

        $properties = $refClass->getProperties();
        foreach ($properties as $key => $val) {
            $ref = new ReflectionProperty($this, $val->name);

            if ($ref->isProtected()) {
                $ref->setAccessible(true);
            }

            $attributes = $ref->getAttributes(Valid::class);
            foreach ($attributes as $k => $vo) {
                $rs = $vo->getArguments();
                $name = $rs['name'] ?? '';
                $message = $rs['message'] ?? '';
                $excludes = $rs['excludes'] ?? [];

                if (in_array($group, $excludes)) {
                    continue;
                }

                $rules[$val->name][] = $name;
                if (strpos($name, ":")) {
                    [$flag, $size] = explode(":", $name);
                    $messages[sprintf("%s.%s", $val->name, $flag)] = $message;
                } else {
                    $messages[sprintf("%s.%s", $val->name, $name)] = $message;
                }
            }

        }

        $rules = array_map(function ($vo) {
            return implode("|", $vo);
        }, $rules);

        $validate = (new Validate())->rule($rules)->message($messages);
        if (!$validate->check($this->toArray())) {
            throw new \Exception($validate->getError());
        }
        // 后置验证（如果验证规则无法满足可自定义验证）
        if (method_exists($this, 'afterValidate')) {
            $this->afterValidate();
        }
        return true;
    }

    /**
     * 对象转数组
     * @return array
     */
    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }

    /**
     * 对象转json
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

}