<?php
declare(strict_types=1);

namespace SuperKernel\Attribute\Contract;

interface AttributeInterface
{
	public function getName(): string;

	public function getClass(): string;

	public function getInstance(): object;
}