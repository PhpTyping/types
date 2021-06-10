<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Typing\Exception\InvalidTransformationException;

/**
 * Class InvalidTransformationExceptionTest.
 */
class InvalidTransformationExceptionTest extends TestCase
{
    public function testReturnsFQDNWhenNotFoundByReflection()
    {
        $imaginaryClass = 'My\\Really\\Long\\Class\\Namespace';
        $this->expectException(InvalidTransformationException::class);
        $this->expectExceptionMessage("Could not transform {$imaginaryClass} to string");
        throw $this->createException($imaginaryClass, 'string');
    }

    protected function createException(string $typeFrom, string $typeTo): InvalidTransformationException
    {
        return new InvalidTransformationException($typeFrom, $typeTo);
    }
}
