<?php declare(strict_types=1);

namespace linron\thinkdto\attributes;

/**
 * 请求体注解
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class RequestBody
{
    public function __construct(public string $group, public string $subType = '')
    {

    }
}