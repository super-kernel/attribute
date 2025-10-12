<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Deprecated
{
    public function __construct(public string $message = '')
    {
    }
}