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
use Typing\Type\Collection;

/**
 * Trait CollectionCastable.
 */
trait CollectionCastableTrait
{
    /**
     * @param Primitive $primitive
     *
     * @return mixed
     */
    abstract protected function getScalar(Primitive $primitive): mixed;

    /**
     * @return Collection<int|string, mixed>
     */
    public function toCollection(): Collection
    {
        return Collection::fromPrimitive($this->toArray());
    }

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return (array) $this->getScalar(Primitive::ARRAY());
    }
}
