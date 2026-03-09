<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Metadata;

use SuperKernel\Attribute\Contract\AttributeInterface;
use SuperKernel\Reflection\Provider\ReflectorProvider;

final readonly class ClassAttribute implements AttributeInterface
{
	private object $instance;

	public function __construct(private string $name, private string $class, private array $arguments)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function getInstance(): object
	{
		if (!isset($this->instance)) {
			$reflection = ReflectorProvider::make()->reflectClass($this->name);
			$this->instance = $reflection->newLazyGhost(function (object $instance) {
				$instance->__construct(...$this->arguments);
			});
		}

		return $this->instance;
	}
}