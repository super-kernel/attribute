<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Definer
{
    public function __construct(public int $priority = 0)
    {
    }
}
