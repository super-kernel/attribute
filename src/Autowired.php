<?php

declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use InvalidArgumentException;

use function class_exists;
use function interface_exists;
use function sprintf;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final readonly class Autowired
{
    /**
     * @param class-string|null $identifier
     */
    public function __construct(public ?string $identifier = null)
    {
        if (null !== $identifier && !class_exists($identifier) && !interface_exists($identifier)) {
            throw new InvalidArgumentException(sprintf('Injectable identifier %s does not exist.', $identifier));
        }
    }
}
