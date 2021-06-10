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

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use Typing\Type\DateTime;
use Typing\Type\StringObject;

/**
 * Class DateTimeTest.
 */
class DateTimeTest extends TestCase
{
    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * Setup DateTime.
     */
    protected function setUp(): void
    {
        $this->dateTime = new DateTime();
    }

    public function testType()
    {
        $this->assertInstanceOf('Carbon\\Carbon', $this->dateTime);
        $this->assertInstanceOf('\\DateTime', $this->dateTime);
    }

    public function testBox()
    {
        $date = new DateTime();
        DateTime::box($date);
        $this->assertTrue(($date instanceof DateTime));

        /* @var DateTime $date */
        $date = '2016-01-01';
        $date->setTimezone(new DateTimeZone('UTC'));
        $this->assertTrue(($date instanceof DateTime));

        $date = new DateTime('2016-05-10');
        $this->assertTrue(($date instanceof DateTime));

        $date = '2016-01-30 00:00:00';
        $this->assertEquals(new StringObject('2016-01-30 00:00:00'), $date->toStringObject());
        $date = '2016-07-30 10:05:43';
        $this->assertEquals('2016-07-30 10:05:43', $date->toString());
    }

    public function testBoxBreakType()
    {
        $date = new DateTime();
        DateTime::box($date);
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform integer to DateTime');
        $date = 1;
    }

    public function testFromStringPrimitiveOrStringObject()
    {
        $this->assertEquals(new DateTime('2016-01-01'), DateTime::fromPrimitive(new StringObject('2016-01-01')));
        $this->assertEquals(new DateTime('2016-01-01'), DateTime::fromPrimitive('2016-01-01'));
    }

    public function testBadFromNull()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform null to DateTime.');
        DateTime::fromPrimitive(null);
    }

    public function testBadFromPrimitiveObject()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform object to DateTime.');
        DateTime::fromPrimitive(new stdClass());
    }

    public function testBadFromPrimitiveResource()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform resource to DateTime.');
        DateTime::fromPrimitive(tmpfile());
    }

    public function testBadFromPrimitiveBoolean()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform boolean to DateTime.');
        DateTime::fromPrimitive(false);
    }

    public function testBadFromPrimitiveArray()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Could not transform array to DateTime.');
        DateTime::fromPrimitive([]);
    }
}
