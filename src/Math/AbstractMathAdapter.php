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

use Generator;
use OutOfBoundsException;
use RuntimeException;
use Throwable;
use Typing\Exception\InvalidNumberException;
use Typing\Math\Library\MathLibraryInterface;
use Typing\Type\StringObject;
use Typing\Type\TypedCollectionInterface;

/**
 * Class AbstractMathAdapter.
 */
abstract class AbstractMathAdapter implements MathAdapterInterface
{
    /**
     * @var NumberValidatorInterface
     */
    private NumberValidatorInterface $validator;

    /**
     * @var MathLibraryInterface[]
     */
    private array $delegates = [];

    /**
     * @var int
     */
    private int $roundingStrategy;

    /**
     * @param NumberValidatorInterface|null                            $validator
     * @param TypedCollectionInterface<int, MathLibraryInterface>|null $delegates
     * @param int                                                      $roundingStrategy
     */
    public function __construct(
        NumberValidatorInterface $validator = null,
        TypedCollectionInterface $delegates = null,
        int $roundingStrategy = PHP_ROUND_HALF_UP
    ) {
        if ($delegates instanceof TypedCollectionInterface && !$delegates->isOfType(MathLibraryInterface::class)) {
            $supported = MathLibraryInterface::class;
            throw new OutOfBoundsException("Delegates may only be of type {$supported}");
        }

        if (null !== $roundingStrategy && !in_array($roundingStrategy, static::getSupportedRoundingStrategies())) {
            throw new OutOfBoundsException(
                'Unsupported rounding strategy. Please refer to PHP\'s documentation on rounding.'
            );
        }

        $this->validator = $validator ?? new DefaultNumberValidator();
        $this->roundingStrategy = $roundingStrategy;
        $this->delegates = $delegates ? $delegates->toArray() : $this->getDefaultDelegates();
    }

    /**
     * @param string|int|float $number
     *
     * @return int
     */
    public static function getNumberPrecision(string | int | float $number): int
    {
        $string = StringObject::fromPrimitive($number);
        if ($string->contains('.')) {
            return $string->substr(($string->indexOf('.') + 1), $string->length())->count();
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getRoundingStrategy(): int
    {
        return $this->roundingStrategy;
    }

    /**
     * Returns the precision of number.
     *
     * @param string|int|float $number
     *
     * @return int
     */
    public function getPrecision(string | int | float $number): int
    {
        if ($this->validator->isValid($number)) {
            return static::getNumberPrecision($number);
        }

        throw new InvalidNumberException(sprintf('Invalid number: %s', ($number ?: gettype($number))));
    }

    /**
     * @return MathLibraryInterface[]
     */
    abstract protected function getDefaultDelegates(): array;

    /**
     * Iterates through libraries to operate on.
     *
     * @param string $type
     *
     * @return Generator<MathLibraryInterface>
     */
    protected function getDelegates(string $type): Generator
    {
        foreach ($this->delegates as $library) {
            if ($library->isEnabled() && $library->supportsOperationType($type)) {
                yield $library;
            }
        }
    }

    /**
     * @param string $type
     * @param string $leftOperand
     * @param string $rightOperand
     *
     * @return bool
     */
    protected function isWholeNumber(string $type, string $leftOperand, string $rightOperand = '0'): bool
    {
        return !(self::TYPE_INT !== $type || $leftOperand < 0 || $rightOperand < 0);
    }

    /**
     * Ensures operands are valid and returns the operation type.
     *
     * @param string      $operandA
     * @param string|null $operandB
     *
     * @return string
     *
     *@throws InvalidNumberException when an operand is not a valid number
     */
    protected function getOperationType(string $operandA, string $operandB = null): string
    {
        $getType = function (string $haystack, string $defaultType = self::TYPE_INT) {
            return (str_contains($haystack, '.')) ? self::TYPE_FLOAT : $defaultType;
        };

        if (!$this->validator->isValid($operandA)) {
            throw $this->createNewInvalidNumberException($operandA);
        }

        $type = $getType($operandA);

        if (null !== $operandB) {
            if (!$this->validator->isValid($operandB)) {
                throw $this->createNewInvalidNumberException($operandB);
            }

            $type = $getType($operandB, $type);
        }

        return $type;
    }

    /**
     * Much like a "chain-of-responsibility" this method iterates through the available delegates, attempting to perform
     * the desired operation if it exists.
     * If the operation fails due to a library error, it will try the next library. If all libraries fail then
     * it will use the last exception thrown.
     *
     * @param string      $operation
     * @param string      $leftOperand
     * @param string|null $rightOperand
     * @param int|null    $precision
     * @param string|null $overrideType
     *
     * @return mixed
     */
    protected function getDelegateResult(
        string $operation,
        string $leftOperand,
        string $rightOperand = null,
        int $precision = null,
        string $overrideType = null
    ): mixed {
        $type = $overrideType ?? $this->getOperationType($leftOperand, $rightOperand);
        $exception = null;

        foreach ($this->getDelegates($type) as $library) {
            try {
                return $this->getLibraryResult($library, $operation, $leftOperand, $rightOperand, $precision);
            } catch (Throwable $e) {
                // Save last exception and try next library.
                $exception = new RuntimeException($e->getMessage(), $e->getCode(), $e);
                continue;
            }
        }

        //We'll use the last exception thrown, otherwise create one.
        throw $exception ?? $this->createNewUnknownErrorException();
    }

    /**
     * Supported rounding strategies.
     *
     * @return int[]
     */
    protected static function getSupportedRoundingStrategies(): array
    {
        return [
            PHP_ROUND_HALF_UP,
            PHP_ROUND_HALF_DOWN,
            PHP_ROUND_HALF_EVEN,
            PHP_ROUND_HALF_ODD,
        ];
    }

    /**
     * @param mixed $num
     *
     * @return InvalidNumberException
     */
    protected function createNewInvalidNumberException(mixed $num): InvalidNumberException
    {
        return new InvalidNumberException(sprintf('Invalid number: %s', ($num ?: gettype($num))));
    }

    /**
     * @return RuntimeException
     */
    protected function createNewUnknownErrorException(): RuntimeException
    {
        return new RuntimeException('Unknown error.');
    }

    /**
     * This method tries to call the operation with the proper number of arguments based on whether they are null.
     *
     * @param MathLibraryInterface $library
     * @param string               $operation
     * @param string               $leftOperand
     * @param string|null          $rightOperand
     * @param int|null             $precision
     *
     * @return mixed
     */
    private function getLibraryResult(
        MathLibraryInterface $library,
        string $operation,
        string $leftOperand,
        string $rightOperand = null,
        int $precision = null
    ): mixed {
        if (null !== $precision) {
            if (null !== $rightOperand) {
                return $library->$operation($leftOperand, $rightOperand, $precision);
            }

            return $library->$operation($leftOperand, $precision);
        }

        return $this->getNonPrecisionResult($library, $operation, $leftOperand, $rightOperand);
    }

    /**
     * @param MathLibraryInterface $library
     * @param string               $operation
     * @param string               $leftOperand
     * @param string|null          $rightOperand
     *
     * @return mixed
     */
    private function getNonPrecisionResult(
        MathLibraryInterface $library,
        string $operation,
        string $leftOperand,
        string $rightOperand = null
    ): mixed {
        if (null !== $rightOperand) {
            return $library->$operation($leftOperand, $rightOperand);
        }

        return $library->$operation($leftOperand);
    }
}
