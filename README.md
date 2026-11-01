<p align="right">
  <strong>English</strong> | <a href="README.zh-CN.md">中文文档</a>
</p>

# SuperKernel Attribute

`super-kernel/attribute` defines native PHP Attribute contracts shared by SuperKernel components, third-party components, and interchangeable framework implementations. It contains declarations only: collection, caching, interpretation, and execution remain with the components that consume each Attribute. Every Attribute lives in the `SuperKernel\Attribute` namespace.

The package does not depend on `super-kernel/di`, so another DI implementation may consume the same contracts without depending on the official container.

## Configuration Attribute

`SuperKernel\Attribute\Configuration` is a non-repeatable class or method marker. On a class it declares a typed configuration source. Its optional `?string $className = null` identifies the configuration item interface explicitly; omitting it asks the Config component to infer one most-specific implemented interface for a single source.

```php
use SuperKernel\Attribute\Configuration;

#[Configuration(ServerConfigInterface::class)]
final readonly class ApplicationServerConfig implements ServerConfigInterface
{
    // Typed configuration methods.
}
```

When supplied, `className` must be a non-empty string without NUL bytes. The Attribute does not validate interfaces, collect declarations, resolve source objects, or choose a DI binding; `super-kernel/config` handles existence, implementation compatibility, omitted-interface inference, collection, and source resolution.

On a method, `Configuration` marks a named configuration item. Its second optional parameter, `bool $default = false`, identifies an explicit default among named configuration items; pass it by name. A sole item is selected automatically; with several items and no explicit default, named lookup works and only unnamed lookup fails. A group is marked by Contract's `MultipleConfigInterface` and declares its item interface explicitly on the class-level `Configuration`. Method-level `className` can be omitted or must match that exact interface. A method Attribute does not declare a source independently of its group class.

```php
#[Configuration(default: true)]
public function primary(): ServerConfigInterface
{
    return new ApplicationServerConfig();
}
```

## DI Attributes

`Definer`, `Resolver`, and `Provider` are non-repeatable class-level declarations. `FactoryProvider` and `PropertyHandler` are repeatable class-level declarations. `Provision` is a non-repeatable method declaration, and `Autowired` is a non-repeatable property or method declaration:

- `Definer` marks a definition provider and carries an integer `priority` that defaults to `0`.
- `Resolver` marks a definition resolver and carries an integer `priority` that defaults to `0`.
- `Provider` maps the annotated implementation class to one required `className` identifier.
- Each `FactoryProvider` declaration maps the annotated callable provider class to one required `className` identifier.
- `Provision` marks a factory method that supplies an instance and retains zero or more service names.
- Each `PropertyHandler` declaration maps the annotated handler class to one native property Attribute class.
- `Autowired` accepts only `?string $identifier = null`. A supplied identifier must name an existing class or interface. For properties, `null` requests type inference; there is no lazy-loading switch.

The required `className` values on `Provider` and `FactoryProvider`, and the required Attribute class name on `PropertyHandler`, reject empty strings and NUL bytes when the declaration is constructed. Consumers validate only mapping compatibility, native Attribute targets, and duplicate ownership.

Higher integer priorities run before lower priorities; equal priorities preserve discovery order. A `Provider` declares a compatible mapping from its annotated class or interface to the mapped identifier; an instantiable class may also register itself with `#[Provider(OwnClass::class)]` for normal DI construction. A `FactoryProvider` declares that its annotated class is a special provider whose public, non-static `__invoke()` method supplies the mapped entry. The factory-provider class may omit its constructor or expose a public one, while the consuming implementation resolves constructor and method parameters.

```php
use SuperKernel\Attribute\FactoryProvider;

#[FactoryProvider(ClockInterface::class)]
#[FactoryProvider(SystemClockInterface::class)]
final class ClockProvider
{
    public function __construct(private ClockConfiguration $configuration)
    {
    }

    public function __invoke(): ClockInterface
    {
        return new SystemClock($this->configuration->timezone());
    }
}
```

Repeated `FactoryProvider` declarations keep their own `className` values and declaration order. Consumers may map each distinct identifier to the same callable provider class; repeating an already mapped identifier is a duplicate-mapping error.

`SuperKernel\Attribute\Provision` accepts `string ...$name` and stores the values in the public readonly `name` list. No arguments produce an empty list; each supplied name must be non-empty and contain no NUL bytes. Positional and named arguments preserve value order and duplicates; argument keys are discarded. Explicit `null` is invalid. Factory arguments belong to the method signature. `super-kernel/config` executes `Provision` methods through DI.

```php
use SuperKernel\Attribute\Provision;

final class ApplicationServices
{
    #[Provision('clock', 'system-clock')]
    public function clock(ClockConfiguration $configuration): ClockInterface
    {
        return new SystemClock($configuration->timezone());
    }
}
```

`PropertyHandler` does not replace PHP's native Attribute declaration. A mapped Attribute class declares its own `#[Attribute(Attribute::TARGET_PROPERTY)]` target. A consumer validates that native declaration, the annotated handler contract, and duplicate mappings before dispatching exact property Attribute names. One handler class may declare multiple `PropertyHandler` mappings.

These Attributes establish the declaration shape, intrinsic value validity, and shared meaning. They do not prescribe container retention, factory dependency construction, scanning strategy, or resolver implementation; an integrating DI implementation validates and executes them. The official `super-kernel/di` component executes `Definer`, `Provider`, `FactoryProvider`, `Autowired`, `Resolver`, and `PropertyHandler`. It consumes `Autowired` on properties, with lazy proxies by default, and treats `Autowired` on methods as metadata only; see the [DI README](https://github.com/super-kernel/di/blob/main/README.md). Support in other DI implementations is implementation-defined.

## Event dispatcher Attributes

`SuperKernel\Attribute\Listener` marks a `SuperKernel\Contract\ListenerInterface` implementation for PSR-14 listener discovery. It carries an integer `priority` that defaults to `0`; higher priorities run before lower priorities, while equal priorities preserve discovery order.

```php
use SuperKernel\Attribute\Listener;
use SuperKernel\Contract\ListenerInterface;

#[Listener(priority: 100)]
final class RecordAuditEntry implements ListenerInterface
{
    public function listen(): array
    {
        return [
            UserRegistered::class,
        ];
    }

    public function process(object $event): void
    {
        // React synchronously to the event.
    }
}
```

The Attribute only marks the class and declares ordering. A consuming PSR-14 listener provider, such as `super-kernel/event-dispatcher`, validates the listener, matches event types, constructs listeners, and dispatches.

## Dependency boundary

Components that use these Attributes in production source should declare `super-kernel/attribute` as a production dependency. Depending on `super-kernel/di` transitively is not sufficient and would couple the component to one DI implementation. Test-only use may remain a development dependency.

This package covers PHP's native `#[Attribute]` declarations only. It does not define Doctrine annotations, DocBlock metadata, collected Attribute records, scanners, caches, or component-private Attributes.
