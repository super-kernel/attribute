<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Contract;

interface ScannedInterface
{
	public function isScanned(): bool;
}