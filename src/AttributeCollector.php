<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use Attribute;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Contract\AttributeInterface;
use SuperKernel\Attribute\Metadata\ClassAttribute;
use SuperKernel\Attribute\Metadata\MethodAttribute;
use SuperKernel\Attribute\Metadata\PropertyAttribute;

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
				if ($attribute instanceof ClassAttribute) {
					$attributes[$class][Attribute::TARGET_CLASS][] = $attribute;
				} elseif ($attribute instanceof MethodAttribute) {
					$attributes[$class][Attribute::TARGET_METHOD][$attribute->getMethod()][] = $attribute;
				} elseif ($attribute instanceof PropertyAttribute) {
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

			foreach ($targets[Attribute::TARGET_CLASS] ?? [] as $class) {
				if ($class->getName() === $attribute) {
					$attributes[] = $class;
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
					if ($method->getName() === $attribute) {
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
					if ($property->getName() === $attribute) {
						$attributes[] = $property;
					}
				}
			}
		}

		return $attributes;
	}
}