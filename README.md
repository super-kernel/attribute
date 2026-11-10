<p align="right">
  <strong>English</strong> | <a href="README.zh-CN.md">中文文档</a>
</p>

# SuperKernel Attribute

`super-kernel/attribute` declares the native PHP Attributes that SuperKernel packages share: services, injection, container extensions, configuration, and event listeners. The packages that consume an Attribute give it its meaning.

## Installation

```bash
composer require super-kernel/attribute
```

The package has no requirement beyond PHP. An Attribute takes effect only when a package that consumes it is installed, such as `super-kernel/di`, `super-kernel/config`, or `super-kernel/event-dispatcher`, and after `skernel build` has collected it.

## What it provides

- `#[Provider]` and `#[FactoryProvider]` to declare container services.
- `#[Autowired]` to declare property injection.
- `#[Definer]`, `#[Resolver]`, and `#[PropertyHandler]` to extend the container.
- `#[Configuration]` and `#[Provision]` to declare configuration and factory methods.
- `#[Listener]` to register an event listener with a priority.
- Checks of each Attribute's own values when it is constructed.

It does not:

- collect, store, or execute the Attributes; the consuming packages and `skernel build` do;
- depend on `super-kernel/di` or any other implementation, so another container can interpret the same declarations;
- support DocBlock annotations, or declare Attributes of a single package, such as exception handlers.

## Quick start

Declare an interface and the class that provides it. Save the interface as `src/Greeting/Greeter.php`:

```php
<?php

declare(strict_types=1);

namespace Src\Greeting;

interface Greeter
{
    public function greet(string $name): string;
}
```

Save the class as `src/Greeting/EnglishGreeter.php`:

```php
<?php

declare(strict_types=1);

namespace Src\Greeting;

use SuperKernel\Attribute\Provider;

#[Provider(Greeter::class)]
final class EnglishGreeter implements Greeter
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}
```

Inject the service into a property. Save the class as `src/Greeting/Welcome.php`:

```php
<?php

declare(strict_types=1);

namespace Src\Greeting;

use SuperKernel\Attribute\Autowired;

final class Welcome
{
    #[Autowired]
    private Greeter $greeter;

    public function message(): string
    {
        return $this->greeter->greet('SuperKernel');
    }
}
```

Save the script as `bin/welcome.php`:

```php
<?php

declare(strict_types=1);

use Src\Greeting\Welcome;
use SuperKernel\Context\ApplicationContext;

require dirname(__DIR__) . '/vendor/autoload.php';

echo ApplicationContext::getContainer()->get(Welcome::class)->message(), PHP_EOL;
```

Build the project and run the script:

```bash
skernel build
php bin/welcome.php
```

It prints `Hello, SuperKernel!`.

## Public surface

| Name | Kind | Purpose |
| --- | --- | --- |
| `SuperKernel\Attribute\Provider` | Attribute | Maps an identifier to the annotated class. |
| `SuperKernel\Attribute\FactoryProvider` | Attribute | Repeatable; creates an identifier by calling the annotated class's `__invoke()`. |
| `SuperKernel\Attribute\Autowired` | Attribute | Injects a property, optionally from a named class or interface. |
| `SuperKernel\Attribute\Definer` | Attribute | Registers a container definer with an integer priority. |
| `SuperKernel\Attribute\Resolver` | Attribute | Registers a container resolver with an integer priority. |
| `SuperKernel\Attribute\PropertyHandler` | Attribute | Repeatable; registers the handler of a custom property Attribute. |
| `SuperKernel\Attribute\Configuration` | Attribute | Declares a configuration source on a class, or a named item on a method. |
| `SuperKernel\Attribute\Provision` | Attribute | Declares a method that creates a container service, under its return type or the given names. |
| `SuperKernel\Attribute\Listener` | Attribute | Registers an event listener with an integer priority. |

## Behavior to know

- An Attribute checks its own values when `skernel build` constructs it and raises `InvalidArgumentException` for an invalid one, for example `Provider className must be non-empty and contain no NUL bytes.` `skernel build` then skips the whole class, prints it, and none of its Attributes reach the application.
- `#[Autowired('Some\Class')]` requires an existing class or interface: `Injectable identifier <identifier> does not exist.`
- For `Definer`, `Resolver`, and `Listener`, a higher priority runs first, and equal priorities keep the order of discovery.
- Run `skernel build` again after adding, removing, or changing an Attribute, or start with `skernel run`, which builds first.
- A package that uses these Attributes in its own code should require `super-kernel/attribute` directly, not rely on `super-kernel/di` to install it.

## Documentation

The developer documentation describes every Attribute, its parameters and checks, and the package that interprets it, with complete examples: [SuperKernel Attribute](https://github.com/super-kernel/.github/blob/main/docs/components/attribute.md).

## License

Released under the MIT License. See [LICENSE](LICENSE).
