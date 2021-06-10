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
 * Interface TypeInterface.
 *
 * Warning: Using (un-)serialize() on a TypeInterface instance is not a supported use-case
 * and may break when we change the internals in the future. If you need to
 * serialize a TypeInterface use __invoke and reconstruct the TypeInterface
 * manually.
 */
interface PrimitiveLoaderInterface
{
    /**
     * Returns an instance of TypeInterface from a primitive type.
     *
     * @param mixed $mixed value to transform to TypeInterface instance
     *
     * @return PrimitiveLoaderInterface
     */
    public static function fromPrimitive(mixed $mixed): PrimitiveLoaderInterface;
}
