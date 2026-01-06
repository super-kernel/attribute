<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use SuperKernel\Attribute\PackageCollector;

final readonly class Scanner
{
	private PackageCollector $packageCollector;

	public function __construct(private ScanHandlerInterface $scanHandler, ClassLoader $classLoader)
	{
		$this->packageCollector = new PackageCollector($classLoader);
	}

	public function scan(): AttributeCollectorInterface
	{
		$canned = $this->scanHandler->scan();

		if ($canned->isScanned()) {
			return ($this->packageCollector)();
		}

		foreach (InstalledVersions::getInstalledPackages() as $packageName) {
			$installPath = InstalledVersions::getInstallPath($packageName);

			if ($installPath) {
				$this->packageCollector->collect($packageName);
			}
		}

		$this->packageCollector->scan();

		exit;
	}
}