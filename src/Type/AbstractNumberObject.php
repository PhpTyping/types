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

use Typing\Math\DefaultMathAdapter;
use Typing\Math\MathAdapterInterface;
use Typing\Type\Traits\ScalarValueObjectTrait;
use Typing\Type\Traits\StringCastableTrait;

/**
 * Class AbstractNumberObject.
 */
abstract class AbstractNumberObject implements NumberObjectInterface, BoxableInterface
{
    use ScalarValueObjectTrait;
    use StringCastableTrait;

    /**
     * @var int
     */
    private int $precision;

    /**
     * @var MathAdapterInterface
     */
    protected MathAdapterInterface $mathAdapter;

    /**
     * AbstractNumberType constructor.
     * Precision order of priority: Argument != null > $num's precision > null precision.
     * So for an int, 0 should be passed for precision, otherwise it will auto-convert to float (if null or $num > 0).
     *
     * @param int|float                 $num
     * @param int|null                  $precision
     * @param MathAdapterInterface|null $mathAdapter
     */
    public function __construct(int | float $num, int $precision = null, MathAdapterInterface $mathAdapter = null)
    {
        $this->mathAdapter = $mathAdapter ?? new DefaultMathAdapter();
        $this->precision = $precision ?? $this->getMathAdapter()->getPrecision($num);
        $this->value = ($this->getPrecision() > 0) ?
            round($num, $this->getPrecision(), $this->mathAdapter->getRoundingStrategy()) : $num;
    }

    /**
     * Sums current NumberTypeInterface and number in argument.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function plus(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation('add', $num);
    }

    /***
     * Subtracts number passed from current NumberTypeInterface.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function minus(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation('subtract', $num);
    }

    /**
     * Multiplies current NumberTypeInterface by the number passed.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function multipliedBy(
        StringObject | NumberObjectInterface | string | float | int $num
    ): NumberObjectInterface {
        return $this->getAdapterOperation('multiply', $num);
    }

    /**
     * Divides current NumberTypeInterface by the number passed.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function dividedBy(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation('divide', $num);
    }

    /**
     * Compares current NumberTypeInterface to value passed.
     * Same rules as spaceship or version_compare.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function compare(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation(__FUNCTION__, $num);
    }

    /**
     * Returns value of NumberTypeInterface modulo num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function modulo(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation(__FUNCTION__, $num);
    }

    /**
     * Returns NumberTypeInterface to the power of num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function power(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation(__FUNCTION__, $num);
    }

    /**
     * Returns the square root of NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function squareRoot(): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->squareRoot(
                $this->toString(),
                $this->getPrecision()
            ),
            $this->getPrecision()
        );
    }

    /**
     * Returns the absolute value of NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function absolute(): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->absolute($this->toString()),
            $this->getPrecision()
        );
    }

    /**
     * Returns the negated/opposite of NumberTypeInterface value.
     *
     * @return NumberObjectInterface
     */
    public function negate(): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->negate($this->toString()),
            $this->getPrecision()
        );
    }

    /**
     * Returns NumberTypeInterface factorial.
     *
     * @return NumberObjectInterface
     */
    public function factorial(): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->factorial($this->toString()),
            $this->getPrecision()
        );
    }

    /**
     * Returns the greatest common divider between NumberTypeInterface and num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function gcd(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface
    {
        return $this->getAdapterOperation(__FUNCTION__, $num);
    }

    /**
     * Returns the root of NumberTypeInterface to the num.
     *
     * @param int $num
     *
     * @return NumberObjectInterface
     */
    public function root(int $num): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->root($this->toString(), $num),
            $this->getPrecision()
        );
    }

    /**
     * Return the next prime number after NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function getNextPrime(): NumberObjectInterface
    {
        return static::fromPrimitive($this->getMathAdapter()->nextPrime($this->toString()));
    }

    /**
     * Returns true if NumberTypeInterface is a prime number. False otherwise.
     *
     * @return bool
     */
    public function isPrime(): bool
    {
        return $this->getMathAdapter()->isPrime($this->toString());
    }

    /**
     * Returns true if NumberTypeInterface is a perfect square. False otherwise.
     *
     * @return bool
     */
    public function isPerfectSquare(): bool
    {
        return $this->getMathAdapter()->isPerfectSquare($this->toString());
    }

    /**
     * @return bool
     */
    public function isEven(): bool
    {
        return 0 === intval($this->modulo(2)->getScalarValue());
    }

    /**
     * @return bool
     */
    public function isOdd(): bool
    {
        return 0 !== intval($this->modulo(2)->getScalarValue());
    }

    /**
     * Gets the current precision (Should be 0 for IntType).
     *
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @return MathAdapterInterface
     */
    protected function getMathAdapter(): MathAdapterInterface
    {
        return $this->mathAdapter;
    }

    /**
     * @param string $operation
     * @param mixed  $operand
     *
     * @return NumberObjectInterface
     */
    private function getAdapterOperation(string $operation, mixed $operand): NumberObjectInterface
    {
        return static::fromPrimitive(
            $this->getMathAdapter()->$operation(
                $this->toString(),
                $this->getTypeForOperand($operand)->toString(),
                $this->getPrecision()
            ),
            $this->getPrecision()
        );
    }

    /**
     * @param mixed $operand
     *
     * @return NumberObjectInterface
     */
    private function getTypeForOperand(mixed $operand): NumberObjectInterface
    {
        if ($operand instanceof IntObject) {
            return new IntObject($operand->getScalarValue(), $this->getMathAdapter());
        }

        if ($operand instanceof FloatObject) {
            return new FloatObject(
                $operand->getScalarValue(),
                max([$operand->getPrecision(), $this->getPrecision()]),
                $this->getMathAdapter()
            );
        }

        if ($operand instanceof ScalarValueObjectInterface) {
            return static::fromPrimitive($operand->getScalarValue());
        }

        return static::fromPrimitive($operand);
    }
}
