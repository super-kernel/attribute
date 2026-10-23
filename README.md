# SuperKernel Attribute

`super-kernel/attribute` defines native PHP Attribute contracts shared by SuperKernel components, third-party
components, and interchangeable framework implementations. It contains declarations only: collection, caching,
interpretation, and execution remain owned by the components that consume each Attribute.

All public declarations live directly in `src/` and use the flat `SuperKernel\Attribute` namespace. The functional
sections below describe how the framework consumes them; they do not introduce nested declaration namespaces.

The package has no dependency on the official SuperKernel DI implementation. A component may require this package in
production while using `super-kernel/di` only as a development-time integration target, and another DI implementation
may consume the same contracts without depending on the official container.

## Configuration Attribute

`SuperKernel\Attribute\Configuration` is a non-repeatable class or method marker. On a class it declares a typed
configuration source. Its optional `?string $className = null` identifies the configuration item interface explicitly;
omitting it asks the Config component to infer one most-specific implemented interface for a single source.

```php
use SuperKernel\Attribute\Configuration;

#[Configuration(ServerConfigInterface::class)]
final readonly class ApplicationServerConfig implements ServerConfigInterface
{
    // Typed configuration methods.
}
```

The Attribute does not validate interfaces, collect declarations, resolve source objects, or choose a DI binding.
When supplied, `className` must be a non-empty string without NUL bytes. `super-kernel/config` owns existence,
implementation compatibility, omitted-interface inference, collection, and source resolution.

On a method, `Configuration` marks a named configuration item. Its second optional parameter, `bool $default = false`,
identifies an explicit default among named configuration items. Config owns method-name lookup, signature validation,
lazy invocation, and retained item identity. It selects a sole item automatically; with several items and no explicit
default, named lookup works and only unnamed lookup fails. A group is marked by Contract's `MultipleConfigInterface`
and declares its item interface explicitly on the class-level `Configuration`. Method-level `className` can be omitted
or must match that exact interface. A method Attribute does not declare a source independently of its group class.

```php
#[Configuration(default: true)]
public function primary(): ServerConfigInterface
{
    return new ApplicationServerConfig();
}
```

Method items use `Configuration`, with `default` passed by name. Group declarations identify the configuration contract.

## DI Attributes

The DI contracts `Definer`, `Resolver`, and `Provider` are non-repeatable class-level declarations. `FactoryProvider`
and `PropertyHandler` are repeatable class-level declarations. `Autowired` is a non-repeatable property or method
declaration for autowiring:

- `Definer` marks a definition provider and carries an integer `priority` that defaults to `0`.
- `Resolver` marks a definition resolver and carries an integer `priority` that defaults to `0`.
- `Provider` maps the annotated implementation class to one required `className` identifier.
- Each `FactoryProvider` declaration maps the annotated callable provider class to one required `className` identifier.
- `Provision` marks a factory method that supplies an instance and retains zero or more service names.
- Each `PropertyHandler` declaration maps the annotated handler class to one native property Attribute class.
- `Autowired` accepts only `?string $identifier = null`. A supplied identifier must name an existing
  class or interface. For properties, `null` requests type inference; there is no lazy-loading switch.
  Method execution remains the responsibility of a consuming implementation.

The required `className` values on `Provider` and `FactoryProvider`, and the required Attribute class name on
`PropertyHandler`, reject empty strings and NUL bytes at declaration construction. Consumers can therefore trust those
basic value invariants and validate only mapping compatibility, native Attribute targets, and duplicate ownership.

Higher integer priorities run before lower priorities; equal priorities preserve discovery order. A `Provider` declares
a compatible mapping from its annotated class or interface to the mapped identifier. The official DI component follows
interface chains to an instantiable class; an instantiable class may also register itself with
`#[Provider(OwnClass::class)]` for normal DI construction. A `FactoryProvider` declares that its annotated class is a
special provider whose public, non-static `__invoke()` method supplies the
mapped entry. The factory-provider class may omit its constructor or expose a public one, while the consuming
implementation owns constructor and method-parameter resolution. The callable's internal production strategy and
concrete return construction remain developer-defined. Factory-provider classes use a `Provider` suffix under their
owning package's `Provider` namespace.

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

Repeated `FactoryProvider` declarations retain their own scalar `className` values and declaration order. Consumers
may map each distinct identifier to the same callable provider class; repeated declarations of an already mapped
identifier remain a consumer-level duplicate-mapping error.

`SuperKernel\Attribute\Provision` is a non-repeatable method Attribute. Its constructor accepts `string ...$name`
and stores the values in the public readonly `name` list. No arguments produce an empty list; each supplied name
must be non-empty and contain no NUL bytes. Positional and named arguments preserve value order and duplicates;
argument keys are discarded. Explicit `null` is invalid.
Factory arguments belong to the method signature. Consumers own name inference, mapping conflicts, factory-object
resolution, parameter injection, result validation, and invocation. The official DI component does not yet execute
`Provision` methods.

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

The Attribute contract establishes the declaration shape, intrinsic value validity, and shared meaning. It does not
prescribe container retention, factory dependency construction, scanning strategy, or resolver implementation. An
integrating DI implementation is responsible for validating and executing the corresponding relationships and
behavior.

`PropertyHandler` does not replace PHP's native Attribute declaration. A mapped Attribute class declares its own
`#[Attribute(Attribute::TARGET_PROPERTY)]` target. A consumer validates that native declaration, the annotated handler
contract, and duplicate mappings before dispatching exact property Attribute names. One handler class may declare
multiple `PropertyHandler` mappings.

Consumers define proxy support, initialization triggers, identity behavior, and unsupported target handling.
The official DI implementation uses native PHP 8.4 lazy proxies by default where the target supports them, without
changing the lifecycle of the referenced container entry.

The official `super-kernel/di` component currently executes `Definer`, `Provider`, `FactoryProvider`, `Autowired`,
`Resolver`, and `PropertyHandler` metadata. Runtime support in other DI implementations remains
implementation-defined; the existence of an Attribute declaration does not by itself prove that every consumer
executes its complete flow.

The official DI component consumes `Autowired` on properties, including explicit identifiers, type inference,
default values, and automatic lazy proxies. Targets without a suitable native proxy class use ordinary container
lookup. Method declarations remain metadata only; DI does not invoke them automatically.
DI uses `SuperKernel\Di\PropertyHandler\AutowiredPropertyHandler` for property injection.

## Event dispatcher Attributes

`SuperKernel\Attribute\Listener` marks an invokable class for PSR-14 listener discovery. It carries an
integer `priority` that defaults to `0`; higher priorities run before lower priorities, while equal priorities preserve
discovery order.

```php
use SuperKernel\Attribute\Listener;

#[Listener(priority: 100)]
final class RecordAuditEntry
{
    public function __invoke(UserRegistered $event): void
    {
        // React synchronously to the event.
    }
}
```

The Attribute only marks the class and declares ordering. A consuming PSR-14 listener provider owns callable
validation, event-type matching, listener construction, and dispatch behavior.

## Dependency boundary

Components that use these Attributes in production source should declare `super-kernel/attribute` as a production
dependency. Depending on `super-kernel/di` transitively is not sufficient and would couple the component to one DI
implementation. Test-only use may remain a development dependency. The manifest suggests `super-kernel/config` as
the standard `Configuration` consumer without requiring it.

This package covers PHP's native `#[Attribute]` declarations only. It does not define Doctrine annotations, DocBlock
metadata, collected Attribute records, scanners, caches, or component-private Attributes.

## Development

```bash
composer84 validate --strict
composer84 test
composer84 format:check
composer84 format
```
