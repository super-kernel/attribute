<?php

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Composer\InstalledPackage;
use SuperKernel\Attribute\Composer\RootPackage;
use SuperKernel\Attribute\Package\Package;

final class PackageFactory
{
	public function __invoke(InstalledPackage|RootPackage $meta): Package
	{
		return new Package(
			$meta->name,
			$meta->path,
			$meta->autoload,
			$meta instanceof InstalledPackage ? $meta->hash : 'root',
		);
	}
}