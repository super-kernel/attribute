<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Scan\Scanner;
use function spl_autoload_functions;
use function spl_autoload_unregister;

final class AttributeCollectorFactory
{
	public function __invoke(): AttributeCollectorInterface
	{
		$attributeCollector = new Scanner()->scan();

		foreach (spl_autoload_functions() as $function) {
			spl_autoload_unregister($function);
		}

		return $attributeCollector;
	}
}