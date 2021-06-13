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

use function array_keys;
use function array_search;
use function array_values;
use function count;
use function current;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use function end;
use Exception;
use function is_array;
use function is_numeric;
use function is_string;
use function key;
use LogicException;
use function next;
use PHPUnit\Framework\TestCase;
use function reset;
use stdClass;
use TypeError;
use Typing\Exception\InvalidTransformationException;
use Typing\Model\Primitive;
use Typing\Type\Collection;
use Typing\Type\IntObject;
use Typing\Type\StringObject;

/**
 * Class CollectionTest.
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    protected function setUp(): void
    {
        $this->collection = new Collection();
    }

    public function testIssetAndUnset(): void
    {
        self::assertFalse(isset($this->collection[0]));
        $this->collection->add('testing');
        self::assertTrue(isset($this->collection[0]));
        unset($this->collection[0]);
        self::assertFalse(isset($this->collection[0]));
    }

    public function testRemovingNonExistentEntryReturnsNull(): void
    {
        self::assertEquals(null, $this->collection->remove('testing_does_not_exist'));
    }

    public function testExists(): void
    {
        $this->collection->add('one');
        $this->collection->add('two');
        $exists = $this->collection->exists(static function ($k, $e) {
            return 'one' === $e;
        });
        self::assertTrue($exists);
        $exists = $this->collection->exists(static function ($k, $e) {
            return 'other' === $e;
        });
        self::assertFalse($exists);

        $elements = [1, 'A' => 'a', 2, 'null' => null, 3, 'A2' => 'a', 'zero' => 0];
        $localCollection = $this->buildCollection($elements);

        self::assertTrue($localCollection->exists(static function ($key, $element) {
            return 'A' === $key && 'a' === $element;
        }), 'Element exists');

        self::assertFalse($localCollection->exists(static function ($key, $element) {
            return 'non-existent' === $key && 'non-existent' === $element;
        }), 'Element not exists');
    }

    public function testMap(): void
    {
        $this->collection->add(1);
        $this->collection->add(2);
        $res = $this->collection->map(static function ($e) {
            return $e * 2;
        });
        self::assertEquals([2, 4], $res->toArray());
    }

    public function testFilter(): void
    {
        $this->collection->add(1);
        $this->collection->add('foo');
        $this->collection->add(3);
        $res = $this->collection->filter(static function ($e) {
            return is_numeric($e);
        });
        self::assertEquals([0 => 1, 2 => 3], $res->toArray());
    }

    public function testFilterByValueAndKey(): void
    {
        $this->collection->add(1);
        $this->collection->add('foo');
        $this->collection->add(3);
        $this->collection->add(4);
        $this->collection->add(5);
        $res = $this->collection->filter(static function ($v, $k) {
            return is_numeric($v) && 0 === $k % 2;
        });
        self::assertSame([0 => 1, 2 => 3, 4 => 5], $res->toArray());
    }

    public function testFirstAndLast(): void
    {
        $this->collection->add('one');
        $this->collection->add('two');

        self::assertEquals('one', $this->collection->first());
        self::assertEquals('two', $this->collection->last());
    }

    public function testArrayAccess(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';

        self::assertEquals('one', $this->collection[0]);
        self::assertEquals('two', $this->collection[1]);

        unset($this->collection[0]);
        self::assertEquals(1, $this->collection->count());
    }

    public function testSearch(): void
    {
        $this->collection[0] = 'test';
        self::assertEquals(0, $this->collection->indexOf('test'));
    }

    public function testGetKeys(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        self::assertEquals([0, 1], $this->collection->getKeys());
    }

    public function testGetValues(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        self::assertEquals(['one', 'two'], $this->collection->getValues());
    }

    public function testCount(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        self::assertEquals(2, $this->collection->count());
        self::assertCount(2, $this->collection);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function testForAll(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        self::assertEquals(true, $this->collection->forAll(static function ($k, $e) {
            return is_string($e);
        }));
        self::assertEquals(false, $this->collection->forAll(static function ($k, $e) {
            return is_array($e);
        }));
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function testPartition(): void
    {
        $this->collection[] = true;
        $this->collection[] = false;
        $partition = $this->collection->partition(static function ($k, $e) {
            return true === $e;
        });
        self::assertEquals(true, $partition[0][0]);
        self::assertEquals(false, $partition[1][0]);
    }

    public function testClear(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->collection->clear();
        self::assertEquals(true, $this->collection->isEmpty());
    }

    public function testRemove(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $el = $this->collection->remove(0);

        self::assertEquals('one', $el);
        self::assertEquals(false, $this->collection->contains('one'));
        self::assertNull($this->collection->remove(0));
    }

    public function testRemoveElement(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';

        self::assertTrue($this->collection->removeElement('two'));
        self::assertFalse($this->collection->contains('two'));
        self::assertFalse($this->collection->removeElement('two'));
    }

    public function testSlice(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->collection[] = 'three';

        $slice = $this->collection->slice(0, 1);
        self::assertIsArray($slice);
        self::assertEquals(['one'], $slice);

        $slice = $this->collection->slice(1);
        self::assertEquals([1 => 'two', 2 => 'three'], $slice);

        $slice = $this->collection->slice(1, 1);
        self::assertEquals([1 => 'two'], $slice);
    }

    public function testCanRemoveNullValuesByKey(): void
    {
        $this->collection->add(null);
        $this->collection->remove(0);
        self::assertTrue($this->collection->isEmpty());
    }

    public function testCanVerifyExistingKeysWithNullValues(): void
    {
        $this->collection->set('key', null);
        self::assertTrue($this->collection->containsKey('key'));
    }

    public function testToString(): void
    {
        $this->collection->add('testing');
        self::assertTrue(is_string($this->collection->toString()));
    }

    /**
     * @group DDC-1637
     */
    public function testMatching(): void
    {
        $this->fillMatchingFixture();

        $col = $this->collection->matching(new Criteria(Criteria::expr()->eq('foo', 'bar')));
        self::assertInstanceOf(Collection::class, $col);
        self::assertNotSame($col, $this->collection);
        self::assertCount(1, $col);
    }

    /**
     * @group DDC-1637
     */
    public function testMatchingOrdering(): void
    {
        $this->fillMatchingFixture();

        $col = $this->collection->matching(new Criteria(null, ['foo' => 'DESC']));

        self::assertInstanceOf(Collection::class, $col);
        self::assertNotSame($col, $this->collection);
        self::assertCount(2, $col);
        self::assertEquals('baz', $col->first()->foo);
        self::assertEquals('bar', $col->last()->foo);
    }

    /**
     * @group DDC-1637
     */
    public function testMatchingSlice(): void
    {
        $this->fillMatchingFixture();

        $col = $this->collection->matching(new Criteria(null, null, 1, 1));

        self::assertInstanceOf(Collection::class, $col);
        self::assertNotSame($col, $this->collection);
        self::assertCount(1, $col);
        self::assertEquals('baz', $col[0]->foo);
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testToArray(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame($elements, $localCollection->toArray());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testFirst(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);
        self::assertSame(reset($elements), $localCollection->first());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testLast(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);
        self::assertSame(end($elements), $localCollection->last());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testKey(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame(key($elements), $localCollection->key());

        next($elements);
        $localCollection->next();

        self::assertSame(key($elements), $localCollection->key());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testNext(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        while (true) {
            $localCollectionNext = $localCollection->next();
            $arrayNext = next($elements);

            if (!$localCollectionNext || !$arrayNext) {
                break;
            }

            self::assertSame(
                $arrayNext,
                $localCollectionNext,
                'Returned value of ArrayCollection::next() and next() not match'
            );
            self::assertSame(key($elements), $localCollection->key(), 'Keys not match');
            self::assertSame(current($elements), $localCollection->current(), 'Current values not match');
        }
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testCurrent(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame(current($elements), $localCollection->current());

        next($elements);
        $localCollection->next();

        self::assertSame(current($elements), $localCollection->current());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testGetKeysSameAsInternal(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame(array_keys($elements), $localCollection->getKeys());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testGetValuesSameAsInternal(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame(array_values($elements), $localCollection->getValues());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     */
    public function testCountSameAsInternal(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);

        self::assertSame(count($elements), $localCollection->count());
    }

    /**
     * @param mixed[] $elements
     *
     * @dataProvider provideDifferentElements
     *
     * @throws Exception
     */
    public function testIterator(array $elements): void
    {
        $localCollection = $this->buildCollection($elements);
        $iterations = 0;
        foreach ($localCollection->getIterator() as $key => $item) {
            self::assertSame($elements[$key], $item, 'Item '.$key.' not match');
            ++$iterations;
        }

        self::assertEquals($iterations, count($elements), 'Number of iterations not match');
    }

    /**
     * @psalm-return array<string, array{mixed[]}>
     *
     * @return array
     */
    public function provideDifferentElements(): array
    {
        return [
            'indexed' => [[1, 2, 3, 4, 5]],
            'associative' => [['A' => 'a', 'B' => 'b', 'C' => 'c']],
            'mixed' => [['A' => 'a', 1, 'B' => 'b', 2, 3]],
        ];
    }

    public function testRemoveWithUnset(): void
    {
        $elements = [1, 'A' => 'a', 2, 'B' => 'b', 3];
        $localCollection = $this->buildCollection($elements);

        self::assertEquals(1, $localCollection->remove(0));
        unset($elements[0]);

        self::assertEquals(null, $localCollection->remove('non-existent'));
        unset($elements['non-existent']);

        self::assertEquals(2, $localCollection->remove(1));
        unset($elements[1]);

        self::assertEquals('a', $localCollection->remove('A'));
        unset($elements['A']);

        self::assertEquals($elements, $localCollection->toArray());
    }

    public function testRemoveElementWithUnset(): void
    {
        $elements = [1, 'A' => 'a', 2, 'B' => 'b', 3, 'A2' => 'a', 'B2' => 'b'];
        $localCollection = $this->buildCollection($elements);

        self::assertTrue($localCollection->removeElement(1));
        unset($elements[0]);

        self::assertFalse($localCollection->removeElement('non-existent'));

        self::assertTrue($localCollection->removeElement('a'));
        unset($elements['A']);

        self::assertTrue($localCollection->removeElement('a'));
        unset($elements['A2']);

        self::assertEquals($elements, $localCollection->toArray());
    }

    public function testContainsKey(): void
    {
        $elements = [1, 'A' => 'a', 2, 'null' => null, 3, 'A2' => 'a', 'B2' => 'b'];
        $localCollection = $this->buildCollection($elements);

        self::assertTrue($localCollection->containsKey(0), 'Contains index 0');
        self::assertTrue($localCollection->containsKey('A'), 'Contains key "A"');
        self::assertTrue($localCollection->containsKey('null'), 'Contains key "null", with value null');
        self::assertFalse($localCollection->containsKey('non-existent'), "Doesn't contain key");
        $this->collection[5] = 'five';
        self::assertTrue($this->collection->containsKey(5));
    }

    public function testEmpty(): void
    {
        $localCollection = $this->buildCollection();
        self::assertTrue($localCollection->isEmpty(), 'Empty collection');

        $localCollection->add(1);
        self::assertFalse($localCollection->isEmpty(), 'Not empty collection');
    }

    public function testContains(): void
    {
        $elements = [1, 'A' => 'a', 2, 'null' => null, 3, 'A2' => 'a', 'zero' => 0];
        $localCollection = $this->buildCollection($elements);

        self::assertTrue($localCollection->contains(0), 'Contains Zero');
        self::assertTrue($localCollection->contains('a'), 'Contains "a"');
        self::assertTrue($localCollection->contains(null), 'Contains Null');
        self::assertFalse($localCollection->contains('non-existent'), "Doesn't contain an element");

        $this->collection[0] = 'test';
        self::assertTrue($this->collection->contains('test'));
    }

    public function testIndexOf(): void
    {
        $elements = [1, 'A' => 'a', 2, 'null' => null, 3, 'A2' => 'a', 'zero' => 0];
        $localCollection = $this->buildCollection($elements);

        self::assertSame(
            array_search(2, $elements, true),
            $localCollection->indexOf(2),
            'Index of 2'
        );
        self::assertSame(
            array_search(null, $elements, true),
            $localCollection->indexOf(null),
            'Index of null'
        );
        self::assertSame(
            array_search('non-existent', $elements, true),
            $localCollection->indexOf('non-existent'),
            'Index of non existent'
        );
    }

    public function testGet(): void
    {
        $elements = [1, 'A' => 'a', 2, 'null' => null, 3, 'A2' => 'a', 'zero' => 0];
        $localCollection = $this->buildCollection($elements);

        self::assertSame(2, $localCollection->get(1), 'Get element by index');
        self::assertSame('a', $localCollection->get('A'), 'Get element by name');
        self::assertSame(null, $localCollection->get('non-existent'), 'Get non existent element');

        $this->collection[0] = 'test';
        self::assertEquals('test', $this->collection->get(0));
    }

    public function testMatchingWithSortingPreservesKeys(): void
    {
        $object1 = new stdClass();
        $object2 = new stdClass();

        $object1->sortField = 2;
        $object2->sortField = 1;

        $localCollection = $this->buildCollection([
            'object1' => $object1,
            'object2' => $object2,
        ]);

        if (!$this->isSelectable($localCollection)) {
            $this->markTestSkipped('Collection does not support Selectable interface');
        }

        self::assertSame(
            [
                'object2' => $object2,
                'object1' => $object1,
            ],
            $localCollection
                ->matching(new Criteria(null, ['sortField' => Criteria::ASC]))
                ->toArray()
        );
    }

    public function testMultiColumnSortAppliesAllSorts(): void
    {
        $localCollection = $this->buildCollection([
            ['foo' => 1, 'bar' => 2],
            ['foo' => 2, 'bar' => 4],
            ['foo' => 2, 'bar' => 3],
        ]);

        $expected = [
            1 => ['foo' => 2, 'bar' => 4],
            2 => ['foo' => 2, 'bar' => 3],
            0 => ['foo' => 1, 'bar' => 2],
        ];

        if (!$this->isSelectable($localCollection)) {
            $this->markTestSkipped('Collection does not support Selectable interface');
        }

        self::assertSame(
            $expected,
            $localCollection
                ->matching(new Criteria(null, ['foo' => Criteria::DESC, 'bar' => Criteria::DESC]))
                ->toArray()
        );
    }

    public function testImplode()
    {
        $localCollection = new Collection(['foo', 'bar', 'baz']);
        $this->assertEquals(StringObject::create('foo, bar, baz'), $localCollection->implode(', '));
    }

    public function testTypedCollectionFailsOnInvalidType()
    {
        $this->expectException(TypeError::class);
        new Collection([1, 2, 3, 'nan'], IntObject::class);
    }

    public function testPrimitiveTypedCollectionFailsOnInvalidType()
    {
        $this->expectException(TypeError::class);
        $this->buildCollection([1, 2, 3, 'nan'], (string) Primitive::INT());
    }

    public function testCastsToCompatibleTypeWhenPossible()
    {
        $localCollection = $this->buildCollection([1, 2], IntObject::class);
        $this->assertEquals([new IntObject(1), new IntObject(2)], $localCollection->toArray());
        $localCollection = $this->buildCollection([1, 2, '3'], Primitive::INT());
        $this->assertEquals([1, 2, 3], $localCollection->toArray());
        $localCollection = $this->buildCollection([1.2, 2.1, '4.5'], Primitive::FLOAT());
        $this->assertEquals([1.2, 2.1, 4.5], $localCollection->toArray());
        $this->assertEquals((string) Primitive::FLOAT(), $localCollection->getType());
    }

    public function testCastStringFailsWhenInvalid()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Object of class stdClass could not be converted to string');
        $localCollection = $this->buildCollection([tmpfile(), new stdClass()]);
        $localCollection->toString();
    }

    public function testMerge()
    {
        $localCollection = $this->buildCollection(['foo']);
        $expected = $this->buildCollection(['foo', 'bar']);
        $this->assertEquals($expected->toArray(), $localCollection->merge($expected)->toArray());

        $localCollection = $this->buildCollection(['foo'], StringObject::class);
        $expected = $this->buildCollection([new StringObject('foo'), 'bar', 'bar'], StringObject::class);
        $this->assertEquals(
            $expected,
            $localCollection->merge($this->buildCollection(['bar', 'bar'], StringObject::class), true)
        );
    }

    public function testMergeWithDupes()
    {
        $localCollection = $this->buildCollection(['foo']);
        $this->assertEquals(
            $this->buildCollection(['foo', 'foo', 'bar']),
            $localCollection->merge(new Collection(['foo', 'bar']), true)
        );
    }

    public function testFromPrimitive()
    {
        $resource = tmpfile();
        $this->assertEquals([10.0], (Collection::fromPrimitive(10.0))->toArray());
        $this->assertEquals([100], (Collection::fromPrimitive(100))->toArray());
        $this->assertEquals(['bar'], (Collection::fromPrimitive('bar'))->toArray());
        $this->assertEquals([false], (Collection::fromPrimitive(false))->toArray());
        $this->assertEquals([new stdClass()], (Collection::fromPrimitive(new stdClass()))->toArray());
        $this->assertEquals([$resource], (Collection::fromPrimitive($resource))->toArray());
        $this->assertEquals(['qux'], (Collection::fromPrimitive(['qux']))->toArray());
        $this->assertEquals(['foo'], (Collection::fromPrimitive(new StringObject('foo')))->toArray());
        $this->assertEquals(['xyz'], (Collection::fromPrimitive(new Collection(['xyz'])))->toArray());
    }

    public function testBadFrom()
    {
        $this->expectException(InvalidTransformationException::class);
        $this->expectExceptionMessageMatches('/Could not transform null to Collection/');
        Collection::fromPrimitive(null);
    }

    public function testToStingType()
    {
        $this->assertEquals(new StringObject('bar, baz'), (new Collection(['bar', 'baz']))->toStringObject());
    }

    public function testBoxable()
    {
        $localCollection = new Collection(['foo', 'bar'], 'string');
        Collection::box($localCollection);
        $this->assertEquals('foo, bar', $localCollection->toString());
        /** @var Collection $localCollection */
        $localCollection = ['baz', 'qux'];
        $this->assertEquals('baz, qux', $localCollection->toString());
        $this->assertEquals(['baz', 'qux'], $localCollection->toArray());
        $localCollection = ['foo', 'bar', 'baz', 'qux'];
        $this->assertEquals(4, $localCollection->toInt()); //"Cast" to int returns count
        $localCollection = false;
        $this->assertEquals([false], $localCollection->toArray());
    }

    public function testIsAssociative()
    {
        $localCollection = new Collection(['foo' => 'bar']);
        $this->assertTrue($localCollection->isAssociative());
        $localCollection = new Collection([]);
        $this->assertFalse($localCollection->isAssociative());
    }

    public function testUnique()
    {
        $localCollection = new Collection(['foo', 'foo', 'bar']);
        $this->assertEquals((new Collection(['foo', 'bar']))->toArray(), $localCollection->unique()->toArray());
        $localCollection = new Collection(['ber' => 'foo', 'qux' => 'foo', 'bar' => 'baaz']);
        $this->assertEquals(
            (new Collection(['ber' => 'foo', 'bar' => 'baaz']))->toArray(),
            $localCollection->unique()->toArray()
        )
        ;
    }

    public function testUniqueFailsWhenCannotBeUnique()
    {
        $localCollection = new Collection([false, false, true]);
        $this->expectException(LogicException::class);
        $localCollection->unique();
    }

    public function testGuessType()
    {
        $strCollection = new Collection(['foo', 'bar']);
        $this->assertEquals((string) Primitive::STRING(), $strCollection->getType());
        $intCollection = new Collection([1, 2]);
        $this->assertEquals((string) Primitive::INT(), $intCollection->getType());
        $objCollection = new Collection([new stdClass(), new stdClass()], stdClass::class);
        $this->assertEquals(stdClass::class, $objCollection->getType());
        $this->assertNull((new Collection(['foo', false, '1', new stdClass()]))->getType());
    }

    public function testUnshift()
    {
        $localCollection = new Collection(['two', 'three']);
        $this->assertTrue($localCollection->unshift('one'));
        $this->assertEquals(['one', 'two', 'three'], $localCollection->toArray());
    }

    public function testConvertsTypes()
    {
        $localCollection = new Collection([123, 223, 311], 'string');
        $this->assertEquals(['123', '223', '311'], $localCollection->toArray());
        $localCollection = new Collection(['yes', '1', 'true', 'no'], (string) Primitive::BOOL());
        $this->assertEquals([true, true, true, false], $localCollection->toArray());
    }

    public function testFailsConvertUnmappedBool()
    {
        $this->expectException(TypeError::class);
        new Collection(['nope'], (string) Primitive::BOOL());
    }

    public function testHasType()
    {
        $localCollection = new Collection([1, false, 'string', new stdClass()]);
        $this->assertTrue($localCollection->hasType(stdClass::class));
        $this->assertTrue($localCollection->hasType((string) Primitive::INT()));
        $this->assertTrue($localCollection->hasType((string) Primitive::BOOL()));
        $this->assertTrue($localCollection->hasType((string) Primitive::STRING()));
    }

    protected function fillMatchingFixture(): void
    {
        $std1 = new stdClass();
        $std1->foo = 'bar';
        $this->collection[] = $std1;

        $std2 = new stdClass();
        $std2->foo = 'baz';
        $this->collection[] = $std2;
    }

    /**
     * @param array       $elements
     * @param string|null $type
     *
     * @return Collection
     */
    protected function buildCollection(array $elements = [], ?string $type = null): Collection
    {
        return new Collection($elements, $type);
    }

    /**
     * @param mixed $obj
     *
     * @return bool
     */
    protected function isSelectable(mixed $obj): bool
    {
        return $obj instanceof Selectable;
    }
}
