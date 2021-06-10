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

namespace Typing\Math;

use DivisionByZeroError;
use RuntimeException;
use Throwable;
use Typing\Exception\InvalidNumberException;
use Typing\Math\Library\BcMath;
use Typing\Math\Library\Gmp;
use Typing\Math\Library\MathLibraryInterface;
use Typing\Math\Library\Spl;

/**
 * Class DefaultMathLibraryAdapter.
 */
class DefaultMathAdapter extends AbstractMathAdapter implements MathAdapterInterface
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
        return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand, $precision);
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     *
     * @throws DivisionByZeroError
     */
    public function divide(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        if ('0' === $rightOperand) {
            throw new DivisionByZeroError();
        }

        return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand, $precision);
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
        return $this->getDelegateResult(
            __FUNCTION__,
            $leftOperand,
            $rightOperand,
            $precision,
            MathInterface::TYPE_FLOAT
        );
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
        return $this->getDelegateResult(__FUNCTION__, $operand, $dividedBy, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $operand, null, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $operand);
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
        return $this->getDelegateResult(__FUNCTION__, $operand);
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
        $type = $this->getOperationType($operand);

        if ($this->isWholeNumber($type, $operand)) {
            return $this->getDelegateResult(__FUNCTION__, $operand);
        }

        throw $this->createNotRealNumberException();
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
        $type = $this->getOperationType($leftOperand, $rightOperand);

        if ($this->isWholeNumber($type, $leftOperand, $rightOperand)) {
            return $this->getDelegateResult(__FUNCTION__, $leftOperand, $rightOperand);
        }

        throw $this->createNotRealNumberException();
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
        $type = $this->getOperationType($operand);
        $exception = null;

        if ($this->isWholeNumber($type, $operand)) {
            foreach ($this->getDelegates($type) as $library) {
                try {
                    return $library->root($operand, $nth);
                } catch (Throwable $e) {
                    // Save last exception and try next library.
                    $exception = new RuntimeException($e->getMessage(), $e->getCode(), $e);
                    continue;
                }
            }
        }

        throw $exception ?? $this->createNotRealNumberException();
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
        return $this->getDelegateResult(__FUNCTION__, $operand);
    }

    /**
     * @param string $operand
     * @param int    $reps
     *
     * @return bool
     */
    public function isPrime(string $operand, int $reps = 10): bool
    {
        $type = $this->getOperationType($operand);
        $exception = null;

        if ($this->getPrecision($operand) > 0 || '1' === $operand) {
            return false;
        }

        if ('2' === $operand) {
            return true;
        }

        foreach ($this->getDelegates($type) as $library) {
            try {
                return $library->isPrime($operand, $reps);
            } catch (Throwable $e) {
                // Save last exception and try next library.
                $exception = new RuntimeException($e->getMessage(), $e->getCode(), $e);
                continue;
            }
        }

        throw $exception ?? $this->createNewUnknownErrorException();
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
        return $this->getDelegateResult(__FUNCTION__, $operand, null, $precision);
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
        return $this->getDelegateResult(__FUNCTION__, $operand);
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
        return $this->getDelegateResult(__FUNCTION__, $operand);
    }

    /**
     * @return MathLibraryInterface[]
     */
    protected function getDefaultDelegates(): array
    {
        //Array is sorted in order of preference. Override in child class if so desired.
        return [
            new BcMath($this->getRoundingStrategy()),
            new Gmp(),
            new Spl($this->getRoundingStrategy()),
        ];
    }

    /**
     * @return InvalidNumberException
     */
    private function createNotRealNumberException(): InvalidNumberException
    {
        return new InvalidNumberException('Arguments must be whole, positive numbers.');
    }
}
