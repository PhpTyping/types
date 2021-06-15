<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
/* @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace Typing\Math\Library;

use Throwable;
use Typing\Exception\InvalidLibraryException;

/**
 * Class Gmp.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Gmp implements MathLibraryInterface
{
    /**
     * Add two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function add(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return gmp_strval(gmp_add(gmp_strval($leftOperand), gmp_strval($rightOperand)));
    }

    /**
     * Subtract two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function subtract(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return gmp_strval(gmp_sub($leftOperand, $rightOperand));
    }

    /**
     * Multiply two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function multiply(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return gmp_strval(gmp_mul($leftOperand, $rightOperand));
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @throws InvalidLibraryException when a non-int is passed
     *
     * @return string
     */
    public function divide(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        try {
            return gmp_strval(gmp_div_q($leftOperand, $rightOperand));
        } catch (Throwable) {
            throw new InvalidLibraryException('GMP can only divide integers.');
        }
    }

    /**
     * Compare two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function compare(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return gmp_strval(gmp_cmp($leftOperand, $rightOperand));
    }

    /**
     * Get modulo of an arbitrary precision number.
     *
     * @param string $operand
     * @param string $dividedBy
     * @param int    $precision
     *
     * @return string
     */
    public function modulo(string $operand, string $dividedBy, int $precision = 0): string
    {
        return gmp_strval(gmp_mod($operand, $dividedBy));
    }

    /**
     * Raise an arbitrary precision number to another.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function power(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return gmp_strval(gmp_pow($leftOperand, (int) $rightOperand));
    }

    /**
     * Get the square root of an arbitrary precision number.
     *
     * @param string $operand
     * @param int    $precision
     *
     * @return string
     */
    public function squareRoot(string $operand, int $precision = 0): string
    {
        return gmp_strval(gmp_sqrt($operand));
    }

    /**
     * Returns absolute value of operand.
     *
     * @param string $operand
     *
     * @return string
     */
    public function absolute(string $operand): string
    {
        return gmp_strval(gmp_abs($operand));
    }

    /**
     * Negates a number. Opposite of absolute/abs.
     *
     * @param string $operand
     *
     * @return string
     */
    public function negate(string $operand): string
    {
        return gmp_strval(gmp_neg($operand));
    }

    /**
     * Returns the factorial of operand.
     *
     * @param string $operand
     *
     * @return string
     */
    public function factorial(string $operand): string
    {
        return gmp_strval(gmp_fact($operand));
    }

    /**
     * Greatest common divisor.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     *
     * @return string
     */
    public function gcd(string $leftOperand, string $rightOperand): string
    {
        return gmp_strval(gmp_gcd($leftOperand, $rightOperand));
    }

    /**
     * Calculates to the nth root.
     *
     * @param string $operand
     * @param int    $nth
     *
     * @return string
     */
    public function root(string $operand, int $nth): string
    {
        $operand = gmp_init($operand, 10);

        return gmp_strval(gmp_root($operand, $nth));
    }

    /**
     * Gets the next prime after operand.
     *
     * @param string $operand
     *
     * @return string
     */
    public function nextPrime(string $operand): string
    {
        return gmp_strval(gmp_nextprime($operand));
    }

    /**
     * @param string $operand
     * @param int    $reps
     *
     * @return bool
     */
    public function isPrime(string $operand, int $reps = 10): bool
    {
        return 0 < gmp_prob_prime($operand, $reps);
    }

    /**
     * Checks if operand is perfect square.
     *
     * @param string $operand
     * @param int    $precision
     *
     * @return bool
     */
    public function isPerfectSquare(string $operand, int $precision = 0): bool
    {
        return gmp_perfect_square($operand);
    }

    /**
     * The gamma function.
     *
     * @param string $operand
     *
     * @return string
     */
    public function gamma(string $operand): string
    {
        throw $this->createInvalidLibraryException(__FUNCTION__);
    }

    /**
     * The log-gamma function.
     *
     * @param string $operand
     *
     * @return string
     */
    public function logGamma(string $operand): string
    {
        throw $this->createInvalidLibraryException(__FUNCTION__);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function supportsOperationType(string $type): bool
    {
        //Supports only int.
        return self::TYPE_FLOAT !== $type;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return extension_loaded('gmp');
    }

    /**
     * @param string $methodName
     *
     * @return InvalidLibraryException
     */
    private function createInvalidLibraryException(string $methodName): InvalidLibraryException
    {
        return new InvalidLibraryException("Not a valid library for {$methodName}");
    }
}
