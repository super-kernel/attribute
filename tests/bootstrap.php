#! /usr/bin/env php
<?php
declare(strict_types=1);

use SuperKernel\Annotation\Autowired;
use SuperKernel\Annotation\Provider;
use SuperKernel\Attribute\Provider\AttributeCollectorProvider;

require __DIR__ . '/../vendor/autoload.php';

$attributeCollector = new AttributeCollectorProvider()();

var_dump(
	$attributeCollector->getClassesByAttribute(Provider::class),
//	$attributeCollector->getClassAttributes(AttributeCollectorProvider::class),
//	$attributeCollector->getMethodAttributes(AttributeCollectorProvider::class, '__invoke'),
	$attributeCollector->getPropertiesByAttribute(Autowired::class),
);