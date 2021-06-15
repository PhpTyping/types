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

use Typing\Exception\InvalidLibraryException;
use Typing\Type\StringObject;

/**
 * Class BcMath.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BcMath implements MathLibraryInterface
{
    /**
     * @var int
     */
    private int $roundingStrategy;

    /**
     * @param int $roundingStrategy
     */
    public function __construct(int $roundingStrategy)
    {
        $this->roundingStrategy = $roundingStrategy;
    }

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
        return bcadd($leftOperand, $rightOperand, $precision);
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
        return (string) round(
            floatval(bcsub($leftOperand, $rightOperand, $precision + 1)),
            $precision,
            $this->roundingStrategy
        );
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
        return bcmul($leftOperand, $rightOperand, $precision);
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     */
    public function divide(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return bcdiv($leftOperand, $rightOperand, $precision) ?? '';
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
        if ($this->isVersionComparison($leftOperand, $rightOperand)) {
            throw new InvalidLibraryException('BcMath cannot do version compare.');
        }

        return strval(bccomp($leftOperand, $rightOperand, $precision));
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
        if ($precision > 0) {
            throw new InvalidLibraryException('Precision is not supported. Use Spl::modulo, it uses fmod.');
        }

        return bcmod($operand, $dividedBy) ?? '';
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
        return bcpow($leftOperand, $rightOperand, $precision);
    }

    /**
     * Get the square root of an arbitrary precision number.
     * Wrapped around because bcsqrt does not round, instead it truncates. To make it compatible with SPL, we use round.
     *
     * If truncating behavior is desired, then use the function directly.
     *
     * @param string $operand
     * @param int    $precision
     *
     * @return string
     */
    public function squareRoot(string $operand, int $precision = 0): string
    {
        return (string) round(floatval(bcsqrt($operand, $precision + 1)), $precision, $this->roundingStrategy);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
    }

    /**
     * @param string $operand
     * @param int    $reps
     *
     * @return bool
     */
    public function isPrime(string $operand, int $reps = 10): bool
    {
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        throw $this->createInvalidLibraryException(__FUNCTION__);
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
        //Supports both float and int.
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return extension_loaded('bcmath');
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     *
     * @return bool
     */
    private function isVersionComparison(string $leftOperand, string $rightOperand): bool
    {
        $leftOperand = StringObject::create($leftOperand);
        $rightOperand = StringObject::create($rightOperand);

        return $leftOperand->isSemVer() && $rightOperand->isSemVer() &&
            ($leftOperand->contains('.') || $rightOperand->contains('.'));
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
