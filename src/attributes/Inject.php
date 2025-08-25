<?php

namespace linron\thinkdto\attributes;

/**
 * 依赖注入注解
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct()
    {

    }
}