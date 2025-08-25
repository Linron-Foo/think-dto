<?php declare(strict_types=1);

namespace linron\thinkdto\attributes;

/**
 * DTO数组类型转换器注解
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CollectionType
{
    public function __construct(public string $type)
    {

    }
}