<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Scan;

use SuperKernel\Attribute\Contract\ScannedInterface;

final readonly class Scanned implements ScannedInterface
{
	public function __construct(private bool $scanned)
	{
	}

	public function isScanned(): bool
	{
		return $this->scanned;
	}
}