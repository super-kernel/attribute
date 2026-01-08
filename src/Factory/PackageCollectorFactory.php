<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Collector\PackageCollector;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;

final class PackageCollectorFactory
{
	public function __construct(private ScanHandlerInterface $packageCollector)
	{

	}

	public function __invoke()
	{
	}
}