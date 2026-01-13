<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use SuperKernel\Attribute\Collector\AttributeCollector;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Scan\Scanner;

final class AttributeCollectorFactory
{
	public function __invoke(): AttributeCollectorInterface
	{
		$packageManager = new Scanner()->scan();

		var_dump($packageManager);

		$attributes = [];

		foreach ($packageManager->getPackages() as $package) {
			var_dump($package);
		}

		return new AttributeCollector($attributes);
	}
}