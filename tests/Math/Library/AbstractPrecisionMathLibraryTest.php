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

use Typing\Math\Library\MathLibraryInterface;

/**
 * Class AbstractMathLibraryTest.
 */
abstract class AbstractPrecisionMathLibraryTest extends AbstractMathLibraryTest
{
    /**
     * @var MathLibraryInterface
     */
    protected MathLibraryInterface $mathLibrary;

    public function testCanAdd()
    {
        $this->assertEquals('4.4', $this->mathLibrary->add('2.2', '2.2', 1));
    }

    public function testCanSubtract()
    {
        $this->assertEquals('0.1', $this->mathLibrary->subtract('2.3', '2.2', 1));
        parent::testCanSubtract();
    }

    public function testCanMultiply()
    {
        $this->assertEquals('4', $this->mathLibrary->multiply('2.2', '2.2')); //No Precision
        $this->assertEquals('4.84', $this->mathLibrary->multiply('2.2', '2.2', 2)); //2 Precision points
        parent::testCanMultiply();
    }

    public function testCanDivide()
    {
        $this->assertEquals('2.0', $this->mathLibrary->divide('2.2', '1.1', 1));
        parent::testCanDivide();
    }

    public function testCanCompare()
    {
        $this->assertEquals('1', $this->mathLibrary->compare('1.4', '1.04', 2));
        parent::testCanCompare();
    }

    public function testCanPower()
    {
        $this->assertEquals('766217865.41', $this->mathLibrary->power('5.5', '12', 2));
        parent::testCanPower();
    }

    public function testCanSquareRoot()
    {
        $this->assertEquals('7.0278', $this->mathLibrary->squareRoot('49.39', 4));
        $this->assertEquals('7.03', $this->mathLibrary->squareRoot('49.39', 2));
        $this->assertEquals('37.939', $this->mathLibrary->squareRoot('1439.39', 3));
        parent::testCanSquareRoot();
    }

    public function testCanAbsolute()
    {
        $this->assertEquals('49.39', $this->mathLibrary->absolute('-49.39'));
        parent::testCanAbsolute();
    }

    public function testCanNegate()
    {
        $this->assertEquals('-49.39', $this->mathLibrary->negate('49.39'));
        $this->assertEquals('49.39', $this->mathLibrary->negate('-49.39'));
        parent::testCanNegate();
    }
}
