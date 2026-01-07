<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Abstract\LocatorFactoryAbstract;
use SuperKernel\Attribute\Locator\VersionLockLocator;

final class VersionLockLocatorFactory extends LocatorFactoryAbstract
{
	private ?VersionLockLocator $instance = null;

	public function __invoke()
	{
		if (null === $this->instance) {
			$this->instance = new VersionLockLocator($this->path);
		}

		return ($this->instance)();
	}
}