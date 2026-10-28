<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use InvalidArgumentException;

use function array_values;
use function str_contains;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Provision
{
    /** @var list<non-empty-string> */
    public array $name;

    /** @throws InvalidArgumentException When any supplied name is empty or contains NUL bytes */
    public function __construct(string ...$name)
    {
        foreach ($name as $value) {
            if ('' === $value || str_contains($value, "\0")) {
                throw new InvalidArgumentException('Provision name must be non-empty and contain no NUL bytes.');
            }
        }

        $this->name = array_values($name);
    }
}
