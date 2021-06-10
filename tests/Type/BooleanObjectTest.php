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
use Typing\Exception\InvalidTransformationException;
use Typing\Type\BooleanObject;

/**
 * Class BooleanObjectTest.
 */
class BooleanObjectTest extends TestCase
{
    public function testCanMutateTo()
    {
        $bool = BooleanObject::fromPrimitive(true);
        $this->assertEquals('true', $bool);
        $this->assertEquals('true', $bool->toString());
        $this->assertEquals(1, $bool->toInt());
        $this->assertTrue($bool->isTrue());
        $this->assertFalse($bool->isFalse());
        $this->assertTrue($bool->getScalarValue());
    }

    /**
     * @dataProvider getBadLoadData
     *
     * @param mixed $primitive
     */
    public function testCanNotLoadFrom(mixed $primitive)
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches(
            '/Argument \#([0-9]+) (.*) must be of type (.*), (.*) given.*$/'
        );
        BooleanObject::fromPrimitive($primitive);
    }

    /**
     * @dataProvider getLoadData
     *
     * @param bool|string $expected
     * @param mixed       $primitive
     */
    public function testCanLoadFrom(bool | string $expected, mixed $primitive)
    {
        $this->assertEquals(new BooleanObject($expected), BooleanObject::fromPrimitive($primitive));
    }

    public function testBox()
    {
        $bool = new BooleanObject(false);
        BooleanObject::box($bool);
        $this->assertTrue(($bool instanceof BooleanObject));
        $this->assertTrue($bool->isFalse());

        /** @var BooleanObject $bool */
        $bool = true;
        $this->assertTrue(($bool instanceof BooleanObject));
        $this->assertTrue($bool->isTrue());

        $bool = false;
        $this->assertTrue(($bool instanceof BooleanObject));
        $this->assertTrue($bool->isFalse());
    }

    public function testFailsWhenBoxedAndInvalidValue()
    {
        $bool = new BooleanObject(true);
        BooleanObject::box($bool);
        $this->expectException(TypeError::class);
        $bool = 5;
    }

    public function testFailsWhenInvalidString()
    {
        $this->expectException(InvalidTransformationException::class);
        new BooleanObject('foo');
    }

    /**
     * @return array
     */
    public function getLoadData(): array
    {
        return [
            [false, false],
            [false, 'false'],
            [true, 'true'],
            ['true', true],
        ];
    }

    /**
     * @return array
     */
    public function getBadLoadData(): array
    {
        return [
            [null],
            [new stdClass()],
            [3.559],
            [3E-5],
            [[]],
            [tmpfile()],
        ];
    }
}
