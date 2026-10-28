<?php

declare(strict_types=1);

namespace SuperKernel\Attribute\Tests\Unit;

use Attribute;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Stringable;
use SuperKernel\Attribute\Autowired;
use SuperKernel\Attribute\Definer;
use SuperKernel\Attribute\FactoryProvider;
use SuperKernel\Attribute\PropertyHandler;
use SuperKernel\Attribute\Provider;
use SuperKernel\Attribute\Resolver;
use Throwable;

use function class_exists;
use function dirname;

final class DiAttributeTest extends TestCase
{
    public function testRetiredFactoryAndInjectAttributeSourcesAreAbsent(): void
    {
        self::assertFalse(class_exists('SuperKernel\\Attribute\\Factory', false));
        self::assertFileDoesNotExist(dirname(__DIR__, 2) . '/src/Factory.php');
        self::assertFalse(class_exists('SuperKernel\\Attribute\\Inject', false));
        self::assertFileDoesNotExist(dirname(__DIR__, 2) . '/src/Inject.php');
    }

    /**
     * @return iterable<string, array{class-string<Definer|Resolver>}>
     */
    public static function priorityAttributes(): iterable
    {
        yield 'definer' => [
            Definer::class,
        ];
        yield 'resolver' => [
            Resolver::class,
        ];
    }

    /**
     * @param class-string<Definer|Resolver> $attributeClass
     *
     * @throws Throwable When fixture setup or the operation under test throws
     */
    #[DataProvider('priorityAttributes')]
    public function testDeclaresClassOnlyPriorityContract(string $attributeClass): void
    {
        $reflection = new ReflectionClass($attributeClass);
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
        self::assertSame(0, new $attributeClass()->priority);
        self::assertSame(100, new $attributeClass(100)->priority);
    }

    /**
     * @return iterable<string, array{class-string<FactoryProvider|Provider>, int}>
     */
    public static function mappingAttributes(): iterable
    {
        yield 'provider' => [
            Provider::class,
            Attribute::TARGET_CLASS,
        ];
        yield 'factory provider' => [
            FactoryProvider::class,
            Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE,
        ];
    }

    /**
     * @return iterable<string, array{class-string<FactoryProvider|Provider>, string}>
     */
    public static function invalidMappingClassNames(): iterable
    {
        foreach ([
            'provider'         => Provider::class,
            'factory provider' => FactoryProvider::class,
        ] as $label => $attributeClass) {
            yield $label . ', empty' => [
                $attributeClass,
                '',
            ];
            yield $label . ', NUL byte' => [
                $attributeClass,
                "Acme\\Service\0Invalid",
            ];
        }
    }

    /**
     * @param class-string<FactoryProvider|Provider> $attributeClass
     *
     * @throws Throwable When fixture setup or the operation under test throws
     */
    #[DataProvider('mappingAttributes')]
    public function testDeclaresClassOnlyClassNameContract(
        string $attributeClass,
        int    $expectedFlags,
    ): void
    {
        $reflection = new ReflectionClass($attributeClass);
        $attribute = $reflection->getAttributes(Attribute::class);
        $constructor = $reflection->getConstructor();

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(1, $attribute);
        self::assertSame($expectedFlags, $attribute[0]->newInstance()->flags);
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());

        $className = $constructor->getParameters()[0];

        self::assertSame('className', $className->getName());
        self::assertSame('string', (string) $className->getType());
        self::assertFalse($className->isDefaultValueAvailable());
        self::assertSame(stdClass::class, new $attributeClass(stdClass::class)->className);
    }

    /**
     * @param class-string<FactoryProvider|Provider> $attributeClass
     */
    #[DataProvider('invalidMappingClassNames')]
    public function testMappingAttributesRejectInvalidClassNames(string $attributeClass, string $className): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('must be non-empty and contain no NUL bytes');

        new $attributeClass($className);
    }

    public function testFactoryProviderCanMapOneCallableClassToMultipleIdentifiers(): void
    {
        $factory = new #[FactoryProvider(DiAttributeTest::class)] #[FactoryProvider(stdClass::class)] class() {};
        $attributes = new ReflectionClass($factory)->getAttributes(FactoryProvider::class);

        self::assertCount(2, $attributes);
        self::assertSame(DiAttributeTest::class, $attributes[0]->newInstance()->className);
        self::assertSame(stdClass::class, $attributes[1]->newInstance()->className);
    }

    public function testPropertyHandlerMapsOneHandlerClassToMultiplePropertyAttributes(): void
    {
        $reflection = new ReflectionClass(PropertyHandler::class);
        $attribute = $reflection->getAttributes(Attribute::class);
        $constructor = $reflection->getConstructor();

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(1, $attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE,
            $attribute[0]->newInstance()->flags,
        );
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());

        $propertyAttribute = $constructor->getParameters()[0];

        self::assertSame('attribute', $propertyAttribute->getName());
        self::assertSame('string', (string) $propertyAttribute->getType());
        self::assertFalse($propertyAttribute->isDefaultValueAvailable());

        $handler = new #[PropertyHandler(Autowired::class)] #[PropertyHandler(DiAttributeTest::class)] class() {};
        $mappings = new ReflectionClass($handler)->getAttributes(PropertyHandler::class);

        self::assertCount(2, $mappings);
        self::assertSame(Autowired::class, $mappings[0]->newInstance()->attribute);
        self::assertSame(self::class, $mappings[1]->newInstance()->attribute);
    }

    #[DataProvider('invalidClassNames')]
    public function testPropertyHandlerRejectsAnInvalidAttributeClassName(string $className): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('must be non-empty and contain no NUL bytes');

        new PropertyHandler($className);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidClassNames(): iterable
    {
        yield 'empty' => [
            '',
        ];
        yield 'NUL byte' => [
            "Acme\\Attribute\0Invalid",
        ];
    }

    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testAutowiredTargetsPropertiesAndMethodsAndAcceptsClassOrInterfaceIdentifiers(): void
    {
        $reflection = new ReflectionClass(Autowired::class);
        $attribute = $reflection->getAttributes(Attribute::class);
        $constructor = $reflection->getConstructor();

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(1, $attribute);
        self::assertSame(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD, $attribute[0]->newInstance()->flags);
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());

        [$identifier] = $constructor->getParameters();

        self::assertSame('identifier', $identifier->getName());
        self::assertSame('?string', (string) $identifier->getType());
        self::assertTrue($identifier->isDefaultValueAvailable());
        self::assertNull($identifier->getDefaultValue());
        self::assertFalse($reflection->hasProperty('lazy'));

        self::assertNull(new Autowired()->identifier);
        self::assertSame(stdClass::class, new Autowired(stdClass::class)->identifier);
        self::assertSame(Stringable::class, new Autowired(Stringable::class)->identifier);
    }

    /** @throws Throwable When native attribute metadata cannot be instantiated */
    public function testAutowiredCanBeInstantiatedOnPropertiesAndMethods(): void
    {
        $consumer = new class() {
            #[Autowired]
            public stdClass $dependency;

            #[Autowired(Stringable::class)]
            public function configure(): void
            {
            }
        };
        $reflection = new ReflectionClass($consumer);
        $property = $reflection->getProperty('dependency')->getAttributes(Autowired::class)[0]->newInstance();
        $method = $reflection->getMethod('configure')->getAttributes(Autowired::class)[0]->newInstance();

        self::assertNull($property->identifier);
        self::assertSame(Stringable::class, $method->identifier);
    }

    public function testAutowiredRejectsAnUndefinedClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Autowired('SuperKernel\\Attribute\\MissingClass');
    }
}
