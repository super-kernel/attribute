<?php
declare(strict_types=1);

use SuperKernel\Attribute\AttributeCollectorFactory;

require __DIR__ . '/../../vendor/autoload.php';

var_dump(
	new AttributeCollectorFactory()(),
);