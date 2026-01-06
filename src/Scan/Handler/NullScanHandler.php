<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan\Handler;

use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use SuperKernel\Attribute\Contract\ScannedInterface;
use SuperKernel\Attribute\Scan\Scanned;

final class NullScanHandler implements ScanHandlerInterface
{
	public function scan(): ScannedInterface
	{
		return new Scanned(true);
	}
}