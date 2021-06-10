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
use Typing\Type\BooleanObject;

/**
 * Trait BoolCastable.
 */
trait BoolCastableTrait
{
    /**
     * @param Primitive $primitive
     *
     * @return mixed
     */
    abstract protected function getScalar(Primitive $primitive): mixed;

    /**
     * @return BooleanObject
     */
    public function toBooleanObject(): BooleanObject
    {
        return BooleanObject::fromPrimitive($this->toBool());
    }

    /**
     * @return bool
     */
    public function toBool(): bool
    {
        return (bool) $this->getScalar(Primitive::BOOL());
    }
}
