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

/**
 * Interface CollectionCastableInterface.
 */
interface CollectionCastableInterface
{
    /**
     * @return Collection<int|string, mixed>
     */
    public function toCollection(): Collection;

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array;
}
