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

namespace Typing\Type;

use Typing\Reference\ManagerInterface;

/**
 * Interface BoxableInterface.
 */
interface BoxableInterface
{
    /**
     * @param mixed                 $pointer
     * @param ManagerInterface|null $manager
     */
    public static function box(mixed &$pointer, ?ManagerInterface $manager = null): void;
}
