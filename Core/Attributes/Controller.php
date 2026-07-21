<?php

declare(strict_types=1);

namespace Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public string $prefix = ''
    ) {
    }
}