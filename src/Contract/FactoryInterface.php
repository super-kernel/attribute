<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Contract;

interface FactoryInterface
{
	public function getObject(): mixed;

	/**
	 * @return class-string|null
	 */
	public function getObjectType(): null|string;

	public function isSingleton(): bool;
}