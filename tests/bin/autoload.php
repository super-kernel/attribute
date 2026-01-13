<?php
declare(strict_types=1);

use SuperKernel\Attribute\AttributeCollectorFactory;
use SuperKernel\Contract\ApplicationInterface;
use SuperKernel\Di\Container;

require __DIR__ . '/../../vendor/autoload.php';

new Container(new AttributeCollectorFactory()())->get(ApplicationInterface::class)->run();