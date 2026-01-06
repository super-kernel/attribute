<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use Phar;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use SuperKernel\Attribute\Scan\Handler\NullScanHandler;
use SuperKernel\Attribute\Scan\Handler\PcntlScanHandler;

final class ScanHandlerFactory
{
	public function __invoke(): ScanHandlerInterface
	{
		return match (true) {
			!Phar::running() => new PcntlScanHandler(),
			default          => new NullScanHandler(),
		};
	}
}