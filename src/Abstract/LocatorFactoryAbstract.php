<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Abstract;

use RuntimeException;

abstract class LocatorFactoryAbstract
{
	protected readonly string $path;

	public function __construct()
	{
		$path = getcwd();

		if (!$this->path) {
			throw new RuntimeException('Failed to get the current working directory.');
		}

		$this->path = $path;
	}
}