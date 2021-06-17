<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Typing\Reference;

use OutOfBoundsException;
use Typing\Type\BoxableInterface;

/**
 * Class Manager.
 */
final class DefaultManager implements ManagerInterface
{
    /**
     * @var self|null
     */
    private static self | null $instance = null;

    /**
     * @var int|string
     */
    private int | string $lastAddress = 0;

    /**
     * @var BoxableInterface[]
     */
    private array $collection = [];

    /**
     * Private constructor.
     */
    private function __construct()
    {
        // Singleton class needs no constructor...
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
            register_shutdown_function([self::$instance, 'removeReferences']);
        }

        return self::$instance;
    }

    /**
     * Sets an address to a specific key.
     *
     * @param string|int $key
     * @param mixed      $pointer
     */
    public function setAddress(string | int $key, mixed &$pointer): void
    {
        $this->collection[$key] = &$pointer;
        if ((is_numeric($key) && $key > $this->lastAddress) || (!is_numeric($key))) {
            $this->lastAddress = $key;
        }
    }

    /**
     * Gets a new address for a pointer, and assigns pointer to it.
     *
     * @param mixed $pointer
     *
     * @return string the address
     */
    public function getNewAddress(mixed &$pointer): string
    {
        $address = $this->createAddress();
        $this->setAddress($address, $pointer);

        return $address;
    }

    /**
     * Returns a pointer by reference.
     *
     * @param string $id
     *
     * @return mixed pointer via reference
     */
    public function &getPointer(string $id): mixed
    {
        if (!array_key_exists($id, $this->collection)) {
            throw new OutOfBoundsException("Address '{$id}' does not exist.");
        }

        return $this->collection[$id];
    }

    /**
     * Removes a pointer from current collection if exists.
     *
     * @param string $address
     *
     * @return bool
     */
    public function shouldFree(string $address): bool
    {
        if (!array_key_exists($address, $this->collection)) {
            return false;
        }

        unset($this->collection[$address]);

        return true;
    }

    /**
     * Called when php shuts down.
     *
     * Cleans left over pointers not explicitly destroyed. Keeps memory leaks at bay.
     */
    public function removeReferences(): void
    {
        foreach ($this->collection as $address => &$pointer) {
            $pointer = null;
            unset($this->collection[$address]);
        }

        gc_collect_cycles();
    }

    /**
     * Creates a new address for the a pointer.
     *
     * @return string
     */
    private function createAddress(): string
    {
        if (PHP_INT_MAX === $this->lastAddress || !is_numeric($this->lastAddress)) {
            do {
                $address = hash('sha1', uniqid(strval(mt_rand()), true));
            } while (isset($this->collection[$address]) || array_key_exists($address, $this->collection));

            return strval($address);
        }

        $address = ++$this->lastAddress;

        return strval($address);
    }
}
