<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Collector;

use stdClass;

final readonly class AttributeInstance
{
	private stdClass $instance;

	public function __construct(object $instance)
	{
		$this->instance = new stdClass();

		foreach (get_object_vars($instance) as $name => $value) {
			$this->instance->{$name} = $value;
		}
	}

	public function __get(string $name)
	{
		if (property_exists($this->instance, $name)) {
			return $this->instance->{$name};
		}

		return null;
	}
}