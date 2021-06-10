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
 * Interface NumberObjectInterface.
 */
interface NumberObjectInterface extends ScalarValueObjectInterface, StringCastableInterface
{
    /**
     * Sums current NumberTypeInterface and number in argument.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function plus(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /***
     * Subtracts number passed from current NumberTypeInterface.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function minus(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Multiplies current NumberTypeInterface by the number passed.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function multipliedBy(
        StringObject | NumberObjectInterface | string | float | int $num
    ): NumberObjectInterface;

    /**
     * Divides current NumberTypeInterface by the number passed.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function dividedBy(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Compares current NumberTypeInterface to value passed.
     * Same rules as spaceship or version_compare.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function compare(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Returns value of NumberTypeInterface modulo num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function modulo(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Returns NumberTypeInterface to the power of num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function power(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Returns the square root of NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function squareRoot(): NumberObjectInterface;

    /**
     * Returns the absolute value of NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function absolute(): NumberObjectInterface;

    /**
     * Returns the negated/opposite of NumberTypeInterface value.
     *
     * @return NumberObjectInterface
     */
    public function negate(): NumberObjectInterface;

    /**
     * Returns NumberTypeInterface factorial.
     *
     * @return NumberObjectInterface
     */
    public function factorial(): NumberObjectInterface;

    /**
     * Returns the greatest common divider between NumberTypeInterface and num.
     *
     * @param StringObject|NumberObjectInterface|string|float|int $num
     *
     * @return NumberObjectInterface
     */
    public function gcd(StringObject | NumberObjectInterface | string | float | int $num): NumberObjectInterface;

    /**
     * Returns the root of NumberTypeInterface to the num.
     *
     * @param int $num
     *
     * @return NumberObjectInterface
     */
    public function root(int $num): NumberObjectInterface;

    /**
     * Return the next prime number after NumberTypeInterface.
     *
     * @return NumberObjectInterface
     */
    public function getNextPrime(): NumberObjectInterface;

    /**
     * Returns true of NumberTypeInterface is prime. False otherwise.
     *
     * @return bool
     */
    public function isPrime(): bool;

    /**
     * Returns true if NumberTypeInterface is a perfect square. False otherwise.
     *
     * @return bool
     */
    public function isPerfectSquare(): bool;

    /**
     * Gets the current precision (Should be 0 for IntType).
     *
     * @return int
     */
    public function getPrecision(): int;

    /**
     * Creates a new instance of NumberTypeInterface from the variable passed.
     *
     * @param mixed    $mixed
     * @param int|null $precision
     *
     * @return NumberObjectInterface
     */
    public static function fromPrimitive(mixed $mixed, ?int $precision = null): NumberObjectInterface;
}
