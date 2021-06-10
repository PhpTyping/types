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

use TypeError;
use Typing\Exception\InvalidTypeCastException;
use Typing\Math\MathAdapterInterface;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\IntCastableTrait;
use Typing\Type\Traits\StringCastableTrait;

/**
 * Class FloatObject.
 *
 * A FloatObject is a TypeInterface implementation that wraps around a regular PHP float / double.
 */
class FloatObject extends AbstractNumberObject
{
    use BoxableTrait;
    use IntCastableTrait;
    use StringCastableTrait;

    /**
     * @param float                     $float
     * @param int|null                  $precision
     * @param MathAdapterInterface|null $mathAdapter
     */
    final public function __construct(float $float, ?int $precision = null, MathAdapterInterface $mathAdapter = null)
    {
        /* @noinspection PhpNamedArgumentMightBeUnresolvedInspection */
        parent::__construct(num: $float, precision: $precision, mathAdapter: $mathAdapter);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function fromPrimitive(mixed $mixed, ?int $precision = null): static
    {
        if (!is_numeric($mixed)) {
            throw new TypeError('Incorrect type used. Use FloatType instead.');
        }

        return new static(floatval($mixed), $precision);
    }

    /**
     * {@inheritdoc}
     *
     * @return string|int|float
     */
    protected function getScalar(Primitive $primitive): string | int | float
    {
        return match ((string) $primitive) {
            (string) Primitive::INT() => (int) $this->getScalarValue(),
            (string) Primitive::STRING() => (string) $this->getScalarValue(),
            default => throw new InvalidTypeCastException($this, $primitive),
        };
    }

    /**
     * @param mixed|null $value
     *
     * @return static
     */
    protected static function createStatic(mixed $value = null): static
    {
        return static::fromPrimitive($value);
    }
}
