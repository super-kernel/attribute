<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use Composer\InstalledVersions;
use SuperKernel\Attribute\Collector\PackageCollector;
use SuperKernel\Attribute\Collector\PackageManager;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;

final readonly class Scanner
{
	private ScanHandlerInterface $scanHandler;

	private PackageManager $packageManager;

	public function __construct()
	{
		$this->scanHandler = new ScanHandlerFactory()();

		$this->packageManager = new PackageManager();
	}

	public function scan(): AttributeCollectorInterface
	{
		$canned = $this->scanHandler->scan();

		if ($canned->isScanned()) {
			return ($this->packageCollector)();
		}

		foreach ($this->packageManager->getPackages() as $package) {

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