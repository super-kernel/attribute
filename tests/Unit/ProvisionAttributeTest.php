<?php

declare(strict_types=1);

namespace SuperKernel\Attribute\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use SuperKernel\Attribute\Provision;

final class ProvisionAttributeTest extends TestCase
{
    /** @return iterable<string, array{array<int|string, string>, list<non-empty-string>}> */
    public static function names(): iterable
    {
        yield 'no names' => [
            [],
            [],
        ];
        yield 'one name' => [
            [
                'clock',
            ],
            [
                'clock',
            ],
        ];
        yield 'preserves order and duplicates' => [
            [
                'clock',
                'system-clock',
                'clock',
            ],
            [
                'clock',
                'system-clock',
                'clock',
            ],
        ];
        yield 'normalizes named arguments' => [
            [
                'name'  => 'clock',
                'alias' => 'system-clock',
            ],
            [
                'clock',
                'system-clock',
            ],
        ];
    }

    /**
     * @param array<int|string, string> $names
     * @param list<non-empty-string>    $expected
     */
    #[DataProvider('names')]
    public function testRetainsNamesAsAnOrderedList(array $names, array $expected): void
    {
        self::assertSame($expected, new Provision(...$names)->name);
    }

    /** @return iterable<string, array{list<string>}> */
    public static function invalidNames(): iterable
    {
        yield 'empty first name' => [
            [
                '',
                'clock',
            ],
        ];
        yield 'empty later name' => [
            [
                'clock',
                '',
            ],
        ];
        yield 'NUL in first name' => [
            [
                "clock\0invalid",
                'system-clock',
            ],
        ];
        yield 'NUL in later name' => [
            [
                'clock',
                "system-clock\0invalid",
            ],
        ];
    }

    /** @param list<string> $names */
    #[DataProvider('invalidNames')]
    public function testRejectsInvalidNamesAtAnyPosition(array $names): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('name must be non-empty and contain no NUL bytes');

        new Provision(...$names);
    }

    /** @throws ReflectionException When the factory method cannot be reflected */
    public function testInstantiatesVariadicNamesFromMethodMetadata(): void
    {
        $factory = new class() {
            #[Provision('clock', 'system-clock')]
            public function clock(): void
            {
            }
        };
        $method = new ReflectionClass($factory)->getMethod('clock');
        $attribute = $method->getAttributes(Provision::class)[0]->newInstance();

        self::assertSame([
            'clock',
            'system-clock',
        ], $attribute->name);
    }
}
