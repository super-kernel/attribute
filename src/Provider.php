<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use InvalidArgumentException;

use function str_contains;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Provider
{
    /**
     * @param class-string $className
     */
    public function __construct(public string $className)
    {
        if ('' === $className || str_contains($className, "\0")) {
            throw new InvalidArgumentException('Provider className must be non-empty and contain no NUL bytes.');
        }
    }
}
