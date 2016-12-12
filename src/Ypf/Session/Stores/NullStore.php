<?php

namespace Ypf\Session\Stores;

class NullStore implements \Ypf\Session\Stores\StoreInterface {
	/**
	 * {@inheritdoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL)
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function read(string $sessionId): array
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $sessionId)
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function gc(int $dataTTL)
	{

	}
}
