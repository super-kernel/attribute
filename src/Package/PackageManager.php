<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Package;

final class PackageManager
{
	private array $packages = [];

	public function add(Package $pkg): void
	{
		$this->packages[$pkg->name] = $pkg;
	}
}