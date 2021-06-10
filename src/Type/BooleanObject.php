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

use Typing\Exception\InvalidTransformationException;
use Typing\Exception\InvalidTypeCastException;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\IntCastableTrait;
use Typing\Type\Traits\ScalarValueObjectTrait;
use Typing\Type\Traits\StringCastableTrait;

/**
 * Class Boolean.
 *
 * A Boolean is a TypeInterface implementation that wraps around a regular PHP bool.
 */
class BooleanObject implements ScalarValueObjectInterface, PrimitiveLoaderInterface
{
    use BoxableTrait;
    use ScalarValueObjectTrait;
    use IntCastableTrait;
    use StringCastableTrait;

    /**
     * @var array<string, bool>
     */
    public const STRING_MAP = [
        'true' => true,
        'on' => true,
        'yes' => true,
        '1' => true,
        'no' => false,
        'off' => false,
        'false' => false,
        '0' => false,
    ];

    /**
     * @param bool|string $bool
     */
    final public function __construct(bool | string $bool)
    {
        if (is_string($bool)) {
            $bool = static::asBool($bool);
        }

        $this->value = $bool;
    }

    /**
     * Returns true if boolean is true.
     *
     * @return bool
     */
    public function isTrue(): bool
    {
        return true === $this->value;
    }

    /**
     * Returns true if boolean is false.
     *
     * @return bool
     */
    public function isFalse(): bool
    {
        return false === $this->value;
    }

    /**
     * @param mixed $mixed
     *
     * @return static
     */
    public static function fromPrimitive(mixed $mixed): static
    {
        return new static(self::asBool($mixed));
    }

    /**
     * @param Primitive $primitive
     *
     * @return string|bool|int
     */
    protected function getScalar(Primitive $primitive): string | bool | int
    {
        return match ((string) $primitive) {
            (string) Primitive::STRING() => ($this->value) ? 'true' : 'false',
            (string) Primitive::INT() => (int) $this->value,
            default => throw new InvalidTypeCastException($this, $primitive),
        };
    }

    /**
     * The strings true, on, yes, 1 will return true, all other strings will return false.
     * Case insensitive.
     *
     * @param string $key
     *
     * @return bool
     */
    protected static function getFromStringMap(string $key): bool
    {
        $key = strtolower($key);

        if (false === array_key_exists($key, static::STRING_MAP)) {
            throw new InvalidTransformationException('string', static::class);
        }

        return static::STRING_MAP[$key];
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
     * Returns a scalar variable as a bool.
     *
     * @param bool|string $mixed
     *
     * @return bool
     */
    private static function asBool(bool | string $mixed): bool
    {
        $type = strtolower(gettype($mixed));

        return match ($type) {
            'boolean' => (bool) $mixed,
            'string' => self::getFromStringMap(strval($mixed)),
            default => throw new InvalidTransformationException($type, static::class),
        };
    }
}
