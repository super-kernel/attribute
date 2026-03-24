<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Factory
{
	public function __construct()
	{
	}
}