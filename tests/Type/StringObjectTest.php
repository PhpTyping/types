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

use Carbon\Exceptions\InvalidFormatException;
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use Typing\Type\BooleanObject;
use Typing\Type\Collection;
use Typing\Type\DateTime;
use Typing\Type\FloatObject;
use Typing\Type\IntObject;
use Typing\Type\StringObject;

/**
 * Class StringObjectTest.
 */
class StringObjectTest extends TestCase
{
    public const STRING_SINGULAR = 'syllabus';
    public const STRING_PLURAL = 'syllabi';
    public const LOREM_IPSUM =
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt. Ipsum.';

    public function testSubStrUntil()
    {
        $this->assertEquals(
            'Lorem ipsum dolor sit amet',
            (string) StringObject::create(self::LOREM_IPSUM)->subStrUntil(',')
        );
        $this->assertEquals(
            'Lorem ipsum dolor sit amet,',
            (string) StringObject::create(self::LOREM_IPSUM)->subStrUntil(',', true)
        );
    }

    public function testSubStrAfter()
    {
        $this->assertEquals(
            'sed do eiusmod tempor incididunt. Ipsum.',
            (string) StringObject::create(self::LOREM_IPSUM)->subStrAfter('elit, ')
        );
        $this->assertEquals(
            'elit, sed do eiusmod tempor incididunt. Ipsum.',
            (string) StringObject::create(self::LOREM_IPSUM)->subStrAfter('elit, ', true)
        );
    }

    public function testCanCastToArray()
    {
        $string = new StringObject('foo');
        $this->assertEquals(['f', 'o', 'o'], $string->toArray());
        $expected = ['b', 'a', 'z', ' ', 'q', 'u', 'x', ' ', 'p', 'i', 'e'];
        $this->assertEquals(new Collection($expected), StringObject::create('baz qux pie')->toCollection());
    }

    public function testCanCastToInt()
    {
        $string = new StringObject('foo');
        $this->assertEquals(3, $string->toInt());
        $this->assertEquals(new IntObject(1), StringObject::create('1')->toIntObject());
        // Returns the count, not the value as an int. Otherwise most ints would just be 1.
        $this->assertEquals(new IntObject(1), StringObject::create('0')->toIntObject());
    }

    public function testCanCastToBool()
    {
        $this->assertEquals(new BooleanObject(false), StringObject::create('off')->toBooleanObject());
        $this->assertEquals(new BooleanObject(false), StringObject::create('false')->toBooleanObject());
        $this->assertEquals(new BooleanObject(false), StringObject::create('no')->toBooleanObject());
        $this->assertEquals(new BooleanObject(true), StringObject::create('on')->toBooleanObject());
        $this->assertEquals(new BooleanObject(true), StringObject::create('true')->toBooleanObject());
        $this->assertEquals(new BooleanObject(true), StringObject::create('yes')->toBooleanObject());
        $this->assertEquals(true, StringObject::create('true')->toBool());
    }

    public function testCanCastToDateTime()
    {
        $this->assertEquals(new DateTime('2016-01-01'), StringObject::create('2016-01-01')->toDateTime());
    }

    public function testCastToDateTimeFailsOnInvalid()
    {
        $this->expectException(InvalidFormatException::class);
        StringObject::create('not-a-date')->toDateTime();
    }

    public function testSingular()
    {
        $this->assertEquals(self::STRING_SINGULAR, StringObject::create(self::STRING_PLURAL)->singularize());
    }

    public function testPluralize()
    {
        $this->assertEquals(self::STRING_PLURAL, StringObject::create(self::STRING_SINGULAR)->pluralize());
    }

    public function testStrpos()
    {
        $this->assertEquals(6, StringObject::create(self::LOREM_IPSUM)->strpos('ipsum'));
        $this->assertEquals(91, StringObject::create(self::LOREM_IPSUM)->strpos('ipsum', 12));
        $this->assertEquals(91, StringObject::create(self::LOREM_IPSUM)->strpos('Ipsum', 0, true));
    }

    public function testStrrpos()
    {
        $this->assertEquals(91, StringObject::create(self::LOREM_IPSUM)->strrpos('Ipsum'));
        $this->assertEquals(91, StringObject::create(self::LOREM_IPSUM)->strrpos('ipsum', 46));
        $this->assertEquals(6, StringObject::create(self::LOREM_IPSUM)->strrpos('ipsum', 0, true));
    }

    public function testIsSemVer()
    {
        $this->assertTrue(StringObject::create('1.0.4')->isSemVer());
        $this->assertTrue(StringObject::create('0.0.1')->isSemVer());
        $this->assertTrue(StringObject::create('1.0.5.1')->isSemVer());
        $this->assertTrue(StringObject::create('1.0')->isSemVer());
        $this->assertTrue(StringObject::create('105')->isSemVer());
        $this->assertFalse(StringObject::create('not-sem-ver')->isSemVer());
        $this->assertFalse(StringObject::create('my-version-1.0.1-foo')->isSemVer());
    }

    public function testExplode()
    {
        $this->assertEquals(
            new Collection(['this', 'is', 'my', 'list'], StringObject::class),
            StringObject::create('this, is, my, list')->explode(',')
        );

        $this->assertEquals(
            new Collection(['this', 'is', 'my', 'list'], StringObject::class),
            StringObject::create('  this, is, my  , list  '.PHP_EOL)->explode(', ')
        );

        $this->assertEquals(
            new Collection(['  this', 'is', 'my  ', 'list  '.PHP_EOL], StringObject::class),
            StringObject::create('  this, is, my  , list  '.PHP_EOL)->explode(', ', PHP_INT_MAX, false)
        );
    }

    public function testFrom()
    {
        $this->assertEquals('false', StringObject::fromPrimitive(false));
        $this->assertEquals('99', StringObject::fromPrimitive(99));
        $this->assertEquals('1.49', StringObject::fromPrimitive(1.49));
        $this->assertEquals('3E-5', StringObject::fromPrimitive(3E-5));
        $this->assertEquals('bar', StringObject::fromPrimitive('bar'));
        $this->assertEquals('1, 2, 3, 4', StringObject::fromPrimitive([1, 2, 3, 4]));
        $this->assertEquals('stream', StringObject::fromPrimitive(tmpfile()));
        $this->assertEquals(StringObject::create('5'), StringObject::fromPrimitive(new IntObject(5)));
        $this->assertEquals(StringObject::create('false'), StringObject::fromPrimitive(new BooleanObject(false)));
        $this->assertEquals(StringObject::create('11.36'), StringObject::fromPrimitive(new FloatObject(11.36)));
        $this->assertEquals(new StringObject('foo, bar'), StringObject::fromPrimitive(new Collection(['foo', 'bar'])));
        $this->assertEquals('foo', StringObject::fromPrimitive(new class() {
            public function __toString(): string
            {
                return 'foo';
            }
        }));
    }

    public function testBadFromObject()
    {
        $this->expectException(TypeError::class);
        StringObject::fromPrimitive(new stdClass());
    }

    public function testBadFromNull()
    {
        $this->expectException(TypeError::class);
        StringObject::fromPrimitive(null);
    }

    /**
     * Asserts that a variable is of a Stringy instance.
     *
     * @param mixed $actual
     */
    public function assertStringType(mixed $actual)
    {
        $this->assertInstanceOf(StringObject::class, $actual);
    }

    public function testConstruct()
    {
        $stringy = new StringObject('foo bar', 'UTF-8');
        $this->assertStringType($stringy);
        $this->assertEquals('foo bar', (string) $stringy);
        $this->assertEquals('UTF-8', $stringy->getEncoding());
    }

    public function testEmptyConstruct()
    {
        $stringy = new StringObject('');
        $this->assertStringType($stringy);
        $this->assertEquals('', (string) $stringy);
    }

    /**
     * @dataProvider toStringProvider()
     *
     * @param $expected
     * @param $str
     */
    public function testToString($expected, $str)
    {
        $this->assertEquals($expected, (string) new StringObject($str));
    }

    /**
     * @return array
     */
    public function toStringProvider(): array
    {
        return [
            ['', null],
            ['', false],
            ['1', true],
            ['-9', -9],
            ['1.18', 1.18],
            [' string  ', ' string  '],
        ];
    }

    public function testCreate()
    {
        $stringy = StringObject::create('foo bar', 'UTF-8');
        $this->assertStringType($stringy);
        $this->assertEquals('foo bar', (string) $stringy);
        $this->assertEquals('UTF-8', $stringy->getEncoding());
    }

    public function testChaining()
    {
        $stringy = StringObject::create('F????     B????', 'UTF-8');
        $this->assertStringType($stringy);
        $result = $stringy->collapseWhitespace()->swapCase()->upperCaseFirst();
        $this->assertEquals('F???? b????', $result);
    }

    public function testCount()
    {
        $stringy = StringObject::create('F????', 'UTF-8');
        $this->assertEquals(3, $stringy->count());
        $this->assertCount(3, $stringy);
    }

    public function testGetIterator()
    {
        $stringy = StringObject::create('F???? B????', 'UTF-8');

        $valResult = [];
        foreach ($stringy as $char) {
            $valResult[] = $char;
        }

        $keyValResult = [];
        foreach ($stringy as $pos => $char) {
            $keyValResult[$pos] = $char;
        }

        $this->assertEquals(['F', '??', '??', ' ', 'B', '??', '??'], $valResult);
        $this->assertEquals(['F', '??', '??', ' ', 'B', '??', '??'], $keyValResult);
    }

    /**
     * @dataProvider offsetExistsProvider()
     *
     * @param bool $expected
     * @param int  $offset
     */
    public function testOffsetExists(bool $expected, int $offset)
    {
        $stringy = StringObject::create('f????', 'UTF-8');
        $this->assertEquals($expected, $stringy->offsetExists($offset));
        $this->assertEquals($expected, isset($stringy[$offset]));
    }

    public function offsetExistsProvider(): array
    {
        return [
            [true, 0],
            [true, 2],
            [false, 3],
            [true, -1],
            [true, -3],
            [false, -4],
        ];
    }

    public function testOffsetGet()
    {
        $stringy = StringObject::create('f????', 'UTF-8');

        $this->assertEquals('f', $stringy->offsetGet(0));
        $this->assertEquals('??', $stringy->offsetGet(2));

        $this->assertEquals('??', $stringy[2]);
    }

    public function testOffsetGetOutOfBounds()
    {
        $this->expectException(OutOfBoundsException::class);
        $stringy = StringObject::create('f????', 'UTF-8');
        $stringy[3];
    }

    public function testOffsetSet()
    {
        $this->expectException(Exception::class);
        $stringy = StringObject::create('f????', 'UTF-8');
        $stringy[1] = 'invalid';
    }

    public function testOffsetUnset()
    {
        $this->expectException(Exception::class);
        $stringy = StringObject::create('f????', 'UTF-8');
        unset($stringy[1]);
    }

    /**
     * @dataProvider indexOfProvider()
     *
     * @param bool|int    $expected
     * @param string      $str
     * @param string      $subStr
     * @param int|null    $offset
     * @param string|null $encoding
     */
    public function testIndexOf(
        bool | int $expected,
        string $str,
        string $subStr,
        ?int $offset = 0,
        ?string $encoding = null
    ) {
        $result = StringObject::create($str, $encoding)->indexOf($subStr, $offset);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function indexOfProvider(): array
    {
        return [
            [6, 'foo & bar', 'bar'],
            [6, 'foo & bar', 'bar', 0],
            [false, 'foo & bar', 'baz'],
            [false, 'foo & bar', 'baz', 0],
            [0, 'foo & bar & foo', 'foo', 0],
            [12, 'foo & bar & foo', 'foo', 5],
            [6, 'f???? & b????', 'b????', 0, 'UTF-8'],
            [false, 'f???? & b????', 'baz', 0, 'UTF-8'],
            [0, 'f???? & b???? & f????', 'f????', 0, 'UTF-8'],
            [12, 'f???? & b???? & f????', 'f????', 5, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider indexOfLastProvider()
     *
     * @param mixed       $expected
     * @param string      $str
     * @param string      $subStr
     * @param int|null    $offset
     * @param string|null $encoding
     */
    public function testIndexOfLast(
        mixed $expected,
        string $str,
        string $subStr,
        ?int $offset = 0,
        ?string $encoding = null
    ): void {
        $result = StringObject::create($str, $encoding)->indexOfLast($subStr, $offset);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function indexOfLastProvider(): array
    {
        return [
            [6, 'foo & bar', 'bar'],
            [6, 'foo & bar', 'bar', 0],
            [false, 'foo & bar', 'baz'],
            [false, 'foo & bar', 'baz', 0],
            [12, 'foo & bar & foo', 'foo', 0],
            [0, 'foo & bar & foo', 'foo', -5],
            [6, 'f???? & b????', 'b????', 0, 'UTF-8'],
            [false, 'f???? & b????', 'baz', 0, 'UTF-8'],
            [12, 'f???? & b???? & f????', 'f????', 0, 'UTF-8'],
            [0, 'f???? & b???? & f????', 'f????', -5, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider appendProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $string
     * @param string|null $encoding
     */
    public function testAppend(string $expected, string $str, string $string, ?string $encoding = null)
    {
        $result = StringObject::create($str, $encoding)->append($string);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function appendProvider(): array
    {
        return [
            ['foobar', 'foo', 'bar'],
            ['f????b????', 'f????', 'b????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider prependProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $string
     * @param string|null $encoding
     */
    public function testPrepend(string $expected, string $str, string $string, ?string $encoding = null)
    {
        $result = StringObject::create($str, $encoding)->prepend($string);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function prependProvider(): array
    {
        return [
            ['foobar', 'bar', 'foo'],
            ['f????b????', 'b????', 'f????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider charsProvider()
     *
     * @param array       $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testChars(array $expected, string $str, ?string $encoding = null)
    {
        $result = StringObject::create($str, $encoding)->chars();
        $this->assertTrue(is_array($result));
        foreach ($result as $char) {
            $this->assertTrue(is_string($char));
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function charsProvider(): array
    {
        return [
            [[], ''],
            [['T', 'e', 's', 't'], 'Test'],
            [['F', '??', '??', ' ', 'B', '??', '??'], 'F???? B????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider linesProvider()
     *
     * @param array       $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testLines(array $expected, string $str, ?string $encoding = null)
    {
        $result = StringObject::create($str, $encoding)->lines();

        $this->assertTrue(is_array($result));
        foreach ($result as $line) {
            $this->assertStringType($line);
        }

        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertEquals($expected[$i], $result[$i]);
        }
    }

    /**
     * @return array[]
     */
    public function linesProvider(): array
    {
        return [
            [[], ''],
            [['', ''], "\r\n"],
            [['foo', 'bar'], "foo\nbar"],
            [['foo', 'bar'], "foo\rbar"],
            [['foo', 'bar'], "foo\r\nbar"],
            [['foo', '', 'bar'], "foo\r\n\r\nbar"],
            [['foo', 'bar', ''], "foo\r\nbar\r\n"],
            [['', 'foo', 'bar'], "\r\nfoo\r\nbar"],
            [['f????', 'b????'], "f????\nb????", 'UTF-8'],
            [['f????', 'b????'], "f????\rb????", 'UTF-8'],
            [['f????', 'b????'], "f????\n\rb????", 'UTF-8'],
            [['f????', 'b????'], "f????\r\nb????", 'UTF-8'],
            [['f????', '', 'b????'], "f????\r\n\r\nb????", 'UTF-8'],
            [['f????', 'b????', ''], "f????\r\nb????\r\n", 'UTF-8'],
            [['', 'f????', 'b????'], "\r\nf????\r\nb????", 'UTF-8'],
        ];
    }

    /**
     * @dataProvider upperCaseFirstProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testUpperCaseFirst(string $expected, string $str, ?string $encoding = null)
    {
        $result = StringObject::create($str, $encoding)->upperCaseFirst();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function upperCaseFirstProvider(): array
    {
        return [
            ['Test', 'Test'],
            ['Test', 'test'],
            ['1a', '1a'],
            ['?? test', '?? test', 'UTF-8'],
            [' ?? test', ' ?? test', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider lowerCaseFirstProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testLowerCaseFirst(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->lowerCaseFirst();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return string[][]
     */
    public function lowerCaseFirstProvider(): array
    {
        return [
            ['test', 'Test'],
            ['test', 'test'],
            ['1a', '1a'],
            ['?? test', '?? test', 'UTF-8'],
            [' ?? test', ' ?? test', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider camelizeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testCamelize(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->camelize();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function camelizeProvider(): array
    {
        return [
            ['camelCase', 'CamelCase'],
            ['camelCase', 'Camel-Case'],
            ['camelCase', 'camel case'],
            ['camelCase', 'camel -case'],
            ['camelCase', 'camel - case'],
            ['camelCase', 'camel_case'],
            ['camelCTest', 'camel c test'],
            ['stringWith1Number', 'string_with1number'],
            ['stringWith22Numbers', 'string-with-2-2 numbers'],
            ['dataRate', 'data_rate'],
            ['backgroundColor', 'background-color'],
            ['yesWeCan', 'yes_we_can'],
            ['mozSomething', '-moz-something'],
            ['carSpeed', '_car_speed_'],
            ['serveHTTP', 'ServeHTTP'],
            ['1Camel2Case', '1camel2case'],
            ['????????????Case', '???????????? case', 'UTF-8'],
            ['??amelCase', '??amel  Case', 'UTF-8'],
//            ['camel??ase', 'camel ??ase', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider upperCamelizeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testUpperCamelize(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->upperCamelize();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function upperCamelizeProvider(): array
    {
        return [
            ['CamelCase', 'camelCase'],
            ['CamelCase', 'Camel-Case'],
            ['CamelCase', 'camel case'],
            ['CamelCase', 'camel -case'],
            ['CamelCase', 'camel - case'],
            ['CamelCase', 'camel_case'],
            ['CamelCTest', 'camel c test'],
            ['StringWith1Number', 'string_with1number'],
            ['StringWith22Numbers', 'string-with-2-2 numbers'],
            ['1Camel2Case', '1camel2case'],
        ];
    }

    /**
     * @dataProvider dasherizeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testDasherize(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->dasherize();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function dasherizeProvider(): array
    {
        return [
            ['test-case', 'testCase'],
            ['test-case', 'Test-Case'],
            ['test-case', 'test case'],
            ['-test-case', '-test -case'],
            ['test-case', 'test - case'],
            ['test-case', 'test_case'],
            ['test-c-test', 'test c test'],
            ['test-d-case', 'TestDCase'],
            ['test-c-c-test', 'TestCCTest'],
            ['string-with1number', 'string_with1number'],
            ['string-with-2-2-numbers', 'String-with_2_2 numbers'],
            ['1test2case', '1test2case'],
            ['data-rate', 'dataRate'],
            ['car-speed', 'CarSpeed'],
            ['yes-we-can', 'yesWeCan'],
            ['background-color', 'backgroundColor'],
            ['dash-??ase', 'dash ??ase', 'UTF-8'],
            ['????????????-case', '???????????? case', 'UTF-8'],
            ['??ash-case', '??ash  Case', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider underscoredProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testUnderscored(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->underscored();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function underscoredProvider(): array
    {
        return [
            ['test_case', 'testCase'],
            ['test_case', 'Test-Case'],
            ['test_case', 'test case'],
            ['test_case', 'test -case'],
            ['_test_case', '-test - case'],
            ['test_case', 'test_case'],
            ['test_c_test', '  test c test'],
            ['test_u_case', 'TestUCase'],
            ['test_c_c_test', 'TestCCTest'],
            ['string_with1number', 'string_with1number'],
            ['string_with_2_2_numbers', 'String-with_2_2 numbers'],
            ['1test2case', '1test2case'],
            ['yes_we_can', 'yesWeCan'],
            ['test_??ase', 'test ??ase', 'UTF-8'],
            ['????????????_case', '???????????? case', 'UTF-8'],
            ['??ash_case', '??ash  Case', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider delimitProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $delimiter
     * @param string|null $encoding
     */
    public function testDelimit(string $expected, string $str, string $delimiter, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->delimit($delimiter);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function delimitProvider(): array
    {
        return [
            ['test*case', 'testCase', '*'],
            ['test&case', 'Test-Case', '&'],
            ['test#case', 'test case', '#'],
            ['test**case', 'test -case', '**'],
            ['~!~test~!~case', '-test - case', '~!~'],
            ['test*case', 'test_case', '*'],
            ['test%c%test', '  test c test', '%'],
            ['test+u+case', 'TestUCase', '+'],
            ['test=c=c=test', 'TestCCTest', '='],
            ['string#>with1number', 'string_with1number', '#>'],
            ['1test2case', '1test2case', '*'],
            ['test ???? ??ase', 'test ??ase', ' ???? ', 'UTF-8'],
            ['??????????????case', '???????????? case', '??', 'UTF-8'],
            ['??ash??case', '??ash  Case', '??', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider swapCaseProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testSwapCase(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->swapCase();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function swapCaseProvider(): array
    {
        return [
            ['TESTcASE', 'testCase'],
            ['tEST-cASE', 'Test-Case'],
            [' - ??ASH  cASE', ' - ??ash  Case', 'UTF-8'],
            ['????????????', '????????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider titleizeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param array|null  $ignore
     * @param string|null $encoding
     */
    public function testTitleize(string $expected, string $str, ?array $ignore = null, string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->titleize($ignore);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function titleizeProvider(): array
    {
        $ignore = ['at', 'by', 'for', 'in', 'of', 'on', 'out', 'to', 'the'];

        return [
            ['Title Case', 'TITLE CASE'],
            ['Testing The Method', 'testing the method'],
            ['Testing the Method', 'testing the method', $ignore],
            ['I Like to Watch Dvds at Home', 'i like to watch DVDs at home',
                $ignore, ],
            ['???? ?????????? ???? ??????????', '  ???? ?????????? ???? ??????????  ', null, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider humanizeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testHumanize(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->humanize();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function humanizeProvider(): array
    {
        return [
            ['Author', 'author_id'],
            ['Test user', ' _test_user_'],
            ['????????????????????', ' ????????????????????_id ', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider tidyProvider()
     *
     * @param string $expected
     * @param string $str
     */
    public function testTidy(string $expected, string $str)
    {
        $stringy = StringObject::create($str);
        $result = $stringy->tidy();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    public function tidyProvider(): array
    {
        return [
            ['"I see..."', '???I see??????'],
            ["'This too'", '???This too???'],
            ['test-dash', 'test???dash'],
            ['?? ???????????????????? ????????...', '?? ???????????????????? ???????????'],
        ];
    }

    /**
     * @dataProvider collapseWhitespaceProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testCollapseWhitespace(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->collapseWhitespace();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return string[][]
     */
    public function collapseWhitespaceProvider(): array
    {
        return [
            ['foo bar', '  foo   bar  '],
            ['test string', 'test string'],
            ['?? ????????????????????', '   ??     ????????????????????  '],
            ['123', ' 123 '],
            ['', ' ', 'UTF-8'], // no-break space (U+00A0)
            ['', '?????????????????????????????????', 'UTF-8'], // spaces U+2000 to U+200A
            ['', '???', 'UTF-8'], // narrow no-break space (U+202F)
            ['', '???', 'UTF-8'], // medium mathematical space (U+205F)
            ['', '???', 'UTF-8'], // ideographic space (U+3000)
            ['1 2 3', '  1??????2??????3??????', 'UTF-8'],
            ['', ' '],
            ['', ''],
        ];
    }

    /**
     * @dataProvider toAsciiProvider()
     *
     * @param string $expected
     * @param string $str
     * @param string $language
     * @param bool   $removeUnsupported
     */
    public function testToAscii(
        string $expected,
        string $str,
        string $language = 'en',
        bool $removeUnsupported = true
    ) {
        $stringy = StringObject::create($str);
        $result = $stringy->toAscii($language, $removeUnsupported);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array
     */
    public function toAsciiProvider(): array
    {
        return [
            ['foo bar', 'f???? b????'],
            [' TEST ', ' ???????? '],
            ['f = z = 3', '?? = ?? = 3'],
            ['perevirka', '??????????????????'],
            ['lysaya gora', '?????????? ????????'],
            ['user@host', 'user@host'],
            ['shchuka', '????????'],
            ['', '??????'],
            ['xin chao the gioi', 'xin ch??o th??? gi???i'],
            ['XIN CHAO THE GIOI', 'XIN CH??O TH??? GI???I'],
            ['dam phat chet luon', '?????m ph??t ch???t lu??n'],
            [' ', ' '], // no-break space (U+00A0)
            ['           ', '?????????????????????????????????'], // spaces U+2000 to U+200A
            [' ', '???'], // narrow no-break space (U+202F)
            [' ', '???'], // medium mathematical space (U+205F)
            [' ', '???'], // ideographic space (U+3000)
            ['', '????'], // some uncommon, unsupported character (U+10349)
            ['????', '????', 'en', false],
            ['aouAOU', '????????????'],
            ['aeoeueAEOEUE', '????????????', 'de'],
            ['aeoeueAEOEUE', '????????????', 'de_DE'],
        ];
    }

    /**
     * @dataProvider padProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $padStr
     * @param string      $padType
     * @param string|null $encoding
     */
    public function testPad(
        string $expected,
        string $str,
        int $length,
        string $padStr = ' ',
        string $padType = 'right',
        string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->pad($length, $padStr, $padType);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    public function padProvider(): array
    {
        return [
            // length <= str
            ['foo bar', 'foo bar', -1],
            ['foo bar', 'foo bar', 7],
            ['f???? b????', 'f???? b????', 7, ' ', 'right', 'UTF-8'],

            // right
            ['foo bar  ', 'foo bar', 9],
            ['foo bar_*', 'foo bar', 9, '_*', 'right'],
            ['f???? b??????????', 'f???? b????', 10, '????', 'right', 'UTF-8'],

            // left
            ['  foo bar', 'foo bar', 9, ' ', 'left'],
            ['_*foo bar', 'foo bar', 9, '_*', 'left'],
            ['??????f???? b????', 'f???? b????', 10, '????', 'left', 'UTF-8'],

            // both
            ['foo bar ', 'foo bar', 8, ' ', 'both'],
            ['??f???? b????????', 'f???? b????', 10, '????', 'both', 'UTF-8'],
            ['????f???? b??????????', 'f???? b????', 12, '??????', 'both', 'UTF-8'],
        ];
    }

    public function testPadException()
    {
        $stringy = StringObject::create('foo');
        $this->expectException(InvalidArgumentException::class);
        $stringy->pad(5, 'foo', 'bar');
    }

    /**
     * @dataProvider padLeftProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $padStr
     * @param string|null $encoding
     */
    public function testPadLeft(
        string $expected,
        string $str,
        int $length,
        string $padStr = ' ',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->padLeft($length, $padStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function padLeftProvider(): array
    {
        return [
            ['  foo bar', 'foo bar', 9],
            ['_*foo bar', 'foo bar', 9, '_*'],
            ['_*_foo bar', 'foo bar', 10, '_*'],
            ['  f???? b????', 'f???? b????', 9, ' ', 'UTF-8'],
            ['????f???? b????', 'f???? b????', 9, '????', 'UTF-8'],
            ['??????f???? b????', 'f???? b????', 10, '????', 'UTF-8'],
            ['????????f???? b????', 'f???? b????', 11, '????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider padRightProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $padStr
     * @param string|null $encoding
     */
    public function testPadRight(
        string $expected,
        string $str,
        int $length,
        string $padStr = ' ',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->padRight($length, $padStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function padRightProvider(): array
    {
        return [
            ['foo bar  ', 'foo bar', 9],
            ['foo bar_*', 'foo bar', 9, '_*'],
            ['foo bar_*_', 'foo bar', 10, '_*'],
            ['f???? b????  ', 'f???? b????', 9, ' ', 'UTF-8'],
            ['f???? b????????', 'f???? b????', 9, '????', 'UTF-8'],
            ['f???? b??????????', 'f???? b????', 10, '????', 'UTF-8'],
            ['f???? b????????????', 'f???? b????', 11, '????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider padBothProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $padStr
     * @param string|null $encoding
     */
    public function testPadBoth(
        string $expected,
        string $str,
        int $length,
        string $padStr = ' ',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->padBoth($length, $padStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function padBothProvider(): array
    {
        return [
            ['foo bar ', 'foo bar', 8],
            [' foo bar ', 'foo bar', 9, ' '],
            ['f???? b???? ', 'f???? b????', 8, ' ', 'UTF-8'],
            [' f???? b???? ', 'f???? b????', 9, ' ', 'UTF-8'],
            ['f???? b??????', 'f???? b????', 8, '????', 'UTF-8'],
            ['??f???? b??????', 'f???? b????', 9, '????', 'UTF-8'],
            ['??f???? b????????', 'f???? b????', 10, '????', 'UTF-8'],
            ['????f???? b????????', 'f???? b????', 11, '????', 'UTF-8'],
            ['??f???? b????????', 'f???? b????', 10, '??????', 'UTF-8'],
            ['????f???? b????????', 'f???? b????', 11, '??????', 'UTF-8'],
            ['????f???? b??????????', 'f???? b????', 12, '??????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider startsWithProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string      $substring
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testStartsWith(
        bool $expected,
        string $str,
        string $substring,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->startsWith($substring, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function startsWithProvider(): array
    {
        return [
            [true, 'foo bars', 'foo bar'],
            [true, 'FOO bars', 'foo bar', false],
            [true, 'FOO bars', 'foo BAR', false],
            [true, 'F???? b????s', 'f???? b????', false, 'UTF-8'],
            [true, 'f???? b????s', 'f???? B????', false, 'UTF-8'],
            [false, 'foo bar', 'bar'],
            [false, 'foo bar', 'foo bars'],
            [false, 'FOO bar', 'foo bars'],
            [false, 'FOO bars', 'foo BAR'],
            [false, 'F???? b????s', 'f???? b????', true, 'UTF-8'],
            [false, 'f???? b????s', 'f???? B????', true, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider startsWithProviderAny()
     *
     * @param bool        $expected
     * @param string      $str
     * @param array       $substrings
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testStartsWithAny(
        bool $expected,
        string $str,
        array $substrings,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->startsWithAny($substrings, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function startsWithProviderAny(): array
    {
        return [
            [true, 'foo bars', ['foo bar']],
            [true, 'FOO bars', ['foo bar'], false],
            [true, 'FOO bars', ['foo bar', 'foo BAR'], false],
            [true, 'F???? b????s', ['foo bar', 'f???? b????'], false, 'UTF-8'],
            [true, 'f???? b????s', ['foo bar', 'f???? B????'], false, 'UTF-8'],
            [false, 'foobar', []],
            [false, 'foo bar', ['bar']],
            [false, 'foo bar', ['foo bars']],
            [false, 'FOO bar', ['foo bars']],
            [false, 'FOO bars', ['foo BAR']],
            [false, 'F???? b????s', ['f???? b????'], true, 'UTF-8'],
            [false, 'f???? b????s', ['f???? B????'], true, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider endsWithProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string      $substring
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testEndsWith(
        bool $expected,
        string $str,
        string $substring,
        bool $caseSensitive = true,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->endsWith($substring, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function endsWithProvider(): array
    {
        return [
            [true, 'foo bars', 'o bars'],
            [true, 'FOO bars', 'o bars', false],
            [true, 'FOO bars', 'o BARs', false],
            [true, 'F???? b????s', '?? b????s', false, 'UTF-8'],
            [true, 'f???? b????s', '?? B????s', false, 'UTF-8'],
            [false, 'foo bar', 'foo'],
            [false, 'foo bar', 'foo bars'],
            [false, 'FOO bar', 'foo bars'],
            [false, 'FOO bars', 'foo BARS'],
            [false, 'F???? b????s', 'f???? b????s', true, 'UTF-8'],
            [false, 'f???? b????s', 'f???? B????S', true, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider endsWithAnyProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param array       $substrings
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testEndsWithAny(
        bool $expected,
        string $str,
        array $substrings,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->endsWithAny($substrings, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function endsWithAnyProvider(): array
    {
        return [
            [true, 'foo bars', ['foo', 'o bars']],
            [true, 'FOO bars', ['foo', 'o bars'], false],
            [true, 'FOO bars', ['foo', 'o BARs'], false],
            [true, 'F???? b????s', ['foo', '?? b????s'], false, 'UTF-8'],
            [true, 'f???? b????s', ['foo', '?? B????s'], false, 'UTF-8'],
            [false, 'foobar', []],
            [false, 'foo bar', ['foo']],
            [false, 'foo bar', ['foo', 'foo bars']],
            [false, 'FOO bar', ['foo', 'foo bars']],
            [false, 'FOO bars', ['foo', 'foo BARS']],
            [false, 'F???? b????s', ['f????', 'f???? b????s'], true, 'UTF-8'],
            [false, 'f???? b????s', ['f????', 'f???? B????S'], true, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider toBooleanProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testToBoolean(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->toBool();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array[]
     */
    public function toBooleanProvider(): array
    {
        return [
            [true, 'true'],
            [true, '1'],
            [true, 'on'],
            [true, 'ON'],
            [true, 'yes'],
            [false, 'false'],
            [false, '0'],
            [false, 'off'],
            [false, 'OFF'],
            [false, 'no'],
        ];
    }

    /**
     * @dataProvider toSpacesProvider()
     *
     * @param string $expected
     * @param string $str
     * @param int    $tabLength
     */
    public function testToSpaces(string $expected, string $str, int $tabLength = 4)
    {
        $stringy = StringObject::create($str);
        $result = $stringy->toSpaces($tabLength);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array
     */
    public function toSpacesProvider(): array
    {
        return [
            ['    foo    bar    ', '	foo	bar	'],
            ['     foo     bar     ', '	foo	bar	', 5],
            ['    foo  bar  ', '		foo	bar	', 2],
            ['foobar', '	foo	bar	', 0],
            ["    foo\n    bar", "	foo\n	bar"],
            ["    f????\n    b????", "	f????\n	b????"],
        ];
    }

    /**
     * @dataProvider toTabsProvider()
     *
     * @param string $expected
     * @param string $str
     * @param int    $tabLength
     */
    public function testToTabs(string $expected, string $str, int $tabLength = 4)
    {
        $stringy = StringObject::create($str);
        $result = $stringy->toTabs($tabLength);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return array
     */
    public function toTabsProvider(): array
    {
        return [
            ['	foo	bar	', '    foo    bar    '],
            ['	foo	bar	', '     foo     bar     ', 5],
            ['		foo	bar	', '    foo  bar  ', 2],
            ["	foo\n	bar", "    foo\n    bar"],
            ["	f????\n	b????", "    f????\n    b????"],
        ];
    }

    /**
     * @dataProvider toLowerCaseProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testToLowerCase(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->toLowerCase();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return string[][]
     */
    public function toLowerCaseProvider(): array
    {
        return [
            ['foo bar', 'FOO BAR'],
            [' foo_bar ', ' FOO_bar '],
            ['f???? b????', 'F???? B????', 'UTF-8'],
            [' f????_b???? ', ' F????_b???? ', 'UTF-8'],
            ['????????????????????', '????????????????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider toTitleCaseProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testToTitleCase(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->toTitleCase();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return string[][]
     */
    public function toTitleCaseProvider(): array
    {
        return [
            ['Foo Bar', 'foo bar'],
            [' Foo_Bar ', ' foo_bar '],
            ['F???? B????', 'f???? b????', 'UTF-8'],
            [' F????_B???? ', ' f????_b???? ', 'UTF-8'],
            ['???????????????????? ????????????????????', '???????????????????? ????????????????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider toUpperCaseProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testToUpperCase(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->toUpperCase();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }

    /**
     * @return string[][]
     */
    public function toUpperCaseProvider(): array
    {
        return [
            ['FOO BAR', 'foo bar'],
            [' FOO_BAR ', ' FOO_bar '],
            ['F???? B????', 'f???? b????', 'UTF-8'],
            [' F????_B???? ', ' F????_b???? ', 'UTF-8'],
            ['????????????????????', '????????????????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider slugifyProvider()
     *
     * @param string $expected
     * @param string $str
     * @param string $replacement
     */
    public function testSlugify(string $expected, string $str, string $replacement = '-')
    {
        $stringy = StringObject::create($str);
        $result = $stringy->slugify($replacement);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function slugifyProvider(): array
    {
        return [
            ['foo-bar', ' foo  bar '],
            ['foo-bar', 'foo -.-"-...bar'],
            ['another-foo-bar', 'another..& foo -.-"-...bar'],
            ['foo-d-bar', " Foo d'Bar "],
            ['a-string-with-dashes', 'A string-with-dashes'],
            ['user-at-host', 'user@host'],
            ['using-strings-like-foo-bar', 'Using strings like f???? b????'],
            ['numbers-1234', 'numbers 1234'],
            ['perevirka-ryadka', '?????????????????? ??????????'],
            ['bukvar-s-bukvoy-y', '?????????????? ?? ???????????? ??'],
            ['podehal-k-podezdu-moego-doma', '???????????????? ?? ???????????????? ?????????? ????????'],
            ['foo:bar:baz', 'Foo bar baz', ':'],
            ['a_string_with_underscores', 'A_string with_underscores', '_'],
            ['a_string_with_dashes', 'A string-with-dashes', '_'],
            ['a\string\with\dashes', 'A string-with-dashes', '\\'],
            ['an_odd_string', '--   An odd__   string-_', '_'],
        ];
    }

    /**
     * @dataProvider betweenProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $start
     * @param string      $end
     * @param int|null    $offset
     * @param string|null $encoding
     */
    public function testBetween(
        string $expected,
        string $str,
        string $start,
        string $end,
        ?int $offset = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->between($start, $end, $offset);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function betweenProvider(): array
    {
        return [
            ['', 'foo', '{', '}'],
            ['', '{foo', '{', '}'],
            ['foo', '{foo}', '{', '}'],
            ['{foo', '{{foo}', '{', '}'],
            ['', '{}foo}', '{', '}'],
            ['foo', '}{foo}', '{', '}'],
            ['foo', 'A description of {foo} goes here', '{', '}'],
            ['bar', '{foo} and {bar}', '{', '}', 1],
            ['', 'f????', '{', '}', 0, 'UTF-8'],
            ['', '{f????', '{', '}', 0, 'UTF-8'],
            ['f????', '{f????}', '{', '}', 0, 'UTF-8'],
            ['{f????', '{{f????}', '{', '}', 0, 'UTF-8'],
            ['', '{}f????}', '{', '}', 0, 'UTF-8'],
            ['f????', '}{f????}', '{', '}', 0, 'UTF-8'],
            ['f????', 'A description of {f????} goes here', '{', '}', 0, 'UTF-8'],
            ['b????', '{f????} and {b????}', '{', '}', 1, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider containsProvider()
     *
     * @param bool        $expected
     * @param string      $haystack
     * @param string      $needle
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testContains(
        bool $expected,
        string $haystack,
        string $needle,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($haystack, $encoding);
        $result = $stringy->contains($needle, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function containsProvider(): array
    {
        return [
            [true, 'Str contains foo bar', 'foo bar'],
            [true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%'],
            [true, '?? ???????????????????? ????????', '????????????????????', false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '????????', true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '???? ???', true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '??????', true, 'UTF-8'],
            [false, 'Str contains foo bar', 'Foo bar'],
            [false, 'Str contains foo bar', 'foobar'],
            [false, 'Str contains foo bar', 'foo bar '],
            [false, '?? ???????????????????? ????????', '  ???????????????????? ', true, 'UTF-8'],
            [false, '?????????????????? ???????????????????????', ' ??????', true, 'UTF-8'],
            [true, 'Str contains foo bar', 'Foo bar', false],
            [true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%', false],
            [true, '?? ???????????????????? ????????', '????????????????????', false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '????????', false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '???? ???', false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', '??????', false, 'UTF-8'],
            [false, 'Str contains foo bar', 'foobar', false],
            [false, 'Str contains foo bar', 'foo bar ', false],
            [false, '?? ???????????????????? ????????', '  ???????????????????? ', false, 'UTF-8'],
            [false, '?????????????????? ???????????????????????', ' ??????', false, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider containsAnyProvider()
     *
     * @param bool        $expected
     * @param string      $haystack
     * @param array       $needles
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testContainsAny(
        bool $expected,
        string $haystack,
        array $needles,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($haystack, $encoding);
        $result = $stringy->containsAny($needles, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function containsAnyProvider(): array
    {
        // One needle
        $singleNeedle = array_map(function ($array) {
            $array[2] = [$array[2]];

            return $array;
        }, $this->containsProvider());

        $provider = [
            // No needles
            [false, 'Str contains foo bar', []],
            // Multiple needles
            [true, 'Str contains foo bar', ['foo', 'bar']],
            [true, '12398!@(*%!@# @!%#*&^%', [' @!%#*', '&^%']],
            [true, '?? ???????????????????? ????????', ['??????????', '??????????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['??????', '??'], true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['???? ', '???'], true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['????', '??'], true, 'UTF-8'],
            [false, 'Str contains foo bar', ['Foo', 'Bar']],
            [false, 'Str contains foo bar', ['foobar', 'bar ']],
            [false, 'Str contains foo bar', ['foo bar ', '  foo']],
            [
                false,
                '?? ???????????????????? ????????',
                ['  ???????????????????? ', '  ?????????????? '],
                true,
                'UTF-8',
            ],
            [false, '?????????????????? ???????????????????????', [' ??????', ' ?? '], true, 'UTF-8'],
            [true, 'Str contains foo bar', ['Foo bar', 'bar'], false],
            [true, '12398!@(*%!@# @!%#*&^%', [' @!%#*&^%', '*&^%'], false],
            [true, '?? ???????????????????? ????????', ['????????????????????', '????????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['????????', '????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['???? ???', ' ???'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['??????', '??'], false, 'UTF-8'],
            [false, 'Str contains foo bar', ['foobar', 'none'], false],
            [false, 'Str contains foo bar', ['foo bar ', ' ba '], false],
            [false, '?? ???????????????????? ????????', ['  ???????????????????? ', ' ???????? '], false, 'UTF-8'],
            [false, '?????????????????? ???????????????????????', [' ??????', ' ???? '], false, 'UTF-8'],
        ];

        return array_merge($singleNeedle, $provider);
    }

    /**
     * @dataProvider containsAllProvider()
     *
     * @param bool        $expected
     * @param string      $haystack
     * @param array       $needles
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testContainsAll(
        bool $expected,
        string $haystack,
        array $needles,
        bool $caseSensitive = true,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($haystack, $encoding);
        $result = $stringy->containsAll($needles, $caseSensitive);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function containsAllProvider(): array
    {
        // One needle
        $singleNeedle = array_map(function ($array) {
            $array[2] = [$array[2]];

            return $array;
        }, $this->containsProvider());

        $provider = [
            // One needle
            [false, 'Str contains foo bar', []],
            // Multiple needles
            [true, 'Str contains foo bar', ['foo', 'bar']],
            [true, '12398!@(*%!@# @!%#*&^%', [' @!%#*', '&^%']],
            [true, '?? ???????????????????? ????????', ['??????????', '??????????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['??????', '??'], true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['???? ', '???'], true, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['????', '??'], true, 'UTF-8'],
            [false, 'Str contains foo bar', ['Foo', 'bar']],
            [false, 'Str contains foo bar', ['foobar', 'bar']],
            [false, 'Str contains foo bar', ['foo bar ', 'bar']],
            [
                false,
                '?? ???????????????????? ????????',
                ['  ???????????????????? ', '  ?????????????? '],
                true,
                'UTF-8',
            ],
            [false, '?????????????????? ???????????????????????', [' ??????', ' ?? '], true, 'UTF-8'],
            [true, 'Str contains foo bar', ['Foo bar', 'bar'], false],
            [true, '12398!@(*%!@# @!%#*&^%', [' @!%#*&^%', '*&^%'], false],
            [true, '?? ???????????????????? ????????', ['????????????????????', '????????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['????????', '????'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['???? ???', ' ???'], false, 'UTF-8'],
            [true, '?????????????????? ???????????????????????', ['??????', '??'], false, 'UTF-8'],
            [false, 'Str contains foo bar', ['foobar', 'none'], false],
            [false, 'Str contains foo bar', ['foo bar ', ' ba'], false],
            [false, '?? ???????????????????? ????????', ['  ???????????????????? ', ' ???????? '], false, 'UTF-8'],
            [false, '?????????????????? ???????????????????????', [' ??????', ' ???? '], false, 'UTF-8'],
        ];

        return array_merge($singleNeedle, $provider);
    }

    /**
     * @dataProvider surroundProvider()
     *
     * @param string $expected
     * @param string $str
     * @param string $substring
     */
    public function testSurround(string $expected, string $str, string $substring)
    {
        $stringy = StringObject::create($str);
        $result = $stringy->surround($substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function surroundProvider(): array
    {
        return [
            ['__foobar__', 'foobar', '__'],
            ['test', 'test', ''],
            ['**', '', '*'],
            ['??f???? b??????', 'f???? b????', '??'],
            ['????????? test ?????????', ' test ', '?????????'],
        ];
    }

    /**
     * @dataProvider insertProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $substring
     * @param int         $index
     * @param string|null $encoding
     */
    public function testInsert(
        string $expected,
        string $str,
        string $substring,
        int $index,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->insert($substring, $index);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function insertProvider(): array
    {
        return [
            ['foo bar', 'oo bar', 'f', 0],
            ['foo bar', 'f bar', 'oo', 1],
            ['f bar', 'f bar', 'oo', 20],
            ['foo bar', 'foo ba', 'r', 6],
            ['f????b????', 'f????b??', '??', 4, 'UTF-8'],
            ['f???? b????', '???? b????', 'f', 0, 'UTF-8'],
            ['f???? b????', 'f b????', '????', 1, 'UTF-8'],
            ['f???? b????', 'f???? b??', '??', 6, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider truncateProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $substring
     * @param string|null $encoding
     */
    public function testTruncate(
        string $expected,
        string $str,
        int $length,
        string $substring = '',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->truncate($length, $substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function truncateProvider(): array
    {
        return [
            ['Test foo bar', 'Test foo bar', 12],
            ['Test foo ba', 'Test foo bar', 11],
            ['Test foo', 'Test foo bar', 8],
            ['Test fo', 'Test foo bar', 7],
            ['Test', 'Test foo bar', 4],
            ['Test foo bar', 'Test foo bar', 12, '...'],
            ['Test foo...', 'Test foo bar', 11, '...'],
            ['Test ...', 'Test foo bar', 8, '...'],
            ['Test...', 'Test foo bar', 7, '...'],
            ['T...', 'Test foo bar', 4, '...'],
            ['Test fo....', 'Test foo bar', 11, '....'],
            ['Test f???? b????', 'Test f???? b????', 12, '', 'UTF-8'],
            ['Test f???? b??', 'Test f???? b????', 11, '', 'UTF-8'],
            ['Test f????', 'Test f???? b????', 8, '', 'UTF-8'],
            ['Test f??', 'Test f???? b????', 7, '', 'UTF-8'],
            ['Test', 'Test f???? b????', 4, '', 'UTF-8'],
            ['Test f???? b????', 'Test f???? b????', 12, '????', 'UTF-8'],
            ['Test f???? ????', 'Test f???? b????', 11, '????', 'UTF-8'],
            ['Test f????', 'Test f???? b????', 8, '????', 'UTF-8'],
            ['Test ????', 'Test f???? b????', 7, '????', 'UTF-8'],
            ['Te????', 'Test f???? b????', 4, '????', 'UTF-8'],
            ['What are your pl...', 'What are your plans today?', 19, '...'],
        ];
    }

    /**
     * @dataProvider safeTruncateProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $length
     * @param string      $substring
     * @param string|null $encoding
     */
    public function testSafeTruncate(
        string $expected,
        string $str,
        int $length,
        string $substring = '',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->safeTruncate($length, $substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function safeTruncateProvider(): array
    {
        return [
            ['Test foo bar', 'Test foo bar', 12],
            ['Test foo', 'Test foo bar', 11],
            ['Test foo', 'Test foo bar', 8],
            ['Test', 'Test foo bar', 7],
            ['Test', 'Test foo bar', 4],
            ['Test foo bar', 'Test foo bar', 12, '...'],
            ['Test foo...', 'Test foo bar', 11, '...'],
            ['Test...', 'Test foo bar', 8, '...'],
            ['Test...', 'Test foo bar', 7, '...'],
            ['T...', 'Test foo bar', 4, '...'],
            ['Test....', 'Test foo bar', 11, '....'],
            ['T??st f???? b????', 'T??st f???? b????', 12, '', 'UTF-8'],
            ['T??st f????', 'T??st f???? b????', 11, '', 'UTF-8'],
            ['T??st f????', 'T??st f???? b????', 8, '', 'UTF-8'],
            ['T??st', 'T??st f???? b????', 7, '', 'UTF-8'],
            ['T??st', 'T??st f???? b????', 4, '', 'UTF-8'],
            ['T??st f???? b????', 'T??st f???? b????', 12, '????', 'UTF-8'],
            ['T??st f????????', 'T??st f???? b????', 11, '????', 'UTF-8'],
            ['T??st????', 'T??st f???? b????', 8, '????', 'UTF-8'],
            ['T??st????', 'T??st f???? b????', 7, '????', 'UTF-8'],
            ['T??????', 'T??st f???? b????', 4, '????', 'UTF-8'],
            ['What are your plans...', 'What are your plans today?', 22, '...'],
        ];
    }

    /**
     * @dataProvider reverseProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testReverse(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->reverse();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function reverseProvider(): array
    {
        return [
            ['', ''],
            ['raboof', 'foobar'],
            ['????b????f', 'f????b????', 'UTF-8'],
            ['????b ????f', 'f???? b????', 'UTF-8'],
            ['?????? ??????', '?????? ??????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider repeatProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $multiplier
     * @param string|null $encoding
     */
    public function testRepeat(string $expected, string $str, int $multiplier, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->repeat($multiplier);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    public function repeatProvider(): array
    {
        return [
            ['', 'foo', 0],
            ['foo', 'foo', 1],
            ['foofoo', 'foo', 2],
            ['foofoofoo', 'foo', 3],
            ['f????', 'f????', 1, 'UTF-8'],
            ['f????f????', 'f????', 2, 'UTF-8'],
            ['f????f????f????', 'f????', 3, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider shuffleProvider()
     *
     * @param string      $str
     * @param string|null $encoding
     */
    public function testShuffle(string $str, ?string $encoding = null): void
    {
        $stringy = StringObject::create($str, $encoding);
        $encoding = $encoding ?: mb_internal_encoding();
        $result = $stringy->shuffle();

        $this->assertStringType($result);
        $this->assertEquals($str, $stringy->getScalarValue());
        $this->assertEquals(
            mb_strlen($str, $encoding),
            mb_strlen($result->getScalarValue(), $encoding)
        );

        // We'll make sure that the chars are present after shuffle
        for ($i = 0; $i < mb_strlen($str, $encoding); ++$i) {
            $char = mb_substr($str, $i, 1, $encoding);
            $countBefore = mb_substr_count($str, $char, $encoding);
            $countAfter = mb_substr_count($result->getScalarValue(), $char, $encoding);
            $this->assertEquals($countBefore, $countAfter);
        }
    }

    /**
     * @return string[][]
     */
    public function shuffleProvider(): array
    {
        return [
            ['foo bar'],
            ['?????? ??????', 'UTF-8'],
            ['?????????????????? ???????????????????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider trimProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $chars
     * @param string|null $encoding
     */
    public function testTrim(string $expected, string $str, ?string $chars = null, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->trim($chars);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function trimProvider(): array
    {
        return [
            ['foo   bar', '  foo   bar  '],
            ['foo bar', ' foo bar'],
            ['foo bar', 'foo bar '],
            ['foo bar', "\n\t foo bar \n\t"],
            ['f????   b????', '  f????   b????  '],
            ['f???? b????', ' f???? b????'],
            ['f???? b????', 'f???? b???? '],
            [' foo bar ', "\n\t foo bar \n\t", "\n\t"],
            ['f???? b????', "\n\t f???? b???? \n\t", null, 'UTF-8'],
            ['f????', '???f???????', null, 'UTF-8'], // narrow no-break space (U+202F)
            ['f????', '??????f??????????', null, 'UTF-8'], // medium mathematical space (U+205F)
            ['f????', '?????????????????????????????????f????', null, 'UTF-8'], // spaces U+2000 to U+200A
        ];
    }

    /**
     * @dataProvider trimLeftProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $chars
     * @param string|null $encoding
     */
    public function testTrimLeft(
        string $expected,
        string $str,
        ?string $chars = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->trimLeft($chars);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function trimLeftProvider(): array
    {
        return [
            ['foo   bar  ', '  foo   bar  '],
            ['foo bar', ' foo bar'],
            ['foo bar ', 'foo bar '],
            ["foo bar \n\t", "\n\t foo bar \n\t"],
            ['f????   b????  ', '  f????   b????  '],
            ['f???? b????', ' f???? b????'],
            ['f???? b???? ', 'f???? b???? '],
            ['foo bar', '--foo bar', '-'],
            ['f???? b????', '????f???? b????', '??', 'UTF-8'],
            ["f???? b???? \n\t", "\n\t f???? b???? \n\t", null, 'UTF-8'],
            ['f???????', '???f???????', null, 'UTF-8'], // narrow no-break space (U+202F)
            ['f??????????', '??????f??????????', null, 'UTF-8'], // medium mathematical space (U+205F)
            ['f????', '?????????????????????????????????f????', null, 'UTF-8'], // spaces U+2000 to U+200A
        ];
    }

    /**
     * @dataProvider trimRightProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $chars
     * @param string|null $encoding
     */
    public function testTrimRight(
        string $expected,
        string $str,
        ?string $chars = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->trimRight($chars);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function trimRightProvider(): array
    {
        return [
            ['  foo   bar', '  foo   bar  '],
            ['foo bar', 'foo bar '],
            [' foo bar', ' foo bar'],
            ["\n\t foo bar", "\n\t foo bar \n\t"],
            ['  f????   b????', '  f????   b????  '],
            ['f???? b????', 'f???? b???? '],
            [' f???? b????', ' f???? b????'],
            ['foo bar', 'foo bar--', '-'],
            ['f???? b????', 'f???? b????????', '??', 'UTF-8'],
            ["\n\t f???? b????", "\n\t f???? b???? \n\t", null, 'UTF-8'],
            ['???f????', '???f???????', null, 'UTF-8'], // narrow no-break space (U+202F)
            ['??????f????', '??????f??????????', null, 'UTF-8'], // medium mathematical space (U+205F)
            ['f????', 'f?????????????????????????????????????', null, 'UTF-8'], // spaces U+2000 to U+200A
        ];
    }

    /**
     * @dataProvider longestCommonPrefixProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $otherStr
     * @param string|null $encoding
     */
    public function testLongestCommonPrefix(
        string $expected,
        string $str,
        string $otherStr,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->longestCommonPrefix($otherStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    public function longestCommonPrefixProvider(): array
    {
        return [
            ['foo', 'foobar', 'foo bar'],
            ['foo bar', 'foo bar', 'foo bar'],
            ['f', 'foo bar', 'far boo'],
            ['', 'toy car', 'foo bar'],
            ['', 'foo bar', ''],
            ['f????', 'f????bar', 'f???? bar', 'UTF-8'],
            ['f???? bar', 'f???? bar', 'f???? bar', 'UTF-8'],
            ['f??', 'f???? bar', 'f??r bar', 'UTF-8'],
            ['', 'toy car', 'f???? bar', 'UTF-8'],
            ['', 'f???? bar', '', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider longestCommonSuffixProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $otherStr
     * @param string|null $encoding
     */
    public function testLongestCommonSuffix(
        string $expected,
        string $str,
        string $otherStr,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->longestCommonSuffix($otherStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function longestCommonSuffixProvider(): array
    {
        return [
            ['bar', 'foobar', 'foo bar'],
            ['foo bar', 'foo bar', 'foo bar'],
            ['ar', 'foo bar', 'boo far'],
            ['', 'foo bad', 'foo bar'],
            ['', 'foo bar', ''],
            ['b????', 'f????b????', 'f???? b????', 'UTF-8'],
            ['f???? b????', 'f???? b????', 'f???? b????', 'UTF-8'],
            [' b????', 'f???? b????', 'f??r b????', 'UTF-8'],
            ['', 'toy car', 'f???? b????', 'UTF-8'],
            ['', 'f???? b????', '', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider longestCommonSubstringProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $otherStr
     * @param string|null $encoding
     */
    public function testLongestCommonSubstring(
        string $expected,
        string $str,
        string $otherStr,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->longestCommonSubstring($otherStr);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function longestCommonSubstringProvider(): array
    {
        return [
            ['foo', 'foobar', 'foo bar'],
            ['foo bar', 'foo bar', 'foo bar'],
            ['oo ', 'foo bar', 'boo far'],
            ['foo ba', 'foo bad', 'foo bar'],
            ['', 'foo bar', ''],
            ['f????', 'f????b????', 'f???? b????', 'UTF-8'],
            ['f???? b????', 'f???? b????', 'f???? b????', 'UTF-8'],
            [' b????', 'f???? b????', 'f??r b????', 'UTF-8'],
            [' ', 'toy car', 'f???? b????', 'UTF-8'],
            ['', 'f???? b????', '', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider lengthProvider()
     *
     * @param int         $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testLength(int $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->length();
        $this->assertTrue(is_int($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function lengthProvider(): array
    {
        return [
            [11, '  foo bar  '],
            [1, 'f'],
            [0, ''],
            [7, 'f???? b????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider sliceProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $start
     * @param int|null    $end
     * @param string|null $encoding
     */
    public function testSlice(
        string $expected,
        string $str,
        int $start,
        ?int $end = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->slice($start, $end);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function sliceProvider(): array
    {
        return [
            ['foobar', 'foobar', 0],
            ['foobar', 'foobar', 0, null],
            ['foobar', 'foobar', 0, 6],
            ['fooba', 'foobar', 0, 5],
            ['', 'foobar', 3, 0],
            ['', 'foobar', 3, 2],
            ['ba', 'foobar', 3, 5],
            ['ba', 'foobar', 3, -1],
            ['f????b????', 'f????b????', 0, null, 'UTF-8'],
            ['f????b????', 'f????b????', 0, null],
            ['f????b????', 'f????b????', 0, 6, 'UTF-8'],
            ['f????b??', 'f????b????', 0, 5, 'UTF-8'],
            ['', 'f????b????', 3, 0, 'UTF-8'],
            ['', 'f????b????', 3, 2, 'UTF-8'],
            ['b??', 'f????b????', 3, 5, 'UTF-8'],
            ['b??', 'f????b????', 3, -1, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider splitProvider()
     *
     * @param array       $expected
     * @param string      $str
     * @param string      $pattern
     * @param int|null    $limit
     * @param string|null $encoding
     */
    public function testSplit(
        array $expected,
        string $str,
        string $pattern,
        ?int $limit = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->split($pattern, $limit);

        $this->assertTrue(is_array($result));
        foreach ($result as $string) {
            $this->assertStringType($string);
        }

        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertEquals($expected[$i], $result[$i]);
        }
    }

    /**
     * @return array[]
     */
    public function splitProvider(): array
    {
        return [
            [['foo,bar,baz'], 'foo,bar,baz', ''],
            [['foo,bar,baz'], 'foo,bar,baz', '-'],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ','],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ',', null],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ',', -1],
            [[], 'foo,bar,baz', ',', 0],
            [['foo'], 'foo,bar,baz', ',', 1],
            [['foo', 'bar'], 'foo,bar,baz', ',', 2],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ',', 3],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ',', 10],
            [['f????,b????,baz'], 'f????,b????,baz', '-', null, 'UTF-8'],
            [['f????', 'b????', 'baz'], 'f????,b????,baz', ',', null, 'UTF-8'],
            [['f????', 'b????', 'baz'], 'f????,b????,baz', ',', null, 'UTF-8'],
            [['f????', 'b????', 'baz'], 'f????,b????,baz', ',', -1, 'UTF-8'],
            [[], 'f????,b????,baz', ',', 0, 'UTF-8'],
            [['f????'], 'f????,b????,baz', ',', 1, 'UTF-8'],
            [['f????', 'b????'], 'f????,b????,baz', ',', 2, 'UTF-8'],
            [['f????', 'b????', 'baz'], 'f????,b????,baz', ',', 3, 'UTF-8'],
            [['f????', 'b????', 'baz'], 'f????,b????,baz', ',', 10, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider stripWhitespaceProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testStripWhitespace(string $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->stripWhitespace();
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function stripWhitespaceProvider(): array
    {
        return [
            ['foobar', '  foo   bar  '],
            ['teststring', 'test string'],
            ['??????????????????????', '   ??     ????????????????????  '],
            ['123', ' 123 '],
            ['', ' ', 'UTF-8'], // no-break space (U+00A0)
            ['', '?????????????????????????????????', 'UTF-8'], // spaces U+2000 to U+200A
            ['', '???', 'UTF-8'], // narrow no-break space (U+202F)
            ['', '???', 'UTF-8'], // medium mathematical space (U+205F)
            ['', '???', 'UTF-8'], // ideographic space (U+3000)
            ['123', '  1??????2??????3??????', 'UTF-8'],
            ['', ' '],
            ['', ''],
        ];
    }

    /**
     * @dataProvider substrProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $start
     * @param int|null    $length
     * @param string|null $encoding
     */
    public function testSubstr(
        string $expected,
        string $str,
        int $start,
        ?int $length = null,
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->substr($start, $length);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function substrProvider(): array
    {
        return [
            ['foo bar', 'foo bar', 0],
            ['bar', 'foo bar', 4],
            ['bar', 'foo bar', 4, null],
            ['o b', 'foo bar', 2, 3],
            ['', 'foo bar', 4, 0],
            ['f???? b????', 'f???? b????', 0, null, 'UTF-8'],
            ['b????', 'f???? b????', 4, null, 'UTF-8'],
            ['?? b', 'f???? b????', 2, 3, 'UTF-8'],
            ['', 'f???? b????', 4, 0, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider atProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $index
     * @param string|null $encoding
     */
    public function testAt(string $expected, string $str, int $index, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->at($index);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function atProvider(): array
    {
        return [
            ['f', 'foo bar', 0],
            ['o', 'foo bar', 1],
            ['r', 'foo bar', 6],
            ['', 'foo bar', 7],
            ['f', 'f???? b????', 0, 'UTF-8'],
            ['??', 'f???? b????', 1, 'UTF-8'],
            ['??', 'f???? b????', 6, 'UTF-8'],
            ['', 'f???? b????', 7, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider firstProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $n
     * @param string|null $encoding
     */
    public function testFirst(string $expected, string $str, int $n, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->first($n);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function firstProvider(): array
    {
        return [
            ['', 'foo bar', -5],
            ['', 'foo bar', 0],
            ['f', 'foo bar', 1],
            ['foo', 'foo bar', 3],
            ['foo bar', 'foo bar', 7],
            ['foo bar', 'foo bar', 8],
            ['', 'f???? b????', -5, 'UTF-8'],
            ['', 'f???? b????', 0, 'UTF-8'],
            ['f', 'f???? b????', 1, 'UTF-8'],
            ['f????', 'f???? b????', 3, 'UTF-8'],
            ['f???? b????', 'f???? b????', 7, 'UTF-8'],
            ['f???? b????', 'f???? b????', 8, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider lastProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $n
     * @param string|null $encoding
     */
    public function testLast(string $expected, string $str, int $n, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->last($n);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function lastProvider(): array
    {
        return [
            ['', 'foo bar', -5],
            ['', 'foo bar', 0],
            ['r', 'foo bar', 1],
            ['bar', 'foo bar', 3],
            ['foo bar', 'foo bar', 7],
            ['foo bar', 'foo bar', 8],
            ['', 'f???? b????', -5, 'UTF-8'],
            ['', 'f???? b????', 0, 'UTF-8'],
            ['??', 'f???? b????', 1, 'UTF-8'],
            ['b????', 'f???? b????', 3, 'UTF-8'],
            ['f???? b????', 'f???? b????', 7, 'UTF-8'],
            ['f???? b????', 'f???? b????', 8, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider ensureLeftProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $substring
     * @param string|null $encoding
     */
    public function testEnsureLeft(string $expected, string $str, string $substring, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->ensureLeft($substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function ensureLeftProvider(): array
    {
        return [
            ['foobar', 'foobar', 'f'],
            ['foobar', 'foobar', 'foo'],
            ['foo/foobar', 'foobar', 'foo/'],
            ['http://foobar', 'foobar', 'http://'],
            ['http://foobar', 'http://foobar', 'http://'],
            ['f????b????', 'f????b????', 'f', 'UTF-8'],
            ['f????b????', 'f????b????', 'f????', 'UTF-8'],
            ['f????/f????b????', 'f????b????', 'f????/', 'UTF-8'],
            ['http://f????b????', 'f????b????', 'http://', 'UTF-8'],
            ['http://f????b????', 'http://f????b????', 'http://', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider ensureRightProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $substring
     * @param string|null $encoding
     */
    public function testEnsureRight(string $expected, string $str, string $substring, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->ensureRight($substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function ensureRightProvider(): array
    {
        return [
            ['foobar', 'foobar', 'r'],
            ['foobar', 'foobar', 'bar'],
            ['foobar/bar', 'foobar', '/bar'],
            ['foobar.com/', 'foobar', '.com/'],
            ['foobar.com/', 'foobar.com/', '.com/'],
            ['f????b????', 'f????b????', '??', 'UTF-8'],
            ['f????b????', 'f????b????', 'b????', 'UTF-8'],
            ['f????b????/b????', 'f????b????', '/b????', 'UTF-8'],
            ['f????b????.com/', 'f????b????', '.com/', 'UTF-8'],
            ['f????b????.com/', 'f????b????.com/', '.com/', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider removeLeftProvider()
     *
     * @param string              $expected
     * @param string              $str
     * @param string|StringObject $substring
     * @param string|null         $encoding
     */
    public function testRemoveLeft(
        string $expected,
        string $str,
        string | StringObject $substring,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->removeLeft($substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function removeLeftProvider(): array
    {
        return [
            ['foo bar', 'foo bar', ''],
            ['oo bar', 'foo bar', 'f'],
            ['bar', 'foo bar', 'foo '],
            ['foo bar', 'foo bar', 'oo'],
            ['foo bar', 'foo bar', 'oo bar'],
            ['oo bar', 'foo bar', StringObject::create('foo bar')->first(1), 'UTF-8'],
            ['oo bar', 'foo bar', StringObject::create('foo bar')->at(0), 'UTF-8'],
            ['f???? b????', 'f???? b????', '', 'UTF-8'],
            ['???? b????', 'f???? b????', 'f', 'UTF-8'],
            ['b????', 'f???? b????', 'f???? ', 'UTF-8'],
            ['f???? b????', 'f???? b????', '????', 'UTF-8'],
            ['f???? b????', 'f???? b????', '???? b????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider removeRightProvider()
     *
     * @param string              $expected
     * @param string              $str
     * @param string|StringObject $substring
     * @param string|null         $encoding
     */
    public function testRemoveRight(
        string $expected,
        string $str,
        string | StringObject $substring,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->removeRight($substring);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    public function removeRightProvider(): array
    {
        return [
            ['foo bar', 'foo bar', ''],
            ['foo ba', 'foo bar', 'r'],
            ['foo', 'foo bar', ' bar'],
            ['foo bar', 'foo bar', 'ba'],
            ['foo bar', 'foo bar', 'foo ba'],
            ['foo ba', 'foo bar', StringObject::create('foo bar')->last(1), 'UTF-8'],
            ['foo ba', 'foo bar', StringObject::create('foo bar')->at(6), 'UTF-8'],
            ['f???? b????', 'f???? b????', '', 'UTF-8'],
            ['f???? b??', 'f???? b????', '??', 'UTF-8'],
            ['f????', 'f???? b????', ' b????', 'UTF-8'],
            ['f???? b????', 'f???? b????', 'b??', 'UTF-8'],
            ['f???? b????', 'f???? b????', 'f???? b??', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isAlphaProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsAlpha(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isAlpha();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isAlphaProvider(): array
    {
        return [
            [true, ''],
            [true, 'foobar'],
            [false, 'foo bar'],
            [false, 'foobar2'],
            [true, 'f????b????', 'UTF-8'],
            [false, 'f???? b????', 'UTF-8'],
            [false, 'f????b????2', 'UTF-8'],
            [true, '????????????', 'UTF-8'],
            [false, '????????????????', 'UTF-8'],
            [true, '?????????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isAlphanumericProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsAlphanumeric(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isAlphanumeric();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isAlphanumericProvider(): array
    {
        return [
            [true, ''],
            [true, 'foobar1'],
            [false, 'foo bar'],
            [false, 'foobar2"'],
            [false, "\nfoobar\n"],
            [true, 'f????b????1', 'UTF-8'],
            [false, 'f???? b????', 'UTF-8'],
            [false, 'f????b????2"', 'UTF-8'],
            [true, '????????????', 'UTF-8'],
            [false, '????????????????', 'UTF-8'],
            [true, '?????????111', 'UTF-8'],
            [true, '????????????1', 'UTF-8'],
            [false, '????????????1 ', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isBlankProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsBlank(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isBlank();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isBlankProvider(): array
    {
        return [
            [true, ''],
            [true, ' '],
            [true, "\n\t "],
            [true, "\n\t  \v\f"],
            [false, "\n\t a \v\f"],
            [false, "\n\t ' \v\f"],
            [false, "\n\t 2 \v\f"],
            [true, '', 'UTF-8'],
            [true, ' ', 'UTF-8'], // no-break space (U+00A0)
            [true, '?????????????????????????????????', 'UTF-8'], // spaces U+2000 to U+200A
            [true, '???', 'UTF-8'], // narrow no-break space (U+202F)
            [true, '???', 'UTF-8'], // medium mathematical space (U+205F)
            [true, '???', 'UTF-8'], // ideographic space (U+3000)
            [false, '???z', 'UTF-8'],
            [false, '???1', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isJsonProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsJson(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isJson();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isJsonProvider(): array
    {
        return [
            [false, ''],
            [false, '  '],
            [true, 'null'],
            [true, 'true'],
            [true, 'false'],
            [true, '[]'],
            [true, '{}'],
            [true, '123'],
            [true, '{"foo": "bar"}'],
            [false, '{"foo":"bar",}'],
            [false, '{"foo"}'],
            [true, '["foo"]'],
            [false, '{"foo": "bar"]'],
            [true, '123', 'UTF-8'],
            [true, '{"f????": "b????"}', 'UTF-8'],
            [false, '{"f????":"b????",}', 'UTF-8'],
            [false, '{"f????"}', 'UTF-8'],
            [false, '["f????": "b????"]', 'UTF-8'],
            [true, '["f????"]', 'UTF-8'],
            [false, '{"f????": "b????"]', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isLowerCaseProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsLowerCase(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isLowerCase();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isLowerCaseProvider(): array
    {
        return [
            [true, ''],
            [true, 'foobar'],
            [false, 'foo bar'],
            [false, 'Foobar'],
            [true, 'f????b????', 'UTF-8'],
            [false, 'f????b????2', 'UTF-8'],
            [false, 'f???? b????', 'UTF-8'],
            [false, 'f????b????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider hasLowerCaseProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testHasLowerCase(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->hasLowerCase();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function hasLowerCaseProvider(): array
    {
        return [
            [false, ''],
            [true, 'foobar'],
            [false, 'FOO BAR'],
            [true, 'fOO BAR'],
            [true, 'foO BAR'],
            [true, 'FOO BAr'],
            [true, 'Foobar'],
            [false, 'F????B????', 'UTF-8'],
            [true, 'f????b????', 'UTF-8'],
            [true, 'f????b????2', 'UTF-8'],
            [true, 'F???? b????', 'UTF-8'],
            [true, 'f????b????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isSerializedProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsSerialized(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isSerialized();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isSerializedProvider(): array
    {
        return [
            [false, ''],
            [true, 'a:1:{s:3:"foo";s:3:"bar";}'],
            [false, 'a:1:{s:3:"foo";s:3:"bar"}'],
            [true, serialize(['foo' => 'bar'])],
            [true, 'a:1:{s:5:"f????";s:5:"b????";}', 'UTF-8'],
            [false, 'a:1:{s:5:"f????";s:5:"b????"}', 'UTF-8'],
            [true, serialize(['f????' => 'b??r']), 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isBase64Provider()
     *
     * @param bool   $expected
     * @param string $str
     */
    public function testIsBase64(bool $expected, string $str): void
    {
        $stringy = StringObject::create($str);
        $result = $stringy->isBase64();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isBase64Provider(): array
    {
        return [
            [false, ' '],
            [true, ''],
            [true, base64_encode('FooBar')],
            [true, base64_encode(' ')],
            [true, base64_encode('F????B????')],
            [true, base64_encode('????????????????????')],
            [false, 'Foobar'],
        ];
    }

    /**
     * @dataProvider isUpperCaseProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsUpperCase(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isUpperCase();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isUpperCaseProvider(): array
    {
        return [
            [true, ''],
            [true, 'FOOBAR'],
            [false, 'FOO BAR'],
            [false, 'fOOBAR'],
            [true, 'F????B????', 'UTF-8'],
            [false, 'F????B????2', 'UTF-8'],
            [false, 'F???? B????', 'UTF-8'],
            [false, 'F????B????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider hasUpperCaseProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testHasUpperCase(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->hasUpperCase();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function hasUpperCaseProvider(): array
    {
        return [
            [false, ''],
            [true, 'FOOBAR'],
            [false, 'foo bar'],
            [true, 'Foo bar'],
            [true, 'FOo bar'],
            [true, 'foo baR'],
            [true, 'fOOBAR'],
            [false, 'f????b????', 'UTF-8'],
            [true, 'F????B????', 'UTF-8'],
            [true, 'F????B????2', 'UTF-8'],
            [true, 'f???? B????', 'UTF-8'],
            [true, 'F????B????', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider isHexadecimalProvider()
     *
     * @param bool        $expected
     * @param string      $str
     * @param string|null $encoding
     */
    public function testIsHexadecimal(bool $expected, string $str, ?string $encoding = null)
    {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->isHexadecimal();
        $this->assertTrue(is_bool($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function isHexadecimalProvider(): array
    {
        return [
            [true, ''],
            [true, 'abcdef'],
            [true, 'ABCDEF'],
            [true, '0123456789'],
            [true, '0123456789AbCdEf'],
            [false, '0123456789x'],
            [false, 'ABCDEFx'],
            [true, 'abcdef', 'UTF-8'],
            [true, 'ABCDEF', 'UTF-8'],
            [true, '0123456789', 'UTF-8'],
            [true, '0123456789AbCdEf', 'UTF-8'],
            [false, '0123456789x', 'UTF-8'],
            [false, 'ABCDEFx', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider countSubstrProvider()
     *
     * @param int         $expected
     * @param string      $str
     * @param string      $substring
     * @param bool        $caseSensitive
     * @param string|null $encoding
     */
    public function testCountSubstr(
        int $expected,
        string $str,
        string $substring,
        bool $caseSensitive = true,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->countSubstr($substring, $caseSensitive);
        $this->assertTrue(is_int($result));
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array[]
     */
    public function countSubstrProvider(): array
    {
        return [
            [0, '', 'foo'],
            [0, 'foo', 'bar'],
            [1, 'foo bar', 'foo'],
            [2, 'foo bar', 'o'],
            [0, '', 'f????', true, 'UTF-8'],
            [0, 'f????', 'b????', true, 'UTF-8'],
            [1, 'f???? b????', 'f????', true, 'UTF-8'],
            [2, 'f?????? b????', '??', true, 'UTF-8'],
            [0, 'f?????? b????', '??', true, 'UTF-8'],
            [0, 'foo', 'BAR', false],
            [1, 'foo bar', 'FOo', false],
            [2, 'foo bar', 'O', false],
            [1, 'f???? b????', 'f????', false, 'UTF-8'],
            [2, 'f?????? b????', '??', false, 'UTF-8'],
            [2, '????????????????????', '??', false, 'UTF-8'],
        ];
    }

    /**
     * @dataProvider replaceProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $search
     * @param string      $replacement
     * @param string|null $encoding
     */
    public function testReplace(
        string $expected,
        string $str,
        string $search,
        string $replacement,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->replace($search, $replacement);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function replaceProvider(): array
    {
        return [
            ['', '', '', ''],
            ['foo', '', '', 'foo'],
            ['foo', '\s', '\s', 'foo'],
            ['foo bar', 'foo bar', '', ''],
            ['foo bar', 'foo bar', 'f(o)o', '\1'],
            ['\1 bar', 'foo bar', 'foo', '\1'],
            ['bar', 'foo bar', 'foo ', ''],
            ['far bar', 'foo bar', 'foo', 'far'],
            ['bar bar', 'foo bar foo bar', 'foo ', ''],
            ['', '', '', '', 'UTF-8'],
            ['f????', '', '', 'f????', 'UTF-8'],
            ['f????', '\s', '\s', 'f????', 'UTF-8'],
            ['f???? b????', 'f???? b????', '', '', 'UTF-8'],
            ['b????', 'f???? b????', 'f???? ', '', 'UTF-8'],
            ['far b????', 'f???? b????', 'f????', 'far', 'UTF-8'],
            ['b???? b????', 'f???? b???? f???? b????', 'f???? ', '', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider regexReplaceProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param string      $pattern
     * @param string      $replacement
     * @param string      $options
     * @param string|null $encoding
     */
    public function testRegexReplace(
        string $expected,
        string $str,
        string $pattern,
        string $replacement,
        string $options = 'msr',
        ?string $encoding = null
    ) {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->regexReplace($pattern, $replacement, $options);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return string[][]
     */
    public function regexReplaceProvider(): array
    {
        return [
            ['', '', '', ''],
            ['bar', 'foo', 'f[o]+', 'bar'],
            ['o bar', 'foo bar', 'f(o)o', '\1'],
            ['bar', 'foo bar', 'f[O]+\s', '', 'i'],
            ['foo', 'bar', '[[:alpha:]]{3}', 'foo'],
            ['', '', '', '', 'msr', 'UTF-8'],
            ['b????', 'f???? ', 'f[????]+\s', 'b????', 'msr', 'UTF-8'],
            ['f????', 'f??', '(??)', '\\1??', 'msr', 'UTF-8'],
            ['f????', 'b????', '[[:alpha:]]{3}', 'f????', 'msr', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider htmlEncodeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $flags
     * @param string|null $encoding
     */
    public function testHtmlEncode(
        string $expected,
        string $str,
        int $flags = ENT_COMPAT,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->htmlEncode($flags);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    public function htmlEncodeProvider(): array
    {
        return [
            ['&amp;', '&'],
            ['&quot;', '"'],
            ['&#039;', "'", ENT_QUOTES],
            ['&lt;', '<'],
            ['&gt;', '>'],
        ];
    }

    /**
     * @dataProvider htmlDecodeProvider()
     *
     * @param string      $expected
     * @param string      $str
     * @param int         $flags
     * @param string|null $encoding
     */
    public function testHtmlDecode(
        string $expected,
        string $str,
        int $flags = ENT_COMPAT,
        ?string $encoding = null
    ): void {
        $stringy = StringObject::create($str, $encoding);
        $result = $stringy->htmlDecode($flags);
        $this->assertStringType($result);
        $this->assertEquals($expected, $result->getScalarValue());
        $this->assertEquals($str, $stringy->getScalarValue());
    }

    /**
     * @return array
     */
    public function htmlDecodeProvider(): array
    {
        return [
            ['&', '&amp;'],
            ['"', '&quot;'],
            ["'", '&#039;', ENT_QUOTES],
            ['<', '&lt;'],
            ['>', '&gt;'],
        ];
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue((new StringObject(''))->isEmpty());
    }

    public function testCanBeBoxed(): void
    {
        $string = new StringObject();
        StringObject::box($string);
        /** @var StringObject $string */
        $string = 'foo';
        $this->assertInstanceOf(StringObject::class, $string);
        $this->assertEquals('foo', $string->getScalarValue());
        $this->assertEquals('foo', (string) $string);
    }

    public function testCanBeBoxedFromPrimitive(): void
    {
        /** @var StringObject $string */
        $string = 'foo';
        StringObject::box($string);
        $this->assertSame('Foo', (string) $string->classify());
        $string = 'bar';
        $this->assertSame('Bar', (string) $string->classify());
    }

    public function testFailsWhenUnboxedStartedAsPrimitive(): void
    {
        /** @var StringObject $string */
        $string = 'foo';
        StringObject::box($string);
        $this->expectException(TypeError::class);
        $string = new stdClass();
    }
}
