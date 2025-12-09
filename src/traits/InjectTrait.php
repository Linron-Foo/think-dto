<?php

namespace linron\thinkdto\traits;

use linron\thinkdto\attributes\Inject;

trait InjectTrait
{
    protected static array $resolvingObjects = [];
    protected static array $resolvingClasses = [];

    /**
     * 解析 Inject 注解 依赖注入
     * @param $object
     * @return void
     */
    public function parseInject($object): void
    {
        $objectId = spl_object_hash($object);
        $className = get_class($object);

        // 对象级别循环检测
        if (isset(self::$resolvingObjects[$objectId])) {
            return;
        }

        // 类级别循环检测
        if (isset(self::$resolvingClasses[$className])) {
            throw new \RuntimeException("Circular dependency detected for class: " . $className);
        }

        self::$resolvingObjects[$objectId] = true;
        self::$resolvingClasses[$className] = true;

        try {
            $refObject = new \ReflectionObject($object);
            foreach ($refObject->getProperties() as $refProperty) {
                if ($refProperty->isDefault() && !$refProperty->isStatic()) {
                    $attrs = $refProperty->getAttributes(Inject::class);
                    if (!empty($attrs)) {
                        if ($refProperty->getType() && !$refProperty->getType()->isBuiltin()) {
                            $type = $refProperty->getType()->getName();

                            // 检查要注入的类型是否正在解析中
                            if (isset(self::$resolvingClasses[$type])) {
                                // 延迟注入或跳过
                                continue;
                            }

                            $value = app()->make($type);
                            if (!$refProperty->isPublic()) {
                                $refProperty->setAccessible(true);
                            }
                            $refProperty->setValue($object, $value);

                            if (is_object($value)) {
                                $this->parseInject($value);
                            }
                        }
                    }
                }
            }
        } finally {
            unset(self::$resolvingObjects[$objectId]);
            unset(self::$resolvingClasses[$className]);
        }
    }
}