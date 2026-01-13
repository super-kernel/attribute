<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use RuntimeException;
use SuperKernel\Attribute\Composer\ComposerMetadata;
use SuperKernel\Attribute\Composer\InstalledPackage;
use SuperKernel\Attribute\Composer\RootPackage;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use function getcwd;

final readonly class ScanConfig
{
	public string $path;

	public function __construct(
		?string                       $path = null,
		private ?ScanHandlerInterface $handler = null,
	)
	{
		if (null === $path) {
			$path = getcwd();

			if (false === $path) {
				throw new RuntimeException('Unable to determine the current working directory.');
			}
		}

		$this->path = $path;
	}

	public function getScanHandler(): ScanHandlerInterface
	{
		return $this->handler ?? match (true) {
			new Handler\PcntlScanHandler()->support() => new Handler\PcntlScanHandler(),
			new Handler\NullScanHandler()->support()  => new Handler\NullScanHandler(),
			default                                   => throw new RuntimeException('No suitable scan handler found.'),
		};
	}

	public function getComposerMetadata(): ComposerMetadata
	{
		$meta = new ComposerMetadata();

		$json = json_decode(file_get_contents($this->path . '/composer.json'), true);
		$lock = json_decode(file_get_contents($this->path . '/composer.lock'), true);

		$meta->root = new RootPackage(
			$json['name'] ?? 'root',
			$this->path,
			$json['autoload'] ?? [],
		);

		$packages = $lock['packages'] ?? [];

		$packages = array_merge($packages, $lock['packages-dev'] ?? []);

		foreach ($packages as $pkg) {
			$path = $this->path . '/vendor/' . $pkg['name'];

			$meta->packages[] = new InstalledPackage(
				$pkg['name'],
				$pkg['version'],
				$path,
				$pkg['autoload'] ?? [],
				'',
			);
		}

		return $meta;
	}
}