<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Collector;

final readonly class Attribute
{
	public AttributeInstance $attributeInstance;

	public function __construct(public string $class, object $attributeInstance)
	{
		$this->attributeInstance = new AttributeInstance($attributeInstance);
	}
}