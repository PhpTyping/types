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

namespace Typing\Type\Traits;

use LogicException;
use TypeError;
use Typing\Reference\DefaultManager;
use Typing\Reference\ManagerInterface;

/**
 * Trait Boxable.
 */
trait BoxableTrait
{
    /**
     * Address to pointer in Manager::collection.
     *
     * @var string|null
     */
    private ?string $referenceAddress = null;

    /**
     * @var ManagerInterface
     */
    private static ManagerInterface $manager;

    /**
     * @param mixed|null $value
     *
     * @return static
     */
    abstract protected static function createStatic(mixed $value = null): static;

    /**
     * Boxes a variable to a specific type, including future reassignment as a primitive.
     *
     * @param mixed                 $pointer variable to box (the pointer), by reference
     * @param ManagerInterface|null $manager the reference manager
     *
     * @throws LogicException when the pointer has previously been declared
     * @throws LogicException when the pointer has previously been declared
     * @throws TypeError      when an invalid argument is passed as value or assigned to pointer
     */
    final public static function box(mixed &$pointer, ?ManagerInterface $manager = null): void
    {
        static::$manager = $manager ?? DefaultManager::getInstance();
        // We need to clone the previously referenced variable because it will have a null address.
        // Otherwise we convert it to the current type if it was a primitive.
        $pointer = ($pointer instanceof self) ? clone $pointer : static::createStatic($pointer);
        // We are now actively managing this address.
        $pointer->referenceAddress = static::$manager->getNewAddress($pointer);
    }

    /**
     * Runs when a variable is reassigned or destroyed with $pointer = null;.
     * Basically overloads the assignment operator when a specific pointer has been boxed to return a new instance
     * of the previous type with the new assigned value.
     */
    final public function __destruct()
    {
        if (null === $this->referenceAddress) {
            return;
        }

        $pointer = &static::$manager->getPointer($this->referenceAddress);

        if ($pointer !== $this && null !== $pointer) {
            try {
                // We assign the value of the pointer to value.
                $value = $pointer;
                // Clear pointer before attempting to box new value.
                $pointer = null;
                $pointer = $value instanceof static? $value : static::createStatic($value);
                // Box the new pointer.
                static::box(pointer: $pointer, manager: static::$manager);
            } catch (TypeError $e) {
                // Reset the pointer to the previous value and re throw exception.
                // This will allow the variable to remain boxed, the exception to be caught, and handled appropriately.
                $pointer = clone $this;

                throw $e;
            }
        }

        // Runs at end of scope, upon assigning new value, and when variable is nullified explicitly.
        // Clears the old pointer.
        static::$manager->shouldFree($this->referenceAddress);
    }
}
