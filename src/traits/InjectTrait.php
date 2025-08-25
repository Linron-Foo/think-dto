<?php

namespace linron\thinkdto\traits;

use linron\thinkdto\attributes\Inject;

trait InjectTrait
{
    /**
     * 解析 Inject 注解 依赖注入
     * @param $object
     * @return void
     */
    public function parseInject($object): void
    {
        $refObject = new \ReflectionObject($object);
        foreach ($refObject->getProperties() as $refProperty) {
            if ($refProperty->isDefault() && !$refProperty->isStatic()) {
                $attrs = $refProperty->getAttributes(Inject::class);
                if (!empty($attrs)) {
                    if ($refProperty->getType() && !$refProperty->getType()->isBuiltin()) {
                        $type = $refProperty->getType()->getName();
                    }

                    if (isset($type)) {
                        $value = app()->make($type);
                        if (!$refProperty->isPublic()) {
                            $refProperty->setAccessible(true);
                        }
                        $refProperty->setValue($object, $value);
                    }
                }
            }
        }
    }
}