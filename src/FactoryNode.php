<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class FactoryNode
{
	public function __construct(public string $class)
	{
	}
}