<?php

declare(strict_types=1);

namespace SuperKernel\Attribute\Tests\Unit;

use Attribute;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stringable;
use SuperKernel\Attribute\Configuration;
use Throwable;

final class ConfigAttributeTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidClassNames(): iterable
    {
        yield 'empty' => [
            '',
        ];
        yield 'NUL byte' => [
            "Acme\\Contract\0Invalid",
        ];
    }

    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testDeclaresAnOptionalClassOrMethodConfigurationContract(): void
    {
        $reflection = new ReflectionClass(Configuration::class);
        $attribute = $reflection->getAttributes(Attribute::class);
        $constructor = $reflection->getConstructor();

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(1, $attribute);
        self::assertSame(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD, $attribute[0]->newInstance()->flags);
        self::assertNotNull($constructor);
        self::assertCount(2, $constructor->getParameters());

        $className = $constructor->getParameters()[0];

        self::assertSame('className', $className->getName());
        self::assertSame('?string', (string) $className->getType());
        self::assertTrue($className->isDefaultValueAvailable());
        self::assertNull($className->getDefaultValue());
        self::assertNull(new Configuration()->className);
        self::assertSame(Stringable::class, new Configuration(Stringable::class)->className);

        $default = $constructor->getParameters()[1];
        self::assertSame('default', $default->getName());
        self::assertSame('bool', (string) $default->getType());
        self::assertTrue($default->isDefaultValueAvailable());
        self::assertFalse($default->getDefaultValue());
        self::assertFalse(new Configuration()->default);
        self::assertTrue(new Configuration(default: true)->default);
    }

    /** @throws Throwable When native class or method metadata cannot be instantiated */
    public function testInstantiatesConfigurationOnClassesAndMethods(): void
    {
        $source = new #[Configuration(Stringable::class)] class() {
            #[Configuration(default: true)]
            public function primary(): void
            {
            }
        };
        $reflection = new ReflectionClass($source);
        $class = $reflection->getAttributes(Configuration::class)[0]->newInstance();
        $method = $reflection->getMethod('primary')->getAttributes(Configuration::class)[0]->newInstance();

        self::assertSame(Stringable::class, $class->className);
        self::assertFalse($class->default);
        self::assertNull($method->className);
        self::assertTrue($method->default);
    }

    #[DataProvider('invalidClassNames')]
    public function testRejectsAnInvalidExplicitClassName(string $className): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('className must be non-empty and contain no NUL bytes');

        new Configuration($className);
    }
}
