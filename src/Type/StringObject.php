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

use Cocur\Slugify\Slugify;
use Composer\Semver\VersionParser;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use LogicException;
use Typing\Exception\InvalidTransformationException;
use Typing\Exception\InvalidTypeCastException;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoolCastableTrait;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\CollectionCastableTrait;
use Typing\Type\Traits\IntCastableTrait;
use UnexpectedValueException;

/**
 * Class StringType.
 *
 * A StringType is a TypeInterface implementation that wraps around a regular PHP string.
 * This object extends Stringy.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class StringObject extends AbstractStringObject implements
    ScalarValueObjectInterface,
    PrimitiveLoaderInterface,
    BoxableInterface
{
    use BoxableTrait;
    use CollectionCastableTrait;
    use IntCastableTrait;
    use BoolCastableTrait;

    /**
     * @var string
     */
    protected const SLUGIFY_REPLACEMENT = '\\2 \\3';

    /**
     * @return string
     */
    public function getScalarValue(): string
    {
        return (string) $this;
    }

    /**
     * Explodes current instance into a collection object.
     *
     * @param string $delimiter
     * @param int    $limit     default PHP_INT_MAX
     * @param bool   $trim      default true, greedely trim the string and delimiter before exploding
     *
     * @return Collection<int, string>
     */
    public function explode(string $delimiter, int $limit = PHP_INT_MAX, bool $trim = true): Collection
    {
        $str = ($trim) ? $this->regexReplace('[[:space:]]', '')->str : $this->str;
        /** @phpstan-var non-empty-string $delimiter */
        $delimiter = ($trim) ? static::create($delimiter)->regexReplace('[[:space:]]', '')->str : $delimiter;

        return new Collection(explode($delimiter, $str, $limit), static::class);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getScalarValue());
    }

    /**
     * cocur/slugify.
     *
     * @param string $replacement
     *
     * @return static
     */
    public function slugify(string $replacement = '-'): static
    {
        return new static(
            (new Slugify())->slugify(
                $this
                    ->regexReplace('(([A-z])([0-9]))', self::SLUGIFY_REPLACEMENT)
                    ->regexReplace('(([0-9])([A-z]))', self::SLUGIFY_REPLACEMENT)
                    ->replace('@', '-at-')
                    ->getScalarValue(),
                $replacement
            ),
            $this->encoding,
            $this->language
        );
    }

    /**
     * Normalizes accents, and other symbols into US letters.
     *
     * @return static
     */
    public function normalize(): static
    {
        return new static($this->getInflector()->unaccent($this->getScalarValue()), $this->encoding, $this->language);
    }

    /**
     * Similar to slugify, but no params.
     * Doctrine implementation.
     *
     * My first blog post! => my-first-blog-post
     *
     * @return static
     */
    public function urlize(): static
    {
        return new static($this->getInflector()->urlize($this->getScalarValue()), $this->encoding, $this->language);
    }

    /**
     * Turns string into a class name.
     * model_name => ModelName.
     *
     * @return static
     */
    public function classify(): static
    {
        return new static($this->getInflector()->classify($this->getScalarValue()), $this->encoding, $this->language);
    }

    /**
     * Returns an UpperCamelCase version of the supplied string. It trims
     * surrounding spaces, capitalizes letters following digits, spaces, dashes
     * and underscores, and removes spaces, dashes, underscores.
     *
     * @return static Object with $str in UpperCamelCase
     */
    public function upperCamelize(): static
    {
        return $this->camelize()->classify();
    }

    /**
     * @return static
     */
    public function tableize(): static
    {
        return new static($this->getInflector()->tableize($this->getScalarValue()), $this->encoding, $this->language);
    }

    /**
     * top-o-the-morning to all_of_you! => Top-O-The-Morning To All_of_you!
     *
     * With delimiters '-_ ' => Top-O-The-Morning To All_Of_You!
     *
     * @param string $delimiters
     *
     * @return static
     */
    public function capitilize(string $delimiters = " \n\t\r\0\x0B-"): static
    {
        return new static(
            $this->getInflector()->capitalize($this->getScalarValue(), $delimiters),
            $this->encoding,
            $this->language
        );
    }

    /**
     * Camel cases string.
     * model_name => modelName.
     *
     * @return static
     */
    public function camelize(): static
    {
        $result = $this->getInflector()->camelize(
            $this
                ->regexReplace('(([A-z])([0-9]))', self::SLUGIFY_REPLACEMENT)
                ->regexReplace('(([0-9])([A-z]))', self::SLUGIFY_REPLACEMENT)
                ->underscored()
                ->getScalarValue()
        );

        return new static(
            $result,
            $this->encoding,
            $this->language
        );
    }

    /**
     * Pluralizes the string.
     *
     * @return StringObject
     */
    public function pluralize(): StringObject
    {
        return static::create($this->getInflector()->pluralize((string) $this->str), $this->encoding);
    }

    /**
     * Singularizes the string.
     *
     * @return StringObject
     */
    public function singularize(): StringObject
    {
        return static::create($this->getInflector()->singularize((string) $this->str), $this->encoding);
    }

    /**
     * Returns position of the first occurrence of subStr null if not present.
     *
     * @param string $subStr        Substring
     * @param int    $offset        Chars to offset from start
     * @param bool   $caseSensitive Enable case sensitivity
     *
     * @return int
     */
    public function strpos(string $subStr, int $offset = 0, bool $caseSensitive = false): int
    {
        $res = ($caseSensitive) ?
            mb_strpos($this->str, $subStr, $offset, $this->encoding) :
            mb_stripos($this->str, $subStr, $offset, $this->encoding);

        return intval($res);
    }

    /**
     * Returns position of the last occurrence of subStr null if not present.
     *
     * @param string $subStr        Substring
     * @param int    $offset        Chars to offset from start
     * @param bool   $caseSensitive Enable case sensitivity
     *
     * @return int
     */
    public function strrpos(string $subStr, int $offset = 0, bool $caseSensitive = false): int
    {
        $res = ($caseSensitive) ?
            mb_strrpos($this->str, $subStr, $offset, $this->encoding) :
            mb_strripos($this->str, $subStr, $offset, $this->encoding);

        return intval($res);
    }

    /**
     * @return bool
     */
    public function isSemVer(): bool
    {
        try {
            (new VersionParser())->normalize($this->getScalarValue());

            return true;
        } catch (UnexpectedValueException) {
            // Return below.
        }

        return false;
    }

    /**
     * @return DateTime
     */
    public function toDateTime(): DateTime
    {
        return DateTime::fromPrimitive($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function fromPrimitive(mixed $mixed): static
    {
        return new static(self::asString($mixed));
    }

    /**
     * Returns substring from beginning until first instance of subsStr.
     *
     * @param string $subStr
     * @param bool   $includingSubStr
     * @param bool   $caseSensitive
     *
     * @return static
     */
    public function subStrUntil(string $subStr, bool $includingSubStr = false, bool $caseSensitive = false): static
    {
        $fromSubStr = $this->str[0];

        return $this->subStrBetween($fromSubStr, $subStr, false, !$includingSubStr, $caseSensitive);
    }

    /**
     * Returns substring from first instance of subStr to end of string.
     *
     * @param string $subStr
     * @param bool   $includingSubStr
     * @param bool   $caseSensitive
     *
     * @return static
     */
    public function subStrAfter(string $subStr, bool $includingSubStr = false, bool $caseSensitive = false): static
    {
        return $this->subStrBetween($subStr, null, !$includingSubStr, false, $caseSensitive);
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
     * {@inheritdoc}
     *
     * @return string|int|float|bool|string[]
     */
    protected function getScalar(Primitive $primitive = null): string | int | float | bool | array
    {
        $primitive = $primitive ?? Primitive::STRING();
        switch ((string) $primitive) {
            case (string) Primitive::INT():
                return $this->count();
            case (string) Primitive::ARRAY():
                if ($this->contains(',')) {
                    return $this->explode(',')->toArray();
                }

                return $this->chars();
            case (string) Primitive::BOOL():
                return BooleanObject::fromPrimitive($this->getScalarValue())->getScalarValue();
            default:
                // Throws exception below.
        }

        throw new InvalidTypeCastException($this, $primitive);
    }

    /**
     * Returns substring between fromSubStr to toSubStr. End of string if toSubStr is not set.
     *
     * @param string      $fromSubStr
     * @param string|null $toSubStr
     * @param bool        $excludeFromSubStr
     * @param bool        $excludeToSubStr
     * @param bool        $caseSensitive
     *
     * @return static
     */
    private function subStrBetween(
        string $fromSubStr,
        ?string $toSubStr = null,
        bool $excludeFromSubStr = false,
        bool $excludeToSubStr = false,
        bool $caseSensitive = false
    ): static {
        $fromIndex = 0;
        $toIndex = mb_strlen($this->str);
        $str = self::create($this->str);
        if ($str->contains($fromSubStr)) {
            $fromIndex = $this->strpos($fromSubStr, 0, $caseSensitive);
            $fromIndex = ($excludeFromSubStr) ? $fromIndex + mb_strlen($fromSubStr, $this->encoding) : $fromIndex;
            if ($fromIndex < 0) {
                throw new LogicException('To cannot be before from.');
            }
            if (!empty($toSubStr) && $str->contains($toSubStr)) {
                $toIndex = $this->strpos($toSubStr, $fromIndex, $caseSensitive);
                $toIndex = ($excludeToSubStr) ?
                    $toIndex - $fromIndex : ($toIndex - $fromIndex) + mb_strlen($toSubStr, $this->encoding);
            }
        }

        return ($toSubStr) ? $str->substr($fromIndex, $toIndex) : $str->substr($fromIndex);
    }

    /**
     * @return Inflector
     */
    private function getInflector(): Inflector
    {
        return InflectorFactory::createForLanguage((string) $this->language)->build();
    }

    /**
     * Returns a mixed variable as a string.
     *
     * @param mixed $mixed
     *
     * @return string
     */
    private static function asString(mixed $mixed): string
    {
        $type = strtolower(gettype($mixed));
        switch ($type) {
            case 'string':
            case 'integer':
            case 'float':
            case 'double':
                return (string) $mixed;
            case 'boolean':
                return ($mixed) ? 'true' : 'false';
            case 'array':
                return implode(', ', $mixed);
            case 'object':
                if (method_exists($mixed, '__toString')) {
                    return (string) $mixed;
                }

                throw new InvalidTransformationException($type, static::class);
            case 'resource':
                return get_resource_type($mixed);
            case 'null':
            default:
                throw new InvalidTransformationException($type, static::class);
        }
    }
}
