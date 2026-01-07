<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Factory;

use SuperKernel\Attribute\Abstract\LocatorFactoryAbstract;
use SuperKernel\Attribute\Locator\VersionLocator;

final class VersionLocatorFactory extends LocatorFactoryAbstract
{
	private ?VersionLocator $instance = null;

	public function __invoke()
	{
		if (null === $this->instance) {
			$this->instance = new VersionLocator($this->path);
		}

		return ($this->instance)();
	}
}