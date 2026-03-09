<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Provider;

use Phar;
use RuntimeException;
use SuperKernel\Annotation\Autowired;
use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\Attribute\AttributeCollector;
use SuperKernel\Attribute\AttributeMetadata;
use SuperKernel\Attribute\Builder\AttributeMetadataBuilder;
use SuperKernel\Attribute\Contract\AttributeCollectorInterface;
use SuperKernel\Attribute\Metadata\ClassAttribute;
use SuperKernel\Attribute\Metadata\MethodAttribute;
use SuperKernel\Attribute\Metadata\PropertyAttribute;
use SuperKernel\ComposerResolver\Contract\PackageMetadataInterface;
use SuperKernel\ComposerResolver\Factory\ScannerFactory;
use SuperKernel\ComposerResolver\Provider\PackageMetadataRegistryProvider;
use SuperKernel\PathResolver\Contract\PathResolverInterface;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

#[
	Provider(AttributeCollectorInterface::class),
	Factory,
]
final class AttributeCollectorProvider
{
	#[Autowired]
	private static AttributeCollectorInterface $attributeCollector;

	private static PathResolverInterface $pathResolver;

	private static function getPathResolver(): PathResolverInterface
	{
		if (!isset(self::$pathResolver)) {
			self::$pathResolver = PathResolverProvider::make('vendor')
				->to('.super-kernel')
				->to('attribute');

			$dir = self::$pathResolver->get();
			if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
				throw new RuntimeException("Could not create cache dir: $dir");
			}
		}
		return self::$pathResolver;
	}

	private static function make(PackageMetadataInterface $package): ?AttributeMetadata
	{
		$fileName = str_replace(['/', '\\'], '_', $package->getName());
		$filePath = self::getPathResolver()->to($fileName)->get();

		$isPhar = strlen(Phar::running(false)) > 0;
		if ($isPhar) {
			return self::loadCache($filePath);
		}

		if (is_null($package->getReference())) {
			return self::scan($package, $filePath);
		}

		$cachePackage = self::loadCache($filePath);
		if ($cachePackage?->getReference() !== $package->getReference()) {
			return self::scan($package, $filePath);
		}

		return $cachePackage;
	}

	private static function loadCache(string $filePath): ?AttributeMetadata
	{
		if (!is_file($filePath)) return null;
		$content = file_get_contents($filePath);
		if (!$content) return null;

		$data = unserialize($content, [
			'allowed_classes' => [
				ClassAttribute::class,
				MethodAttribute::class,
				PropertyAttribute::class,
				AttributeMetadata::class,
			],
		]);
		return $data instanceof AttributeMetadata ? $data : null;
	}

	private static function scan(PackageMetadataInterface $package, string $filePath): ?AttributeMetadata
	{
		ScannerFactory::make()->execute(function () use ($package, $filePath) {
			$metadata = AttributeMetadataBuilder::make($package);
			file_put_contents($filePath, serialize($metadata), LOCK_EX);
		});

		return self::loadCache($filePath) ?? throw new RuntimeException("Scan failed for {$package->getName()}");
	}

	public function __invoke(): AttributeCollectorInterface
	{
		if (!isset(self::$attributeCollector)) {
			$packageMetadataRegistry = new PackageMetadataRegistryProvider()();
			$attributesMetadata = [];
			foreach ($packageMetadataRegistry->getPackages() as $package) {
				$attributesMetadata[] = self::make($package);
			}
			self::$attributeCollector = new AttributeCollector(...$attributesMetadata);
		}

		return self::$attributeCollector;
	}
}