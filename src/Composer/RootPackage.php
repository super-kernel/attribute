<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Composer;

final readonly class RootPackage
{
	public function __construct(
		public string $name,
		public string $path,
		public array  $autoload,
	)
	{
	}
}