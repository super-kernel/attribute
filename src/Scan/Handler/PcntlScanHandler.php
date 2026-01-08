<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan\Handler;

use Exception;
use SuperKernel\Attribute\Contract\ScanHandlerInterface;
use SuperKernel\Attribute\Contract\ScannedInterface;
use SuperKernel\Attribute\Scan\Scanned;
use function pcntl_fork;
use function pcntl_wait;

final class PcntlScanHandler implements ScanHandlerInterface
{
	/**
	 * @return ScannedInterface
	 * @throws Exception
	 */
	public function scan(): ScannedInterface
	{
		$pid = pcntl_fork();

		if (-1 == $pid) {
			throw new Exception('The process fork failed');
		}

		if ($pid) {
			pcntl_wait($status);
			if (0 === $status) {
				return new Scanned(true);
			}

			exit(-1);
		}

		return new Scanned(false);
	}
}