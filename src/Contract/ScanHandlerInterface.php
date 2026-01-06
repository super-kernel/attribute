<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Contract;

interface ScanHandlerInterface
{
	public function scan(): ScannedInterface;
}