<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use stdClass;
use function get_object_vars;

final readonly class Attribute
{
	public AttributeInstance $attributeInstance;

	public function __construct(public string $class, object $attributeInstance)
	{
		$this->attributeInstance = new AttributeInstance($attributeInstance);
	}
}