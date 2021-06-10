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

use RuntimeException;
use Typing\Math\Library\BcMath;
use Typing\Math\Library\MathLibraryInterface;

/**
 * Class BcMathTest.
 */
class BcMathTest extends AbstractPrecisionMathLibraryTest
{
    /**
     * @var MathLibraryInterface
     */
    protected MathLibraryInterface $mathLibrary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mathLibrary = new BcMath(PHP_ROUND_HALF_UP);
    }

    public function testCanCompare()
    {
        $this->assertEquals(1, $this->mathLibrary->compare('6', '5'));
    }

    public function testCompareFailsOnSemver()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BcMath cannot do version compare');
        $this->mathLibrary->compare('0.90.01', '0.91.04', 5);
    }

    public function testCanModulo()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Precision is not supported. Use Spl::modulo, it uses fmod.');
        $this->mathLibrary->modulo('5.5', '10', 1);
    }

    public function testCanAbsolute()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for absolute');
        $this->mathLibrary->absolute('-9');
    }

    public function testCanNegate()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for negate');
        $this->mathLibrary->negate('-9');
    }

    public function testCanFactorial()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for factorial');
        $this->mathLibrary->factorial('10');
    }

    public function testCanGetGcd()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for gcd');
        $this->mathLibrary->gcd('10', '50');
    }

    public function testCanRoot()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for root');
        $this->mathLibrary->root('32', 3);
    }

    public function testCanCheckIsPrime()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for isPrime');
        $this->mathLibrary->isPrime(5);
    }

    public function testCanCheckNextPrime()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for nextPrime');
        $this->mathLibrary->nextPrime('5');
    }

    public function testCanCheckIsPerfectSquare()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for isPerfectSquare');
        $this->mathLibrary->isPerfectSquare(1);
    }
}
