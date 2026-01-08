<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Composer\ComposerMetadata;
use SuperKernel\Attribute\Composer\InstalledPackage;
use SuperKernel\Attribute\Composer\RootPackage;
use function array_merge;
use function file_get_contents;
use function json_decode;

final class ComposerMetadataFactory
{
	public function __invoke(string $path): ComposerMetadata
	{
		$meta = new ComposerMetadata();

		$json = json_decode(file_get_contents($path . '/composer.json'), true);
		$lock = json_decode(file_get_contents($path . '/composer.lock'), true);

		$meta->root = new RootPackage(
			$json['name'] ?? 'root',
			$path,
			$json['autoload'] ?? [],
		);

		$packages = $lock['packages'] ?? [];

		$packages = array_merge($packages, $lock['packages-dev'] ?? []);

		foreach ($packages as $pkg) {
			$path = $path . '/vendor/' . $pkg['name'];

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