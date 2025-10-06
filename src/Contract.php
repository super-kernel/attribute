<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[
	Attribute(Attribute::TARGET_CLASS),
]
final class Contract
{
	public function __construct(public string $class, public int $priority = 0)
	{
	}
}