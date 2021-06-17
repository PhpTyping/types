<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Typing\Type;

use function array_filter;
use const ARRAY_FILTER_USE_BOTH;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_search;
use function array_slice;
use function array_values;
use ArrayIterator;
use Closure;
use function count;
use function current;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Selectable;
use function end;
use function in_array;
use function key;
use LogicException;
use function next;
use function reset;
use Symfony\Polyfill\Ctype\Ctype;
use Throwable;
use TypeError;
use Typing\Exception\InvalidTransformationException;
use Typing\Exception\InvalidTypeCastException;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\IntCastableTrait;
use Typing\Type\Traits\StringCastableTrait;
use function uasort;

/**
 * Class Collection.
 *
 * A Collection is a TypeInterface implementation that wraps around a regular PHP array.
 * This class implements Doctrine's Collection and is analogous to Doctrine's
 * ArrayCollection, with extra functionality.
 *
 * This class can be extended to create type specific collections. (either primitive or compound),
 * You can also use this class as-is as either typeless or typed collection.
 *
 *
 * Examples:
 *
 * $myTypedCollection = new Collection(elements: [...], type: PathToMyType::class);
 * $myCollection = new Collection([...]);
 *
 * @template-implements Selectable<int|string, mixed>
 */
class Collection implements Selectable, TypedCollectionInterface, PrimitiveLoaderInterface, BoxableInterface //NOSONAR
{
    use BoxableTrait;
    use IntCastableTrait;
    use StringCastableTrait;

    /**
     * @var string|null
     */
    private ?string $type;

    /**
     * An array containing the entries of this collection.
     *
     * @var mixed[]
     */
    private array $elements;

    /**
     * @param array<string|int, mixed> $elements
     * @param string|null              $type
     */
    public function __construct(array $elements = [], ?string $type = null)
    {
        $this->type = $type;
        $this->elements = array_map(function ($element) {
            return $this->getIntendedValue($element);
        }, $elements);
    }

    /**
     * Prepends one or more elements to the beginning of the collection.
     *
     * @param mixed $element
     *
     * @return bool
     */
    public function unshift(mixed $element): bool
    {
        array_unshift($this->elements, $this->getIntendedValue($element));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function first(): mixed
    {
        return reset($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function last(): mixed
    {
        return end($this->elements);
    }

    /**
     * @return bool
     */
    public function isAssociative(): bool
    {
        if ([] === $this->toArray()) {
            return false;
        }

        return array_keys($this->toArray()) !== range(start: 0, end: count($this->toArray()) - 1);
    }

    /**
     * @return int|string|null
     */
    public function key(): int | string | null
    {
        return key($this->elements);
    }

    /**
     * @return mixed
     */
    public function next(): mixed
    {
        return next($this->elements);
    }

    /**
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->elements);
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     */
    public function remove(mixed $key): mixed
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * @param mixed $element
     *
     * @return bool
     */
    public function removeElement(mixed $element): bool
    {
        $key = array_search($element, $this->elements, true);

        if (false === $key) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param string|int $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param int|string $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param string|int|null $offset
     * @param mixed           $value
     *
     * @return bool
     */
    public function offsetSet(mixed $offset, mixed $value): bool
    {
        if (!isset($offset)) {
            return $this->add($value);
        }

        $this->set($offset, $value);

        return true;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param string|int $offset
     *
     * @return mixed
     */
    public function offsetUnset(mixed $offset): mixed
    {
        return $this->remove($offset);
    }

    /**
     * @param int|string $key
     *
     * @return bool
     */
    public function containsKey(mixed $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * @param mixed $element
     *
     * @return bool
     */
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * @param Closure $p
     *
     * @return bool
     */
    public function exists(Closure $p): bool
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element): false | int | string
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key): mixed
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function getValues(): array
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|int $key   the key/index of the element to set
     * @param mixed      $value the element to set
     */
    public function set(mixed $key, mixed $value)
    {
        $this->elements[$key] = $this->getIntendedValue($value);
    }

    /**
     * This add method can take any value and converts it to the Type that the Collection holds, if any is set.
     *
     * {@inheritDoc}
     */
    public function add(mixed $element): bool
    {
        $this->elements[] = $this->getIntendedValue($element);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     *
     * @return ArrayIterator<int|string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function map(Closure $func): static
    {
        return $this->createFrom(array_map($func, $this->elements), $this->type);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function filter(Closure $p): static
    {
        return $this->createFrom(array_filter($this->elements, $p, ARRAY_FILTER_USE_BOTH), $this->type);
    }

    /**
     * {@inheritdoc}
     */
    public function forAll(Closure $p): bool
    {
        foreach ($this->elements as $key => $element) {
            if (!$p($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return static<int, static>
     */
    public function partition(Closure $p): static
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return $this->createFrom(
            [
            $this->createFrom($matches, $this->type),
            $this->createFrom($noMatches, $this->type),
            ],
            static::class
        );
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->elements = [];
    }

    /**
     * {@inheritDoc}
     */
    public function slice($offset, $length = null): array
    {
        return array_slice($this->elements, $offset, $length, true);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function matching(Criteria $criteria): static
    {
        $expr = $criteria->getWhereExpression();
        $filtered = $this->elements;

        if ($expr) {
            $visitor = new ClosureExpressionVisitor();
            $filter = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }

        $orderings = $criteria->getOrderings();

        if ($orderings) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = ClosureExpressionVisitor::sortByField($field, Criteria::DESC === $ordering ? -1 : 1, $next);
            }

            uasort($filtered, $next);
        }

        $offset = $criteria->getFirstResult();
        $length = $criteria->getMaxResults();

        if ($offset || $length) {
            $filtered = array_slice($filtered, (int) $offset, $length);
        }

        return $this->createFrom($filtered, $this->type);
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     *
     * @return static
     *
     * @throws LogicException when collection is untyped or collection type does not contain __toString method
     */
    public function unique(): static
    {
        $closure = function ($k, $value) {
            // Uniqueness on a bool array would be pointless.
            return is_scalar($value) && !is_bool($value);
        };

        if ((null !== $this->type && method_exists($this->type, '__toString')) || $this->forAll($closure)) {
            if (false === $this->isAssociative()) {
                return $this->createFrom(array_values(array_unique($this->elements, SORT_STRING)), $this->type);
            }

            return $this->createFrom(array_unique($this->elements, SORT_STRING), $this->type);
        }

        throw new LogicException('Collection instance is not typed, or type has no string support.');
    }

    /**
     * @param CollectionInterface<int|string, mixed> $collection
     * @param bool                                   $keepDupes
     * @param int|null                               $sortingMode
     *
     * @return static<int|string, mixed>
     */
    public function merge(
        CollectionInterface $collection,
        bool $keepDupes = false,
        ?int $sortingMode = null
    ): static {
        if ($keepDupes) {
            $merged = array_merge(
                $this->toArray(),
                $collection->toArray()
            );

            return $this->createFrom(
                $this->isAssociative() ? $merged : array_values($merged),
                $this->type
            );
        }

        $merged = array_unique(
            array_merge(
                $this->toArray(),
                $collection->toArray()
            ),
            $sortingMode ?? SORT_STRING
        );

        return $this->createFrom(
            $this->isAssociative() ? $merged : array_values($merged),
            $this->type
        );
    }

    /**
     * @param string $delimiter
     *
     * @return StringObject
     */
    public function implode(string $delimiter): StringObject
    {
        return StringObject::create(implode($delimiter, $this->toArray()));
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        if ($this->isTyped()) {
            return $this->type;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isTyped(): bool
    {
        return !empty($this->type) || null !== $this->guessType();
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isOfType(string $type): bool
    {
        if ($this->isTyped()) {
            return $this->type === $type;
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType(string $type): bool
    {
        $clone = clone $this;
        $types = $clone->map(
            function (mixed $element): string {
                if (is_object($element)) {
                    return $element::class;
                }

                return gettype($element);
            }
        );

        return $types->contains($type);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function fromPrimitive($mixed): static
    {
        return new static(self::asArray($mixed));
    }

    /**
     * @param Primitive|null $primitive
     *
     * @return int|array<int|string, mixed>|string
     */
    protected function getScalar(?Primitive $primitive = null): int | array | string
    {
        $primitive = $primitive ?? Primitive::ARRAY();
        switch ((string) $primitive) {
            case (string) Primitive::INT():
                return $this->count();
            case (string) Primitive::STRING():
                try {
                    return StringObject::fromPrimitive($this->toArray())->getScalarValue();
                } catch (Throwable $e) {
                    // Let's wrap the throwable in an exception, in case it's an Error object.
                }
                throw new TypeError(message: $e->getMessage(), code: $e->getCode(), previous: $e);
            default:
                throw new InvalidTypeCastException($this, $primitive);
        }
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array<int|string, mixed> $elements elements
     * @param string|null              $type
     *
     * @return static
     *
     * @phpstan-template K
     */
    protected function createFrom(array $elements, ?string $type = null): static
    {
        return new static($elements, $type);
    }

    /**
     * @param mixed|null $value
     *
     * @return static
     */
    protected static function createStatic(mixed $value = null): static
    {
        return static::fromPrimitive($value);
    }

    /**
     * Returns a mixed variable as an array.
     *
     * @param mixed $mixed
     *
     * @return array<int|string, mixed>
     */
    private static function asArray(mixed $mixed): array
    {
        if ($mixed instanceof CollectionInterface || $mixed instanceof CollectionCastableInterface) {
            return $mixed->toArray();
        }

        $type = strtolower(gettype($mixed));

        return match ($type) {
            'integer', 'double', 'float', 'string', 'object', 'resource', 'boolean' => [$mixed],
            'array' => $mixed,
            default => throw new InvalidTransformationException($type, static::class),
        };
    }

    /**
     * If this is typed collection, it checks to see if value passed is a type of "$this->type".
     * If it is,.
     *
     * @param mixed $value Could be primitive, could be object...who knows.
     *
     * @return mixed
     */
    private function getIntendedValue(mixed &$value): mixed //NOSONAR
    {
        $type = gettype($value); //NOSONAR
        if (null !== $this->type && ((!$value instanceof $this->type) && ($this->type !== $type))) {
            try {
                if (class_exists($this->type)) {
                    // try to do a simple instance.
                    return new $this->type($value);
                }

                // For scalars and arrays we run our own rules. PHP Juggling will convert "NaN" to 0.
                if ((is_scalar($value) || is_array($value)) &&
                    !$this->isValidForType($value, Primitive::from($this->type))) {
                    throw new TypeError();
                }

                if ($this->type === (string) Primitive::BOOL()) {
                    return (bool) BooleanObject::STRING_MAP[$value];
                }

                settype($value, $this->type);

                return $value;
            } catch (Throwable $e) {
                // The calling code is trying to either create a typed collection of an object that is complex
                // or there's an incompatible object in the collection.
                $typeFound = gettype($value);
                throw new TypeError(
                    "Could not convert invalid type '{$typeFound}' in ".
                    "Typed Collection<{$this->type}>. {$e->getMessage()}"
                );
            }
        }

        return $value;
    }

    /**
     * @param mixed     $value
     * @param Primitive $targetType
     *
     * @return bool
     */
    private function isValidForType(mixed $value, Primitive $targetType): bool //NOSONAR
    {
        if ((string) Primitive::INT() === (string) $targetType ||
            (string) Primitive::FLOAT() === (string) $targetType) {
            if ((string) floatval($value) === (string) $value) {
                return (bool) is_numeric($value);
            }

            if ($value >= 0 && is_string($value)) {
                return (bool) Ctype::ctype_digit($value);
            }

            return (bool) is_numeric($value);
        }

        if ((string) Primitive::STRING() === (string) $targetType) {
            return (bool) Ctype::ctype_alnum(strval($value));
        }

        if ((string) Primitive::BOOL() === (string) $targetType) {
            if ((string) boolval($value) === (string) $value) {
                return (bool) $value;
            }

            if (Ctype::ctype_alnum(strval($value)) && in_array($value, array_keys(BooleanObject::STRING_MAP))) {
                return true;
            }

            return is_bool($value);
        }

        // For arrays we just let PHP handle it.
        return true;
    }

    /**
     * @return string|null
     */
    private function guessType(): ?string
    {
        if (null !== $this->type) {
            return $this->type;
        }

        $clone = clone $this;

        $types = $clone->map(
            function (mixed $element): string {
                if (is_object($element)) {
                    return $element::class;
                }

                return gettype($element);
            }
        );

        if (1 === $types->unique()->count()) {
            $this->type = $types->first();

            return $this->type;
        }

        return null;
    }
}
