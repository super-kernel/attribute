<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Collector\PackageManager;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;

final class PackageManagerFactory
{
	public function __invoke(): AttributeCollectorInterface
	{
		$packageManager = new PackageManager();

		foreach ($packageManager->getInstalledPackages() as $package) {
			$packageManager->isReloadPackage();
		}
	}
}