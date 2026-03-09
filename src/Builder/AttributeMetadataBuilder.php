<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Builder;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;
use RuntimeException;
use SuperKernel\Attribute\AttributeMetadata;
use SuperKernel\Attribute\Contract\AttributeInterface;
use SuperKernel\Attribute\Metadata\ClassAttribute;
use SuperKernel\Attribute\Metadata\MethodAttribute;
use SuperKernel\Attribute\Metadata\PropertyAttribute;
use SuperKernel\ComposerResolver\Contract\PackageMetadataInterface;
use SuperKernel\ComposerResolver\Provider\PackageRegistryProvider;
use SuperKernel\Contract\ReflectorInterface;
use SuperKernel\Reflection\Provider\ReflectorProvider;
use Throwable;
use function is_null;
use function method_exists;
use function printf;
use const PHP_EOL;

final class AttributeMetadataBuilder
{
	private static ReflectorInterface $reflector;

	private array $attributes = [];

	private function __construct(private readonly PackageMetadataInterface $packageMetadata)
	{
	}

	public static function make(PackageMetadataInterface $packageMetadata): AttributeMetadata
	{
		if (!isset(self::$reflector)) {
			self::$reflector = ReflectorProvider::make();
		}

		$instance = new self($packageMetadata);
		foreach ($packageMetadata->getClassmap() as $class => $path) {
			try {
				$reflectClass = self::$reflector->reflectClass($class);

				$instance->addAttribute($class, $reflectClass);
				$instance->addAttribute($class, $reflectClass->getMethods());
				$instance->addAttribute($class, $reflectClass->getProperties());
			}
			catch (Throwable $throwable) {
				if (!is_null($packageMetadata->getReference())) {
					continue;
				}

				printf("\033[33m[WARNING]\033[0m %s in %s" . PHP_EOL,
				       $throwable->getMessage(),
				       PackageRegistryProvider::make()
					       ->getPackage($packageMetadata->getName())
					       ->getPathResolver()
					       ->to($path)
					       ->get(),
				);
			}
		}

		return $instance->create();
	}

	public function addAttribute(string $class, array|Reflector $reflector): AttributeMetadataBuilder
	{
		$reflectors = $reflector instanceof Reflector ? [$reflector] : $reflector;
		foreach ($reflectors as $reflector) {
			if (!method_exists($reflector, 'getAttributes')) {
				continue;
			}
			foreach ($reflector->getAttributes() as $attribute) {
				$this->attributes[] = $this->getAttribute($class, $reflector, $attribute);
			}
		}

		return $this;
	}

	public function getAttribute(string              $class, Reflector $reflector,
	                             ReflectionAttribute $reflectionAttribute): AttributeInterface
	{
		$attribute = $reflectionAttribute->getName();
		$arguments = $reflectionAttribute->getArguments();

		return match (true) {
			$reflector instanceof ReflectionClass    => new ClassAttribute($attribute, $class, $arguments),
			$reflector instanceof ReflectionMethod   => new MethodAttribute($attribute, $class, $reflector->getName(), $arguments),
			$reflector instanceof ReflectionProperty => new PropertyAttribute($attribute, $class, $reflector->getName(), $arguments),
			default                                  => throw new RuntimeException("Unsupported target type: {$reflector->getName()}"),
		};
	}

	public function create(): AttributeMetadata
	{
		return new AttributeMetadata(
			   $this->packageMetadata->getName(),
			   $this->packageMetadata->getReference(),
			...$this->attributes,
		);
	}
}