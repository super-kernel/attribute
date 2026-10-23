<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use InvalidArgumentException;

use function str_contains;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Configuration
{
    public function __construct(public ?string $className = null, public bool $default = false)
    {
        if (null !== $className && ('' === $className || str_contains($className, "\0"))) {
            throw new InvalidArgumentException(
                'Configuration className must be non-empty and contain no NUL bytes when provided.',
            );
        }
    }
}
