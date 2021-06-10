<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Type;

use Doctrine\Common\Collections\Collection;

/**
 * Interface TypedCollectionInterface.
 *
 * @template-extends Collection<int|string, mixed>
 */
interface TypedCollectionInterface extends Collection
{
    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @return bool
     */
    public function isTyped(): bool;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isOfType(string $type): bool;
}
