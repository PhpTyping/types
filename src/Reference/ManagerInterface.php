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

/**
 * Interface ManagerInterface.
 */
interface ManagerInterface
{
    /**
     * Gets a new address for a pointer, and assigns pointer to it.
     *
     * @param mixed $pointer
     *
     * @return string the address
     */
    public function getNewAddress(mixed &$pointer): string;

    /**
     * Returns a pointer by reference.
     *
     * @param string $id
     *
     * @return mixed pointer via reference
     */
    public function &getPointer(string $id): mixed;

    /**
     * Removes a pointer from current collection if exists.
     *
     * @param string $address
     *
     * @return bool
     */
    public function shouldFree(string $address): bool;
}
