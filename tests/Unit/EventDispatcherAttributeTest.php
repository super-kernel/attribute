<?php

declare(strict_types=1);

namespace SuperKernel\Attribute\Tests\Unit;

use Attribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SuperKernel\Attribute\Listener;
use Throwable;

final class EventDispatcherAttributeTest extends TestCase
{
    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testDeclaresClassOnlyPriorityContract(): void
    {
        $reflection = new ReflectionClass(Listener::class);
        $attribute = $reflection->getAttributes(Attribute::class);
        $constructor = $reflection->getConstructor();

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(1, $attribute);
        self::assertSame(Attribute::TARGET_CLASS, $attribute[0]->newInstance()->flags);
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());

        $priority = $constructor->getParameters()[0];

        self::assertSame('priority', $priority->getName());
        self::assertSame('int', (string) $priority->getType());
        self::assertTrue($priority->isDefaultValueAvailable());
        self::assertSame(0, $priority->getDefaultValue());
        self::assertSame(0, new Listener()->priority);
        self::assertSame(100, new Listener(100)->priority);
    }

    public function testIsNotRepeatable(): void
    {
        $listener = new #[Listener] class() {};
        $attributes = new ReflectionClass($listener)->getAttributes(Listener::class);

        self::assertCount(1, $attributes);
        self::assertSame(0, $attributes[0]->newInstance()->priority);
    }
}
