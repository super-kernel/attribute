<?php
declare(strict_types=1);

namespace SuperKernel\Attribute;

use RuntimeException;
use SuperKernel\Attribute\Locator\VersionLocator;
use function file_get_contents;
use function getcwd;

final class LocatorFactory
{
	private string $rootDir;

	public static function getInstance(): self
	{
		return self::$instance ??= new self()();
	}

	public function getVersionLocator()
	{
		return new VersionLocator($this->rootDir);
	}

	public function getVersionLockLocator()
	{
	}

	public function __invoke(): self
	{
	}

	private static ?self $instance = null;

	private function __construct()
	{
		$rootDir = getcwd();

		if (!$rootDir) {
			throw new RuntimeException('Failed to get the current working directory.');
		}

		$this->rootDir = $rootDir;
	}

	private function __clone()
	{
	}
}