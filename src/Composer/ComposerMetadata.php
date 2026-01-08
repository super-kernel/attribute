<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Composer;

final class ComposerMetadata
{
	public RootPackage $root;

	/** @var InstalledPackage[] */
	public array $packages = [];
}