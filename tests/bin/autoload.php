<?php
declare(strict_types=1);

use SuperKernel\Attribute\AttributeCollectorFactory;

require __DIR__ . '/../../vendor/autoload.php';

new Container(new AttributeCollectorFactory()())->get(Application::class)->run();