<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Package;

final class Package
{
	public array $classmap = [];

	public function __construct(
		public readonly string $name,
		public readonly string $path,
		public readonly array  $autoload,
		public readonly string $hash,
	)
	{
	}

	public function __serialize(): array
	{
		return get_object_vars($this);
	}

	public function __unserialize(array $data): void
	{
		foreach ($data as $k => $v) {
			$this->{$k} = $v;
		}
	}
}