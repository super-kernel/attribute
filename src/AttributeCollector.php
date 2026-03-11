<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use SuperKernel\Contract\AttributeCollectorInterface;
use SuperKernel\Contract\AttributeInterface;

final readonly class AttributeCollector implements AttributeCollectorInterface
{
	/**
	 * @var array<string, AttributeInterface> $attributes
	 */
	private array $attributes;

	public function __construct(AttributeMetadata ...$attributesMetadata)
	{
		$attributes = [];
		foreach ($attributesMetadata as $attributeMetadata) {
			foreach ($attributeMetadata->getAttributes() as $attribute) {
				$class = $attribute->getClass();
				if ($attribute->compatible(AttributeInterface::TARGET_CLASS)) {
					$attributes[$class][Attribute::TARGET_CLASS][] = $attribute;
				} elseif ($attribute->compatible(AttributeInterface::TARGET_METHOD)) {
					$attributes[$class][Attribute::TARGET_METHOD][$attribute->getMethod()][] = $attribute;
				} elseif ($attribute->compatible(AttributeInterface::TARGET_PROPERTY)) {
					$attributes[$class][Attribute::TARGET_PROPERTY][$attribute->getProperty()][] = $attribute;
				}
			}
		}

		$this->attributes = $attributes;
	}

	public function getClassAttributes(string $class): array
	{
		return $this->attributes[$class][Attribute::TARGET_CLASS] ?? [];
	}

	public function getMethodAttributes(string $class, string $method): array
	{
		return $this->attributes[$class][Attribute::TARGET_METHOD][$method] ?? [];
	}

	public function getPropertyAttributes(string $class, string $property): array
	{
		return $this->attributes[$class][Attribute::TARGET_PROPERTY][$property] ?? [];
	}

	public function getClassesByAttribute(string $attribute): array
	{
		$attributes = [];

		foreach ($this->attributes as $targets) {
			if (!isset($targets[Attribute::TARGET_CLASS])) {
				continue;
			}

			/* @var AttributeInterface $classAttribute */
			foreach ($targets[Attribute::TARGET_CLASS] ?? [] as $classAttribute) {
				if ($classAttribute->getAttribute() === $attribute) {
					$attributes[] = $classAttribute;
				}
			}
		}

		return $attributes;
	}

	public function getMethodsByAttribute(string $attribute): array
	{
		$attributes = [];

		foreach ($this->attributes as $targets) {
			if (!isset($targets[Attribute::TARGET_METHOD])) {
				continue;
			}

			foreach ($targets[Attribute::TARGET_METHOD] ?? [] as $methods) {
				foreach ($methods as $method) {
					if ($method->getAttribute() === $attribute) {
						$attributes[] = $method;
					}
				}
			}
		}

		return $attributes;
	}

	public function getPropertiesByAttribute(string $attribute): array
	{
		$attributes = [];

		foreach ($this->attributes as $targets) {
			if (!isset($targets[Attribute::TARGET_PROPERTY])) {
				continue;
			}

			foreach ($targets[Attribute::TARGET_PROPERTY] ?? [] as $properties) {
				foreach ($properties as $property) {
					if ($property->getAttribute() === $attribute) {
						$attributes[] = $property;
					}
				}
			}
		}

		return $attributes;
	}
}