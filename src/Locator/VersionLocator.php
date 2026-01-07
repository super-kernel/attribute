<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Locator;

use RuntimeException;
use function explode;
use function file_exists;
use function file_get_contents;
use function is_readable;
use function json_decode;
use function json_validate;
use function str_contains;

final class VersionLocator
{
	private array $data;

	public function __construct(string $path)
	{
		$filepath = $path . DIRECTORY_SEPARATOR . 'composer.json';

		if (!file_exists($filepath)) throw new RuntimeException('Composer.json file not found');
		if (!is_readable($filepath)) throw new RuntimeException('Composer.json file is not readable');

		$data = file_get_contents($filepath);

		if (!json_validate($data)) {
			throw new RuntimeException('Composer.json file is not valid');
		}

		$this->data = json_decode($data, true);
	}

	public function get(string $name): mixed
	{
		$key = str_contains($name, '.') ? explode('.', $name) : $name;

		return $this->data[$key] ?? null;
	}
}