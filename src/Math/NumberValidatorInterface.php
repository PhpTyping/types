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

namespace Typing\Math;

/**
 * Interface NumberValidatorInterface.
 */
interface NumberValidatorInterface
{
    /**
     * Ensures scalar variable is a number.
     *
     * @param string|int|float $number
     *
     * @return bool
     */
    public function isValid(string | int | float $number): bool;
}
