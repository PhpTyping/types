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
use Typing\Type\StringObject;

/**
 * Trait StringCastable.
 */
trait StringCastableTrait
{
    /**
     * @param Primitive $primitive
     *
     * @return mixed
     */
    abstract protected function getScalar(Primitive $primitive): mixed;

    /**
     * @return StringObject
     */
    public function toStringObject(): StringObject
    {
        return StringObject::fromPrimitive($this);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return strval($this->getScalar(Primitive::STRING()));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
