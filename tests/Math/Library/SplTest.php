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
use Typing\Exception\InvalidNumberException;
use Typing\Math\Library\Spl;

/**
 * Class SplTest.
 */
class SplTest extends AbstractPrecisionMathLibraryTest
{
    protected function setUp(): void
    {
        $this->mathLibrary = new Spl(PHP_ROUND_HALF_UP);
    }

    public function testCanCompare()
    {
        //Can compare versions
        $this->assertEquals('1', $this->mathLibrary->compare('1.30.5', '1.29.99', 5));
        $this->assertEquals('1', $this->mathLibrary->compare('1.105.02', '1.049.9', 5));
        parent::testCanCompare();
    }

    public function testCanCheckNextPrime()
    {
        $this->assertEquals('7', $this->mathLibrary->nextPrime('5.5'));
        parent::testCanCheckNextPrime();
    }

    public function testCanGetGcd()
    {
        $this->assertEquals('2.2', $this->mathLibrary->gcd('4.4', '6.66'));
        $this->assertEquals('2.2', $this->mathLibrary->gcd('6.6', '4.44'));
        $this->assertEquals('2.2', $this->mathLibrary->gcd('6.666', '4.4'));
        $this->assertEquals('2.22', $this->mathLibrary->gcd('6.66', '4.44'));

        parent::testCanGetGcd();
    }

    public function testCanFactorial()
    {
        $this->assertEquals('3.3233509704478', $this->mathLibrary->factorial('2.5'));
        $this->assertEquals('24', $this->mathLibrary->factorial('4.0'));
        $this->assertEquals('287.88527781504', $this->mathLibrary->factorial('5.5'));
        parent::testCanFactorial();
    }

    public function testCanModulo()
    {
        $this->assertEquals('5.5', $this->mathLibrary->modulo('5.5', '10', 1));
        $this->assertEquals('5.3', $this->mathLibrary->modulo('55.3', '10', 1));
        $this->assertEquals('0', $this->mathLibrary->modulo('10.0', '2.0', 1));
        parent::testCanModulo();
    }

    public function testCanGamma()
    {
        $this->assertEquals('1.7724538509055', $this->mathLibrary->gamma('.5'));
        $this->assertEquals('10.136101851155', $this->mathLibrary->gamma('4.4'));
        $this->assertEquals('334838609873.69', $this->mathLibrary->gamma('15.5'));
        $this->assertEquals('5.5620924145341E+305', $this->mathLibrary->gamma('170.5'));
        $this->assertEquals('999999.42278467', $this->mathLibrary->gamma('.000001'));
    }

    public function testBadZeroGamma()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Operand must be a positive number');
        $this->mathLibrary->gamma('0');
    }

    public function testBadGamma()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Number too large');
        $this->mathLibrary->gamma('172');
    }

    public function testBadZeroLogGamma()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Operand must be a positive number');
        $this->mathLibrary->logGamma('0');
    }

    public function testGetsPrecisionAutomatically()
    {
        $this->assertEquals(1.3846153846154, $this->mathLibrary->divide(54.382, 39.3949));
    }

    public function testCanLogGamma()
    {
        $this->assertEquals('0.57236494292469', $this->mathLibrary->logGamma('.5'));
        $this->assertEquals('2.3161034914248', $this->mathLibrary->logGamma('4.4'));
        $this->assertEquals('26.536914491116', $this->mathLibrary->logGamma('15.5'));
    }

    public function testCanRoot()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a valid library for root^n');
        parent::testCanRoot();
    }
}
