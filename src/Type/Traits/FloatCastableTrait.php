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
use Typing\Type\FloatObject;

/**
 * Trait FloatCastable.
 */
trait FloatCastableTrait
{
    /**
     * @param Primitive $primitive
     *
     * @return mixed
     */
    abstract protected function getScalar(Primitive $primitive): mixed;

    /**
     * @return FloatObject
     */
    public function toFloatObject(): FloatObject
    {
        return FloatObject::fromPrimitive($this->toFloat());
    }

    /**
     * @return float
     */
    public function toFloat(): float
    {
        return (float) $this->getScalar(Primitive::FLOAT());
    }
}
