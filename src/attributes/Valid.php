<?php declare(strict_types=1);

namespace linron\thinkdto\attributes;

/**
 * 验证器注解
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Valid
{
    public function __construct(public string $name, public string $message, public array $excludes = [])
    {

    }
}