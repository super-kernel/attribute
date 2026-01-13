<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan\Handler;

use Phar;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use SuperKernel\Attribute\Contract\ScannedInterface;
use SuperKernel\Attribute\Scan\Scanned;
use function extension_loaded;

final class NullScanHandler implements ScanHandlerInterface
{
	public function support(): bool
	{
		return extension_loaded('phar') && Phar::running(false) !== '';
	}

	public function scan(): ScannedInterface
	{
		return new Scanned(true);
	}
}