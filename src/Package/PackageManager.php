<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Package;

use DirectoryIterator;
use SuperKernel\Attribute\Scan\ScanConfig;
use function file_get_contents;
use function is_dir;
use function mkdir;
use function unserialize;

final class PackageManager
{
	private string $path;

	/**
	 * @var array<string, Package> $packages
	 */
	private array $packages;

	public function __construct(ScanConfig $scanConfig)
	{
		$this->path = $scanConfig->path . '/vendor/.super-kernel/packages/';
	}

	public function addPackage(Package $package): void
	{
		$this->packages[$package->name] = $package;
	}

	public function addPackages(Package ...$packages): void
	{
		foreach ($packages as $package) {
			$this->addPackage($package);
		}
	}

	public function getPackageByName(string $name): ?Package
	{
		return $this->packages[$name] ?? null;
	}

	public function getPackageByType(string $type): array
	{
		$packages = [];

		foreach ($this->packages as $package) {
			if (in_array($type, $package->autoload['types'] ?? [], true)) {
				$packages[] = $package;
			}
		}

		return $packages;
	}

	public function __serialize(): array
	{
		return [
			'packages' => $this->packages,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->packages = $data['packages'] ?? [];
	}

	public function __invoke(): void
	{
		$packages = [];
		$iterator = new DirectoryIterator($this->path);

		foreach ($iterator as $file) {
			if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'cache') {
				continue;
			}

			$package = unserialize(file_get_contents($file->getPathname()));

			if ($package instanceof Package) {
				$packages[$package->name] = $package;
			}
		}

		$this->addPackages(...$packages);
	}

	public function __destruct()
	{
		if (!is_dir($this->path)) {
			@mkdir($this->path, 0777, true);
		}

		foreach ($this->packages as $package) {
			$packagePath = $this->path . '/' . str_replace('/', '_', $package->name) . '.cache';

			file_put_contents($packagePath, serialize($package));
		}
	}

	public function getPackages(): array
	{
		return $this->packages;
	}
}