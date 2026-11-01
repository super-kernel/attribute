<p align="right">
  <a href="README.md">English</a> | <strong>中文文档</strong>
</p>

# SuperKernel Attribute

`super-kernel/attribute` 定义由 SuperKernel 组件、第三方组件和可互换的框架实现共享的原生 PHP 注解（Attribute）契约。它只包含声明：收集、缓存、解释和执行都由使用各个注解的组件负责。所有注解都位于 `SuperKernel\Attribute` 命名空间。

本包不依赖 `super-kernel/di`，因此其他 DI 实现可以使用同样的契约，而不必依赖官方容器。

## 配置注解

`SuperKernel\Attribute\Configuration` 是一个不可重复的类或方法标记。标注在类上时，它声明一个类型化的配置源。可选的 `?string $className = null` 显式指定配置条目接口；省略时，由 Config 组件为单一来源推断唯一一个最具体的已实现接口。

```php
use SuperKernel\Attribute\Configuration;

#[Configuration(ServerConfigInterface::class)]
final readonly class ApplicationServerConfig implements ServerConfigInterface
{
    // Typed configuration methods.
}
```

传入 `className` 时，它必须是不含 NUL 字节的非空字符串。注解本身不校验接口、不收集声明、不解析源对象，也不选择 DI 绑定；接口是否存在、实现是否兼容、省略接口时的推断、收集和源解析都由 `super-kernel/config` 处理。

标注在方法上时，`Configuration` 标记一个具名配置条目。第二个可选参数 `bool $default = false` 在具名条目中指定显式默认值；请按名称传入。只有一个条目时会自动选中它；有多个条目且没有显式默认值时，具名查询可以使用，只有不带名称的查询会失败。条目组由 Contract 的 `MultipleConfigInterface` 标记，并在类级 `Configuration` 上显式声明其条目接口。方法级的 `className` 可以省略，否则必须与该接口完全一致。方法注解不能脱离其所在的组类单独声明来源。

```php
#[Configuration(default: true)]
public function primary(): ServerConfigInterface
{
    return new ApplicationServerConfig();
}
```

## DI 注解

`Definer`、`Resolver` 和 `Provider` 是不可重复的类级声明。`FactoryProvider` 和 `PropertyHandler` 是可重复的类级声明。`Provision` 是不可重复的方法声明，`Autowired` 是不可重复的属性或方法声明：

- `Definer` 标记一个定义提供者，带有默认为 `0` 的整数 `priority`。
- `Resolver` 标记一个定义解析器，带有默认为 `0` 的整数 `priority`。
- `Provider` 把所标注的实现类映射到一个必需的 `className` 标识。
- 每个 `FactoryProvider` 声明把所标注的可调用提供者类映射到一个必需的 `className` 标识。
- `Provision` 标记一个提供实例的工厂方法，并保留零个或多个服务名称。
- 每个 `PropertyHandler` 声明把所标注的处理器类映射到一个原生属性注解类。
- `Autowired` 只接受 `?string $identifier = null`。传入的标识必须是已存在的类或接口。对属性而言，`null` 表示按类型推断；没有延迟加载开关。

`Provider` 和 `FactoryProvider` 必需的 `className` 值，以及 `PropertyHandler` 必需的注解类名，在构造声明时就会拒绝空字符串和 NUL 字节。使用方只需校验映射兼容性、原生注解目标和重复归属。

整数优先级高的先执行；优先级相同时保持发现顺序。`Provider` 声明从所标注的类或接口到映射标识的兼容映射；可实例化的类也可以用 `#[Provider(OwnClass::class)]` 把自身注册为普通的 DI 构造。`FactoryProvider` 声明所标注的类是一个特殊的提供者，由其公开、非静态的 `__invoke()` 方法提供映射的条目。工厂提供者类可以省略构造函数或提供一个公开的构造函数，构造函数和方法参数的解析由使用方实现负责。

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

重复的 `FactoryProvider` 声明各自保留 `className` 值和声明顺序。使用方可以把每个不同的标识映射到同一个可调用提供者类；重复声明一个已映射的标识属于重复映射错误。

`SuperKernel\Attribute\Provision` 接受 `string ...$name`，并把这些值存放在公开的只读列表 `name` 中。不传参数得到空列表；每个传入的名称都必须非空且不含 NUL 字节。位置参数和命名参数都保留值的顺序和重复项，参数键会被丢弃。显式的 `null` 无效。工厂参数属于方法签名。`super-kernel/config` 通过 DI 执行 `Provision` 方法。

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

`PropertyHandler` 不能替代 PHP 原生的注解声明。被映射的注解类要自己声明 `#[Attribute(Attribute::TARGET_PROPERTY)]` 目标。使用方在分发确切的属性注解名称之前，先校验该原生声明、所标注处理器的契约以及重复映射。一个处理器类可以声明多个 `PropertyHandler` 映射。

这些注解确立了声明的形状、值本身的有效性以及共同的含义。它们不规定容器的保留方式、工厂依赖的构造、扫描策略或解析器实现；由集成的 DI 实现负责校验并执行。官方的 `super-kernel/di` 组件执行 `Definer`、`Provider`、`FactoryProvider`、`Autowired`、`Resolver` 和 `PropertyHandler`。它处理属性上的 `Autowired`，默认使用惰性代理，并把方法上的 `Autowired` 仅视为元数据；参见 [DI README](https://github.com/super-kernel/di/blob/main/README.md)。其他 DI 实现的支持程度由各实现自行定义。

## 事件分发器注解

`SuperKernel\Attribute\Listener` 标记一个 `SuperKernel\Contract\ListenerInterface` 实现，供 PSR-14 监听器发现使用。它带有默认为 `0` 的整数 `priority`；优先级高的先执行，优先级相同时保持发现顺序。

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

这个注解只标记类并声明顺序。使用它的 PSR-14 监听器提供者（例如 `super-kernel/event-dispatcher`）负责校验监听器、匹配事件类型、构造监听器以及分发。

## 依赖边界

在生产代码中使用这些注解的组件，应把 `super-kernel/attribute` 声明为生产依赖。通过 `super-kernel/di` 间接依赖它是不够的，而且会把组件绑定到某一个 DI 实现上。仅在测试中使用时，可以保留为开发依赖。

本包只涵盖 PHP 原生的 `#[Attribute]` 声明。它不定义 Doctrine 注解、DocBlock 元数据、收集后的注解记录、扫描器、缓存或组件私有的注解。
