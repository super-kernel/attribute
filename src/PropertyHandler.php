<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use InvalidArgumentException;

use function str_contains;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class PropertyHandler
{
    /**
     * @param class-string $attribute
     */
    public function __construct(public string $attribute)
    {
        if ('' === $attribute || str_contains($attribute, "\0")) {
            throw new InvalidArgumentException('PropertyHandler attribute must be non-empty and contain no NUL bytes.');
        }
    }
}
