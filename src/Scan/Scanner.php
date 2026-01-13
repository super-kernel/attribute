<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use SuperKernel\Attribute\Factory\PackageFactory;
use SuperKernel\Attribute\Package\PackageManager;

final readonly class Scanner
{
	private ScanConfig $scanConfig;

	private PackageManager $packageManager;

	public function __construct(?ScanConfig $scanConfig = null)
	{
		$this->scanConfig = $scanConfig ?? new ScanConfig();

		$this->packageManager = new PackageManager($this->scanConfig);
	}

	public function scan(): PackageManager
	{
		$canned = $this->scanConfig->getScanHandler()->scan();

		if ($canned->isScanned()) {
			($this->packageManager)();
			
			return $this->packageManager;
		}

		$composerMetadata = $this->scanConfig->getComposerMetadata();

		$packageFactory = new PackageFactory();

		$this->packageManager->addPackage($packageFactory($composerMetadata->root));

		foreach ($composerMetadata->packages as $package) {
			$this->packageManager->addPackage($packageFactory($package));
		}

		exit;
	}
}