<p align="right">
  <a href="README.md">English</a> | <strong>中文文档</strong>
</p>

# SuperKernel Attribute

`super-kernel/attribute` 声明 SuperKernel 各个包共享的原生 PHP 注解：服务、注入、容器扩展、配置和事件监听器。由使用某个注解的包赋予它含义。

## 安装

```bash
composer require super-kernel/attribute
```

本包除 PHP 外没有其他要求。只有安装了使用某个注解的包（例如 `super-kernel/di`、`super-kernel/config` 或 `super-kernel/event-dispatcher`），并且 `skernel build` 收集了该注解之后，它才会生效。

## 提供的能力

- 用 `#[Provider]` 和 `#[FactoryProvider]` 声明容器服务。
- 用 `#[Autowired]` 声明属性注入。
- 用 `#[Definer]`、`#[Resolver]` 和 `#[PropertyHandler]` 扩展容器。
- 用 `#[Configuration]` 和 `#[Provision]` 声明配置和工厂方法。
- 用 `#[Listener]` 以某个优先级注册事件监听器。
- 每个注解在构造时检查自身的值。

它不会：

- 收集、存储或执行这些注解，这由使用它们的包和 `skernel build` 负责；
- 依赖 `super-kernel/di` 或其他任何实现，因此另一个容器也可以解释同样的声明；
- 支持 DocBlock 注释注解，或者声明只属于某一个包的注解，例如异常处理器。

## 快速开始

声明一个接口以及提供它的类。将接口保存为 `src/Greeting/Greeter.php`：

```php
<?php

declare(strict_types=1);

namespace Src\Greeting;

interface Greeter
{
    public function greet(string $name): string;
}
```

将类保存为 `src/Greeting/EnglishGreeter.php`：

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

把该服务注入到一个属性中。将类保存为 `src/Greeting/Welcome.php`：

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

将脚本保存为 `bin/welcome.php`：

```php
<?php

declare(strict_types=1);

use Src\Greeting\Welcome;
use SuperKernel\Context\ApplicationContext;

require dirname(__DIR__) . '/vendor/autoload.php';

echo ApplicationContext::getContainer()->get(Welcome::class)->message(), PHP_EOL;
```

构建项目并运行脚本：

```bash
skernel build
php bin/welcome.php
```

它会打印 `Hello, SuperKernel!`。

## 公开接口

| 名称 | 类别 | 用途 |
| --- | --- | --- |
| `SuperKernel\Attribute\Provider` | 注解 | 把一个标识符映射到被注解的类。 |
| `SuperKernel\Attribute\FactoryProvider` | 注解 | 可重复；通过调用被注解类的 `__invoke()` 创建一个标识符。 |
| `SuperKernel\Attribute\Autowired` | 注解 | 注入一个属性，可以指定来源的类或接口。 |
| `SuperKernel\Attribute\Definer` | 注解 | 以一个整数优先级注册容器的定义器（definer）。 |
| `SuperKernel\Attribute\Resolver` | 注解 | 以一个整数优先级注册容器的解析器（resolver）。 |
| `SuperKernel\Attribute\PropertyHandler` | 注解 | 可重复；注册自定义属性注解的处理器。 |
| `SuperKernel\Attribute\Configuration` | 注解 | 在类上声明配置来源，或在方法上声明一个具名配置项。 |
| `SuperKernel\Attribute\Provision` | 注解 | 声明一个创建容器服务的方法，服务以其返回类型或给定的名称注册。 |
| `SuperKernel\Attribute\Listener` | 注解 | 以一个整数优先级注册事件监听器。 |

## 需要了解的行为

- 注解在 `skernel build` 构造它时检查自身的值，对无效的值抛出 `InvalidArgumentException`，例如 `Provider className must be non-empty and contain no NUL bytes.`。随后 `skernel build` 会跳过整个类并打印它，该类的所有注解都不会进入应用。
- `#[Autowired('Some\Class')]` 要求一个已存在的类或接口：`Injectable identifier <identifier> does not exist.`
- 对于 `Definer`、`Resolver` 和 `Listener`，优先级越高越先运行，优先级相同的保持发现顺序。
- 添加、删除或修改注解之后，请重新运行 `skernel build`，或者用会先构建的 `skernel run` 启动。
- 在自己的代码中使用这些注解的包应当直接依赖 `super-kernel/attribute`，而不是依靠 `super-kernel/di` 来安装它。

## 文档

开发者文档用完整示例介绍每一个注解、它的参数和检查，以及解释它的包：[SuperKernel Attribute](https://github.com/super-kernel/.github/blob/main/docs/components/attribute.zh-CN.md)。

## 许可证

基于 MIT 许可证发布，详见 [LICENSE](LICENSE)。
