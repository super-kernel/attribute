<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Configuration
{
	public function __construct(public string $interface)
	{
	}
}