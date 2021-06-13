<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Type;

use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use Typing\Type\FloatObject;
use Typing\Type\IntObject;

/**
 * Class FloatObjectTest.
 */
class FloatObjectTest extends TestCase
{
    public function testBox()
    {
        $float = new FloatObject(1.0);
        FloatObject::box($float);
        $this->assertTrue(($float instanceof FloatObject));
        $this->assertEquals(1.0, $float->getScalarValue());

        /* @var FloatObject $float */
        $float = 493.29;
        $this->assertTrue(($float instanceof FloatObject));
    }

    public function testIsEven()
    {
        $float = new FloatObject(2.0);
        $this->assertTrue($float->isEven());
        $this->assertFalse($float->isOdd());
    }

    public function testIsOdd()
    {
        $float = new FloatObject(1.0);
        $this->assertTrue($float->isOdd());
        $this->assertFalse($float->isEven());
    }

    public function testCanCastToString()
    {
        $float = new FloatObject(1.1);
        $this->assertEquals('1.1', $float->toString());
        $this->assertEquals('1.1', $float->toStringObject()->getScalarValue());
    }

    public function testCanCastToInt()
    {
        $float = new FloatObject(1.0);
        $this->assertEquals(1, $float->toInt());
        $this->assertEquals(1, $float->toIntObject()->getScalarValue());
    }

    public function testBoxBreak()
    {
        $float = new FloatObject(0.0);
        FloatObject::box($float);
        $this->expectException(TypeError::class);
        $float = false;
    }

    public function testMultiply()
    {
        $this->assertEquals(new FloatObject(38.4), (new FloatObject(6.4))->multipliedBy(new IntObject(6)));
        $this->assertEquals(
            (new FloatObject(38.1, 2))->getScalarValue(),
            (new FloatObject(6.35))->multipliedBy(6)->getScalarValue()
        );
        $this->assertEquals(34.036, (new FloatObject(5.36, 3))->multipliedBy(new FloatObject(6.35))->getScalarValue());
        $this->assertEquals(34.036, (new FloatObject(5.36, 3))->multipliedBy(6.35)->getScalarValue());
    }

    public function testAdd()
    {
        $this->assertEquals(1.1, (new FloatObject(0.7))->plus(new FloatObject(0.4))->getScalarValue());
        $this->assertEquals(1.1, (new FloatObject(0.7))->plus(0.4)->getScalarValue());
    }

    public function testSubtract()
    {
        $this->assertEquals(new FloatObject(9.6), (new FloatObject(10, 1))->minus(new FloatObject(0.4)));
        $this->assertEquals(new FloatObject(9.6), (new FloatObject(10.0, 1))->minus(0.4));
        $this->assertEquals(12.6, (new FloatObject(17.6))->minus(new IntObject(5))->getScalarValue());
        $this->assertEquals(12.6, (new FloatObject(17.6))->minus(5)->getScalarValue());
    }

    public function testDivideBy()
    {
        $this->assertEquals(229.78, (new FloatObject(459.56))->dividedBy(new IntObject(2))->getScalarValue());
        $this->assertEquals(229.78, (new FloatObject(919.12))->dividedBy(4)->getScalarValue());
        $this->assertEquals(8.842, (new FloatObject(66.32, 3))->dividedBy(7.5)->getScalarValue());
    }

    public function testAbsolute()
    {
        $this->assertEquals(30.39, (new FloatObject(30.39))->absolute()->getScalarValue());
        $this->assertEquals(30.39, (new FloatObject(-30.39))->absolute()->getScalarValue());
    }

    public function testCompare()
    {
        $this->assertEquals(new FloatObject(1), (new FloatObject(5))->compare(4));
        $this->assertEquals(new FloatObject(1), (new FloatObject(6))->compare(3));
        $this->assertEquals(new FloatObject(1.0, 1), (new FloatObject(0.7, 1))->compare(0.3));
    }

    public function testModulo()
    {
        $this->assertEquals(new FloatObject(5.5), (new FloatObject(5.5))->modulo(10));
        $this->assertEquals(new FloatObject(0), (new FloatObject(10.0))->modulo(2.0));
    }

    public function testPower()
    {
        $this->assertEquals(
            (new FloatObject(31622776.60))->getScalarValue(),
            (new FloatObject(10, 2))->power(7.5)->getScalarValue()
        );
        $this->assertEquals(new FloatObject(766217865.41), (new FloatObject(5.5, 2))->power(12));
        $this->assertEquals(new FloatObject(766217865.4), (new FloatObject(5.5))->power(12));
    }

    public function testSquareRoot()
    {
        $this->assertEquals(new FloatObject(3), (new FloatObject(9))->squareRoot());
        $this->assertEquals(new FloatObject(7.03), (new FloatObject(49.39))->squareRoot());
        $this->assertEquals(new FloatObject(7.02780193233), (new FloatObject(49.39, 11))->squareRoot());
    }

    public function testNegate()
    {
        $this->assertEquals(new FloatObject(-49.39), (new FloatObject(49.39))->negate());
        $this->assertEquals(new FloatObject(49.39), (new FloatObject(-49.39))->negate());
    }

    public function testFactorial()
    {
        $this->assertEquals(new FloatObject(3628800), (new FloatObject(10))->factorial());
        $this->assertEquals(new FloatObject(3628800), (new FloatObject(10.0))->factorial());
    }

    public function testGcd()
    {
        $this->assertEquals(new FloatObject(10), (new FloatObject(10))->gcd(50));
        $this->assertEquals(new FloatObject(10), (new FloatObject(50))->gcd(10));
    }

    public function testRoot()
    {
        $this->assertEquals(new FloatObject(3), (new FloatObject(32))->root(3));
    }

    public function testNextPrime()
    {
        $this->assertEquals(new FloatObject(7), (new FloatObject(5))->getNextPrime());
        $this->assertEquals(new FloatObject(5), (new FloatObject(4.5))->getNextPrime());
    }

    public function testIsPrime()
    {
        $this->assertTrue((new FloatObject(5))->isPrime());
        $this->assertFalse((new FloatObject(4.5))->isPrime());
        $this->assertFalse((new FloatObject(5.5))->isPrime());
    }

    public function testIsPerfectSquare()
    {
        $this->assertTrue((new FloatObject(4))->isPerfectSquare());
        $this->assertFalse((new FloatObject(4.5))->isPerfectSquare());
    }

    public function testType()
    {
        $this->assertTrue(is_float((new FloatObject(10.0))->getScalarValue()));
    }

    public function testValue()
    {
        $this->assertEquals(5.5, (new FloatObject(5.5))->getScalarValue());
    }

    public function testFrom()
    {
        $this->assertEquals(9.49, (FloatObject::fromPrimitive(9.49))->getScalarValue());
        $this->assertEquals(7, (FloatObject::fromPrimitive(7))->getScalarValue());
        $this->assertEquals(1239.369, (FloatObject::fromPrimitive('1239.369'))->getScalarValue());
    }

    public function testBadFromNull()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive(null);
    }

    public function testBadFromObject()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive(new stdClass());
    }

    public function testBadFromResource()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive(tmpfile());
    }

    public function testBadFromBoolean()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive(false);
    }

    public function testBadFromArray()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive([]);
    }

    public function testBadFromString()
    {
        $this->expectException(TypeError::class);
        FloatObject::fromPrimitive('Foo');
    }

    public function testPrecision()
    {
        $this->assertEquals(new FloatObject(float: 1.00, precision: 2), (new FloatObject(float: 1, precision: 2)));
    }
}
