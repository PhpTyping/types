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
use Typing\Model\Primitive;
use Typing\Type\NumberObjectInterface;
use Typing\Type\PrimitiveLoaderInterface;

/**
 * Class InvalidTypeCastException.
 */
class InvalidTypeCastException extends InvalidTransformationException
{
    /**
     * @param PrimitiveLoaderInterface|NumberObjectInterface $typeFrom
     * @param Primitive|null                                 $typeTo
     * @param string|null                                    $message
     * @param int                                            $code
     * @param Exception|null                                 $previous
     */
    public function __construct(
        PrimitiveLoaderInterface | NumberObjectInterface $typeFrom,
        ?Primitive $typeTo = null,
        ?string $message = null,
        int $code = 0,
        Exception $previous = null
    ) {
        $typeTo = $typeTo ?? 'null';
        $message = $message ?? 'Object of class %s could not be converted to %s';
        parent::__construct($typeFrom::class, (string) $typeTo, $message, $code, $previous);
    }
}
