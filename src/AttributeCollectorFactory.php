<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Scan\ScanHandlerFactory;
use SuperKernel\Attribute\Scan\Scanner;

use LogicException;
use function spl_autoload_functions;

final class AttributeCollectorFactory
{
	public function __invoke(): AttributeCollectorInterface
	{
		if (!empty(spl_autoload_functions())) {
			echo "\n\033[33m" . <<<TXT
[Autoloader Policy Violation]

Super-Kernel enforces a strict and deterministic class loading model.
The use of Composer or any third-party class autoloader is strictly prohibited within the skernel runtime environment.

One or more external autoloader have been detected at runtime.

This violates the class loading boundaries defined by Super-Kernel and may lead to undefined behavior, runtime instability, or production risks.

Please remove all external autoloader and use the class loading mechanism provided by Super-Kernel.
TXT . "\033[0m";
		}
		return (new Scanner(new ScanHandlerFactory()))->scan();
	}
}