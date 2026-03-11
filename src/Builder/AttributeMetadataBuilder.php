<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Builder;

use Reflector;
use SuperKernel\Attribute\Attribute;
use SuperKernel\Attribute\AttributeMetadata;
use SuperKernel\ComposerResolver\Contract\PackageMetadataInterface;
use SuperKernel\ComposerResolver\Provider\PackageRegistryProvider;
use SuperKernel\Reflector\ReflectionManager;
use Throwable;
use function is_null;
use function method_exists;
use function printf;
use const PHP_EOL;

final class AttributeMetadataBuilder
{
	private array $attributes = [];

	private function __construct(private readonly PackageMetadataInterface $packageMetadata)
	{
	}

	public static function make(PackageMetadataInterface $packageMetadata): AttributeMetadata
	{
		$instance = new self($packageMetadata);
		foreach ($packageMetadata->getClassmap() as $class => $path) {
			try {
				$reflectClass = ReflectionManager::reflectClass($class);

				$instance->addAttribute($reflectClass);
				$instance->addAttribute($reflectClass->getMethods());
				$instance->addAttribute($reflectClass->getProperties());
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

	public function addAttribute(array|Reflector $reflector): AttributeMetadataBuilder
	{
		$reflectors = $reflector instanceof Reflector ? [$reflector] : $reflector;
		foreach ($reflectors as $reflector) {
			if (!method_exists($reflector, 'getAttributes')) {
				continue;
			}
			foreach ($reflector->getAttributes() as $attribute) {
				$this->attributes[] = new Attribute($reflector, $attribute);
			}
		}

		return $this;
	}

	private function create(): AttributeMetadata
	{
		return new AttributeMetadata(
			   $this->packageMetadata->getName(),
			   $this->packageMetadata->getReference(),
			...$this->attributes,
		);
	}
}