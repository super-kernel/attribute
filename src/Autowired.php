<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Autowired
{
	public function __construct(public ?string $class = null)
	{
	}
}