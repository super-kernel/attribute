<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Inject
{
	public function __construct(public ?string $value = null)
	{
	}
}