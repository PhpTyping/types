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

namespace Typing\Math\Library;

use Typing\Exception\InvalidLibraryException;
use Typing\Exception\InvalidNumberException;
use Typing\Math\DefaultMathAdapter;
use Typing\Type\StringObject;

/**
 * Class Spl.
 */
class Spl implements MathLibraryInterface
{
    private const FACTORIAL_TWO = 2;
    private const GAMMA_CONSTANT = 0.577215664901532860606512090;
    private const GAMMA_SMP = 0.001;
    private const GAMMA_SM_SMP = 12.0;
    private const GAMMA_DEN_MX = 8;
    private const GAMMA_MAX = 171.624;

    /**
     * @var int
     */
    private int $roundingStrategy;

    /**
     * @param int $roundingStrategy a PHP_ROUND_HALF_* integer
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
        return (string) ($this->isIntOperation($precision) ? (intval($leftOperand) + intval($rightOperand)) :
            round(floatval($leftOperand) + floatval($rightOperand), $precision, $this->roundingStrategy));
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
        return (string) ($this->isIntOperation($precision) ? (intval($leftOperand) - intval($rightOperand)) :
            round(floatval($leftOperand) - floatval($rightOperand), $precision, $this->roundingStrategy));
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
        return (string) ($this->isIntOperation($precision) ? (intval($leftOperand) * intval($rightOperand)) :
            round(floatval($leftOperand) * floatval($rightOperand), ($precision ?? 0), $this->roundingStrategy));
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
        $preciseValue = $this->isIntOperation($precision) ? strval(intval($leftOperand) / intval($rightOperand)) :
            strval(round(floatval($leftOperand) / floatval($rightOperand), $precision, $this->roundingStrategy));
        $calculatedPrecision = $this->getPrecision($preciseValue);
        if ($precision !== $calculatedPrecision && 0 === $calculatedPrecision) {
            return $preciseValue.'.0';
        }

        return $preciseValue;
    }

    /**
     * Compare two arbitrary precision numbers.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int    $precision
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function compare(string $leftOperand, string $rightOperand, int $precision = 0): string
    {
        return strval($leftOperand <=> $rightOperand);
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
        return (string) round(
            fmod(
                floatval($operand),
                floatval($dividedBy)
            ),
            ($precision ?? 0),
            $this->roundingStrategy
        );
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
        return (string) round(
            pow(
                floatval($leftOperand),
                floatval($rightOperand)
            ),
            ($precision ?? 0),
            $this->roundingStrategy
        );
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
        return (string) round(sqrt(floatval($operand)), $precision, $this->roundingStrategy);
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
        return (string) abs($this->hasPrecision($operand) ? floatval($operand) : intval($operand));
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
        return strval(floatval($operand) * -1);
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
        if ($this->hasPrecision($operand)) {
            ++$operand;
            $result = $this->gamma((string) $operand);

            return (string) ($this->hasPrecision($operand) ? floatval($result) : intval($result));
        }

        $factorial = function (string $num) use (&$factorial) {
            if ($num < self::FACTORIAL_TWO) {
                return 1;
            }

            return $factorial(strval(floatval($num) - 1)) * $num;
        };

        return (string) $factorial($operand);
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
        $gcd = function (string $operandA, string $operandB) use (&$gcd) {
            return $operandB ? $gcd($operandB, strval($operandA % $operandB)) : $operandA;
        };

        $exponent = $this->getSmallestDecimalPlaceCount($leftOperand, $rightOperand);

        return (string) (
            $gcd(
                strval(floatval($leftOperand) * (pow(10, $exponent))),
                strval(floatval($rightOperand) * (pow(10, $exponent)))
            ) / pow(10, $exponent)
        );
    }

    /**
     * Calculates to the nth root.
     *
     * @param string $operand
     * @param int    $nth
     *
     * @throws InvalidLibraryException
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function root(string $operand, int $nth): string
    {
        throw new InvalidLibraryException('Not a valid library for root^n.');
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
        $operand = (intval($operand) + 1);
        for ($i = $operand;; ++$i) {
            if ($this->isPrime(strval($i))) {
                break;
            }
        }

        return (string) $i;
    }

    /**
     * @param string $operand
     * @param int    $reps
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isPrime(string $operand, int $reps = 10): bool
    {
        $sqRoot = floor(sqrt(floatval($operand)));
        for ($i = 2; $i <= $sqRoot; ++$i) {
            if (($operand % $i) === 0) {
                break;
            }
        }

        return intval($sqRoot) === ($i - 1);
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
        $candidate = $this->squareRoot($operand, $precision + 1);

        return (string) $candidate === (string) intval($candidate);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @param string $type
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function supportsOperationType(string $type): bool
    {
        // Supports both float and int.
        return true;
    }

    /**
     * From: https://hewgill.com/picomath/index.html.
     *
     * @param string $operand
     *
     * @return string
     */
    public function gamma(string $operand): string
    {
        if ($operand <= 0.0) {
            throw new InvalidNumberException('Operand must be a positive number.');
        }

        if ($operand < self::GAMMA_SMP) {
            return strval(1.0 / ($operand * (1.0 + self::GAMMA_CONSTANT * $operand)));
        }

        if ($operand < self::GAMMA_SM_SMP) {
            return $this->getGammaSmSmp($operand);
        }

        if ($operand > self::GAMMA_MAX) {
            throw new InvalidNumberException('Number too large.');
        }

        return (string) exp(floatval($this->logGamma((string) $operand)));
    }

    /**
     * From https://hewgill.com/picomath/index.html.
     *
     * @param string $operand
     *
     * @return string
     */
    public function logGamma(string $operand): string
    {
        if ($operand <= 0.0) {
            throw new InvalidNumberException('Operand must be a positive number.');
        }

        if ($operand < self::GAMMA_SM_SMP) {
            $operand = $this->gamma((string) $operand);

            return (string) log(abs($this->hasPrecision($operand) ? floatval($operand) : intval($operand)));
        }

        $logGammaC = [
            1.0 / 12.0,
            -1.0 / 360.0,
            1.0 / 1260.0,
            -1.0 / 1680.0,
            1.0 / 1188.0,
            -691.0 / 360360.0,
            1.0 / 156.0,
            -3617.0 / 122400.0,
        ];

        $logGammaZ = 1.0 / (floatval($operand) * floatval($operand));
        $sum = $logGammaC[7];
        for ($i = 6; $i >= 0; --$i) {
            $sum *= $logGammaZ;
            $sum += $logGammaC[$i];
        }

        $series = $sum / $operand;
        $halfLogTwoPi = 0.91893853320467274178032973640562;
        $logGamma = (floatval($operand) - 0.5) * log(floatval($operand)) - $operand + $halfLogTwoPi + $series;

        return (string) $logGamma;
    }

    /**
     * Figures out the smallest number of decimal places between the two numbers and returns that count.
     * Eg. (1.005, 2.4) => 1, (1.005, 2.5399) => 3.
     *
     * @param string $leftOperand
     * @param string $rightOperand
     *
     * @return int
     */
    private function getSmallestDecimalPlaceCount(string $leftOperand, string $rightOperand): int
    {
        $leftPrecision = DefaultMathAdapter::getNumberPrecision($leftOperand);
        $rightPrecision = DefaultMathAdapter::getNumberPrecision($rightOperand);

        return $leftPrecision < $rightPrecision ? $leftPrecision : $rightPrecision;
    }

    /**
     * Ensures that an operation is meant to be an integer operation, float operation otherwise.
     *
     * @param int $precision
     *
     * @return bool
     */
    private function isIntOperation(int $precision = 0): bool
    {
        return 0 === $precision;
    }

    /**
     * @param string|int|float $operand
     *
     * @return bool
     */
    private function hasPrecision(string | int | float $operand): bool
    {
        return StringObject::create((string) $operand)->contains('.');
    }

    /**
     * @param string|int|float $operand
     *
     * @return int
     */
    private function getPrecision(string | int | float $operand): int
    {
        $operand = StringObject::create(strval($operand));
        if ($operand->contains('.')) {
            return $operand->subStrAfter(subStr: '.', includingSubStr: false)->count();
        }

        return 0;
    }

    /**
     * @param string $operand
     *
     * @return string
     */
    private function getGammaSmSmp(string $operand): string
    {
        $currOperand = $operand;
        $base = 0;
        $lessThanOne = ($currOperand < 1);
        if ($lessThanOne) {
            $currOperand += 1.0;
        }

        if (!$lessThanOne) {
            // will use n later
            $base = floor($this->hasPrecision($operand) ? floatval($currOperand) : intval($currOperand)) - 1;
            $currOperand -= $base;
        }

        $gammaP = [
            -1.71618513886549492533811E+0,
            2.47656508055759199108314E+1,
            -3.79804256470945635097577E+2,
            6.29331155312818442661052E+2,
            8.66966202790413211295064E+2,
            -3.14512729688483675254357E+4,
            -3.61444134186911729807069E+4,
            6.64561438202405440627855E+4,
        ];

        $gammaQ = [
            -3.08402300119738975254353E+1,
            3.15350626979604161529144E+2,
            -1.01515636749021914166146E+3,
            -3.10777167157231109440444E+3,
            2.25381184209801510330112E+4,
            4.75584627752788110767815E+3,
            -1.34659959864969306392456E+5,
            -1.15132259675553483497211E+5,
        ];

        $num = 0.0;
        $den = 1.0;
        $low = $currOperand - 1;

        for ($i = 0; $i < self::GAMMA_DEN_MX; ++$i) {
            $num = ($num + $gammaP[$i]) * $low;
            $den = $den * $low + $gammaQ[$i];
        }

        $result = $num / $den + 1.0;

        if ($lessThanOne) {
            $result /= ($currOperand - 1.0);
        }

        if (!$lessThanOne) {
            for ($i = 0; $i < $base; ++$i) {
                $result *= $currOperand++;
            }
        }

        return (string) $result;
    }
}
