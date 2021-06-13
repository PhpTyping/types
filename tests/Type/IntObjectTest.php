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
use RuntimeException;
use stdClass;
use TypeError;
use Typing\Exception\InvalidTypeCastException;
use Typing\Type\FloatObject;
use Typing\Type\IntObject;
use Typing\Type\StringObject;

/**
 * Class IntObjectTest.
 */
class IntObjectTest extends TestCase
{
    public function testBox()
    {
        $int = new IntObject(1);
        IntObject::box($int);
        $this->assertTrue(($int instanceof IntObject));
        $this->assertEquals(1.0, $int->getScalarValue());

        /** @var IntObject $int */
        $int = 493;
        $this->assertTrue(($int instanceof IntObject));
        $this->assertEquals(493, $int->getScalarValue());
    }

    public function testBoxBreak()
    {
        $int = new IntObject(1);
        IntObject::box($int);
        $this->expectException(TypeError::class);
        $int = false;
    }

    public function testIsEven()
    {
        $float = new IntObject(2);
        $this->assertTrue($float->isEven());
        $this->assertFalse($float->isOdd());
    }

    public function testIsOdd()
    {
        $float = new IntObject(1);
        $this->assertTrue($float->isOdd());
        $this->assertFalse($float->isEven());
    }

    public function testMultiply()
    {
        $this->assertEquals(new IntObject(15), (new IntObject(3))->multipliedBy(new IntObject(5)));
        $this->assertEquals(new IntObject(15), (new IntObject(3))->multipliedBy(5));
        $this->assertEquals(new IntObject(30), (new IntObject(2))->multipliedBy(new IntObject(15)));
        $this->assertEquals(new IntObject(30), (new IntObject(2))->multipliedBy(15));
    }

    public function testAdd()
    {
        $this->assertEquals(new IntObject(15), (new IntObject(10))->plus(new IntObject(5)));
        $this->assertEquals(new IntObject(15), (new IntObject(10))->plus(5));
    }

    public function testSubtract()
    {
        $this->assertEquals(new IntObject(10), (new IntObject(20))->minus(new IntObject(10)));
        $this->assertEquals(new IntObject(10), (new IntObject(20))->minus(new FloatObject(10.4))); //Rounds up
        $this->assertEquals(new IntObject(9), (new IntObject(20))->minus(new FloatObject(10.6))); //Rounds down
        $this->assertEquals(new FloatObject(10), (new IntObject(20))->toFloatObject()->minus(new FloatObject(10.4)));
        $this->assertEquals(new FloatObject(9), (new IntObject(20))->toFloatObject()->minus(new FloatObject(10.6)));
        $this->assertEquals(new IntObject(10), (new IntObject(20))->minus(10));
    }

    public function testDivideBy()
    {
        $this->assertEquals(new IntObject(15), (new IntObject(30))->dividedBy(new IntObject(2)));
        $this->assertEquals(new IntObject(15), (new IntObject(30))->dividedBy(2));
    }

    public function testAbsolute()
    {
        $this->assertEquals(new IntObject(30), (new IntObject(30))->absolute());
        $this->assertEquals(new IntObject(30), (new IntObject(-30))->absolute());
    }

    public function testCompare()
    {
        $this->assertEquals(new IntObject(1), (new IntObject(5))->compare(4));
        $this->assertEquals(new IntObject(1), (new IntObject(6))->compare(3));
        $this->assertEquals(new IntObject(1), (new IntObject(1000))->compare(999));
    }

    public function testModulo()
    {
        $this->assertEquals(new IntObject(5), (new IntObject(5))->modulo(10));
    }

    public function testPower()
    {
        $this->assertEquals(new IntObject(244140625), (new IntObject(5))->power(12));
    }

    public function testSquareRoot()
    {
        $this->assertEquals(new IntObject(3), (new IntObject(9))->squareRoot());
        $this->assertEquals(new IntObject(7), (new IntObject(49))->squareRoot());
    }

    public function testNegate()
    {
        $this->assertEquals(new IntObject(-49), (new IntObject(49))->negate());
        $this->assertEquals(new IntObject(49), (new IntObject(-49))->negate());
    }

    public function testFactorial()
    {
        $this->assertEquals(new IntObject(3628800), (new IntObject(10))->factorial());
    }

    public function testGcd()
    {
        $this->assertEquals(new IntObject(10), (new IntObject(10))->gcd(50));
    }

    public function testRoot()
    {
        $this->assertEquals(new IntObject(3), (new IntObject(32))->root(3));
    }

    public function testNextPrime()
    {
        $this->assertEquals(new IntObject(7), (new IntObject(5))->getNextPrime());
    }

    public function testIsPrime()
    {
        $this->assertTrue((new IntObject(5))->isPrime());
        $this->assertFalse((new IntObject(10))->isPrime());
    }

    public function testIsPerfectSquare()
    {
        $this->assertTrue((new IntObject(4))->isPerfectSquare());
        $this->assertFalse((new IntObject(5))->isPerfectSquare());
    }

    public function testType()
    {
        $this->assertEquals('integer', gettype((new IntObject(10))->getScalarValue()));
    }

    public function testValue()
    {
        $this->assertEquals(5, (new IntObject(5))->getScalarValue());
    }

    public function testFromPrimitive()
    {
        $this->assertEquals(10, (IntObject::fromPrimitive(10))->getScalarValue());
        $this->assertEquals(9, (IntObject::fromPrimitive(9.2345))->getScalarValue());
        $this->assertEquals(1239, (IntObject::fromPrimitive('1239'))->getScalarValue());
        $this->assertEquals(1240, (IntObject::fromPrimitive('1239.9'))->getScalarValue());
    }

    public function testBadFromNull()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive(null);
    }

    public function testBadFromObject()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive(new stdClass());
    }

    public function testBadFromResource()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive(tmpfile());
    }

    public function testBadFromBoolean()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive(false);
    }

    public function testBadFromArray()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive([]);
    }

    public function testBadFromString()
    {
        $this->expectException(TypeError::class);
        IntObject::fromPrimitive('Foo');
    }

    public function testBadBigInt()
    {
        $this->expectException(RuntimeException::class);
        IntObject::fromPrimitive(PHP_INT_MAX + 1);
    }

    public function testCanCastToBool()
    {
        $this->assertFalse((new IntObject(0))->toBooleanObject()->isTrue());
        $this->assertFalse((new IntObject(0))->toBool());
        $this->assertTrue((new IntObject(1))->toBool());
    }

    public function testCastToBoolFailsWhenInvalid()
    {
        $this->expectException(InvalidTypeCastException::class);
        (new IntObject(5))->toBool();
    }

    public function testCanCastToString()
    {
        $this->assertEquals(new StringObject(1), (new IntObject(1))->toStringObject());
        $this->assertEquals('1', (new IntObject(1))->toString());
    }

    public function testCanCastToFloat()
    {
        $this->assertEquals(new FloatObject(1.0), (new IntObject(1))->toFloatObject());
        $this->assertEquals(1.0, (new IntObject(1))->toFloat());
    }
}
