<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

final class Attribute
{
	public function __construct(public string $class, public object $attribute)
	{
	}
}