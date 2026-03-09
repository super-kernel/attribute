<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Contract;

use SuperKernel\Attribute\Metadata\ClassAttribute;
use SuperKernel\Attribute\Metadata\MethodAttribute;
use SuperKernel\Attribute\Metadata\PropertyAttribute;

interface AttributeCollectorInterface
{
	/**
	 * @param string $class
	 *
	 * @return array<ClassAttribute>
	 */
	public function getClassAttributes(string $class): array;

	/**
	 * @param string $class
	 * @param string $method
	 *
	 * @return array<MethodAttribute>
	 */
	public function getMethodAttributes(string $class, string $method): array;

	/**
	 * @param string $class
	 * @param string $property
	 *
	 * @return array<PropertyAttribute>
	 */
	public function getPropertyAttributes(string $class, string $property): array;

	/**
	 * @param string $attribute
	 *
	 * @return array<ClassAttribute>
	 */
	public function getClassesByAttribute(string $attribute): array;

	/**
	 * @param string $attribute
	 *
	 * @return array<MethodAttribute>
	 */
	public function getMethodsByAttribute(string $attribute): array;

	/**
	 * @param string $attribute
	 *
	 * @return array<PropertyAttribute>
	 */
	public function getPropertiesByAttribute(string $attribute): array;
}