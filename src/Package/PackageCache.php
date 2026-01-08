<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Package;

final class PackageCache
{
	private string $dir;

	public function __construct(string $cwd)
	{
		$this->dir = $cwd . '/vendor/.skernel/package';
		@mkdir($this->dir, 0777, true);
	}

	public function load(string $name): ?Package
	{
		$file = $this->dir . '/' . str_replace('/', '_', $name) . '.cache';
		return file_exists($file) ? unserialize(file_get_contents($file)) : null;
	}

	public function save(Package $pkg): void
	{
		$file = $this->dir . '/' . str_replace('/', '_', $pkg->name) . '.cache';
		file_put_contents($file, serialize($pkg));
	}

	public function loadAll(): void
	{
	}
}