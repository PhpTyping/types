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

use RuntimeException;
use Symfony\Polyfill\Ctype\Ctype;
use Typing\Exception\InvalidTransformationException;
use Typing\Exception\InvalidTypeCastException;
use Typing\Math\MathAdapterInterface;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoolCastableTrait;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\FloatCastableTrait;
use Typing\Type\Traits\StringCastableTrait;

/**
 * Class IntObject.
 *
 * A IntObject is a TypeInterface implementation that wraps around a regular PHP int.
 */
class IntObject extends AbstractNumberObject
{
    use BoxableTrait;
    use BoolCastableTrait;
    use FloatCastableTrait;
    use StringCastableTrait;

    /**
     * @param int                       $int
     * @param MathAdapterInterface|null $mathAdapter
     */
    final public function __construct(int $int, MathAdapterInterface $mathAdapter = null)
    {
        /* @noinspection PhpNamedArgumentMightBeUnresolvedInspection */
        parent::__construct(num: $int, precision: 0, mathAdapter: $mathAdapter);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function fromPrimitive(mixed $mixed, ?int $precision = null): static
    {
        //Dealing with big integers. Best to use FloatType.
        if (is_numeric($mixed) && ($mixed >= PHP_INT_MAX && !Ctype::ctype_digit(strval($mixed)))) {
            throw new RuntimeException('Incorrect type used. Use FloatType instead.');
        }

        if (!is_numeric($mixed)) {
            throw new InvalidTransformationException(gettype($mixed), static::class);
        }

        return new static(intval(round(floatval($mixed))));
    }

    /**
     * {@inheritdoc}
     *
     * @return string|int|float|bool
     */
    protected function getScalar(Primitive $primitive): string | int | float | bool
    {
        if ((string) Primitive::BOOL() === (string) $primitive) {
            if (1 === $this->getScalarValue()) {
                return true;
            }

            if (0 === $this->getScalarValue()) {
                return false;
            }

            throw new InvalidTypeCastException($this, Primitive::BOOL());
        }

        return match ((string) $primitive) {
            (string) Primitive::FLOAT() => (float) $this->getScalarValue(),
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
