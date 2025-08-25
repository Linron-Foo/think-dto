<?php

namespace linron\thinkdto\attributes;

/**
 * DTO字段默认值注解
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DefaultVal
{
    public function __construct(public mixed $value)
    {

    }
}