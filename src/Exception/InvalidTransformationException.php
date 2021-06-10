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

namespace Typing\Exception;

use Exception;
use ReflectionClass;
use ReflectionException;
use TypeError;

/**
 * Class InvalidTransformationException.
 */
class InvalidTransformationException extends TypeError
{
    /**
     * @param string         $typeFrom
     * @param string         $typeTo
     * @param string|null    $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct(
        string $typeFrom,
        string $typeTo,
        ?string $message = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        $typeFrom = ('double' === $typeFrom) ? 'float' : $typeFrom;
        $message = sprintf(
            $message ?? 'Could not transform %s to %s.',
            $this->getWithoutNamespace($typeFrom),
            $this->getWithoutNamespace($typeTo)
        );

        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string $fullyQualified
     *
     * @return string
     */
    private function getWithoutNamespace(string $fullyQualified): string
    {
        try {
            if (false === class_exists($fullyQualified)) {
                // Throwing this to keep the catch consistent.
                throw new ReflectionException();
            }

            return (new ReflectionClass($fullyQualified))->getShortName();
        } catch (ReflectionException) {
            // We just return the FQDN below.
        }

        return $fullyQualified;
    }
}
