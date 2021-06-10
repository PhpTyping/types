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

use Typing\Model\Primitive;
use Typing\Type\IntObject;

/**
 * Trait IntCastable.
 */
trait IntCastableTrait
{
    /**
     * @param Primitive $primitive
     *
     * @return mixed
     */
    abstract protected function getScalar(Primitive $primitive): mixed;

    /**
     * @return IntObject
     */
    public function toIntObject(): IntObject
    {
        return IntObject::fromPrimitive($this->toInt());
    }

    /**
     * Returns the integer representation of the current object.
     * String -> Count
     * Array -> Count
     * Bool -> 0 | 1
     * Etc.
     *
     * @return int
     */
    public function toInt(): int
    {
        return intval($this->getScalar(Primitive::INT()));
    }
}
