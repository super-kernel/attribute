<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use SuperKernel\Contract\AttributeInterface;

final readonly class AttributeMetadata
{
	/**
	 * @var array<AttributeInterface>
	 */
	private array $attributes;

	public function __construct(private string $name, private ?string $reference, AttributeInterface ...$attributes)
	{
		$this->attributes = $attributes;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getReference(): ?string
	{
		return $this->reference;
	}

	/**
	 * @return array<AttributeInterface>
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}
}