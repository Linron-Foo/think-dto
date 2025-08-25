<?php

namespace linron\thinkdto\attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DefaultVal
{
    public function __construct(public mixed $value)
    {

    }
}