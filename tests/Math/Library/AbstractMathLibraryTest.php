<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Math\Library;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Typing\Math\Library\MathLibraryInterface;

/**
 * Class AbstractMathLibraryTest.
 */
abstract class AbstractMathLibraryTest extends TestCase
{
    /**
     * @var MathLibraryInterface
     */
    protected MathLibraryInterface $mathLibrary;

    public function testCanAdd()
    {
        $this->assertEquals('4', $this->mathLibrary->add(2, 2, precision: 0));
    }

    public function testCanSubtract()
    {
        $this->assertEquals('0', $this->mathLibrary->subtract(2, 2));
    }

    public function testCanMultiply()
    {
        $this->assertEquals('4', $this->mathLibrary->multiply(2, 2));
    }

    public function testCanDivide()
    {
        $this->assertEquals('1', $this->mathLibrary->divide(2, 2));
    }

    public function testCanCompare()
    {
        $this->assertEquals('0', $this->mathLibrary->compare('3', '3'));
    }

    public function testCanModulo()
    {
        $this->assertEquals('5', $this->mathLibrary->modulo(5, 10));
    }

    public function testCanPower()
    {
        $this->assertEquals('9765625', $this->mathLibrary->power(5, 10));
    }

    public function testCanSquareRoot()
    {
        $this->assertEquals(3, $this->mathLibrary->squareRoot('9'));
    }

    public function testCanAbsolute()
    {
        $this->assertEquals('9', $this->mathLibrary->absolute('-9'));
    }

    public function testCanNegate()
    {
        $this->assertEquals('9', $this->mathLibrary->negate('-9'));
        $this->assertEquals('-9', $this->mathLibrary->negate('9'));
    }

    public function testCanFactorial()
    {
        $this->assertEquals('3628800', $this->mathLibrary->factorial('10'));
    }

    public function testCanGetGcd()
    {
        $this->assertEquals('10', $this->mathLibrary->gcd('10', '50'));
        $this->assertEquals('40', $this->mathLibrary->gcd('80', '120'));
    }

    public function testCanRoot()
    {
        $this->assertEquals('3', $this->mathLibrary->root('32', 3));
    }

    public function testCanCheckIsPrime()
    {
        $this->assertTrue($this->mathLibrary->isPrime(5));
    }

    public function testCanCheckNextPrime()
    {
        $this->assertEquals('7', $this->mathLibrary->nextPrime('5'));
    }

    public function testCanCheckIsPerfectSquare()
    {
        $this->assertTrue($this->mathLibrary->isPerfectSquare(1));
        $this->assertTrue($this->mathLibrary->isPerfectSquare(4));
        $this->assertTrue($this->mathLibrary->isPerfectSquare(9));
        $this->assertFalse($this->mathLibrary->isPerfectSquare(3));
        $this->assertFalse($this->mathLibrary->isPerfectSquare(5));
        $this->assertFalse($this->mathLibrary->isPerfectSquare(26));
    }

    public function testCanGamma()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for gamma');
        $this->mathLibrary->gamma(.5);
    }

    public function testCanLogGamma()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for logGamma');
        $this->mathLibrary->logGamma(.5);
    }
}
