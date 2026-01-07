<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use Composer\InstalledVersions;
use SuperKernel\Attribute\Collector\PackageCollector;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;

final readonly class Scanner
{
	private PackageCollector $packageCollector;

	public function __construct(private ScanHandlerInterface $scanHandler)
	{
		$this->packageCollector = new PackageCollector();
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