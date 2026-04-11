<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Node
{
	public function __construct(public string $name, public string $value)
	{
	}
}