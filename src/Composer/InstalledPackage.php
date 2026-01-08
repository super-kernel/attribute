<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Composer;

final readonly class InstalledPackage
{
	public function __construct(
		public string $name,
		public string $version,
		public string $path,
		public array  $autoload,
		public string $hash,
	)
	{
	}
}