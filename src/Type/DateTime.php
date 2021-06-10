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

use Carbon\Carbon;
use DateTimezone;
use Typing\Exception\InvalidTransformationException;
use Typing\Exception\InvalidTypeCastException;
use Typing\Model\Primitive;
use Typing\Type\Traits\BoxableTrait;
use Typing\Type\Traits\StringCastableTrait;

/**
 * Class DateTime.
 *
 * A DateTime is a TypeInterface implementation that wraps around a regular string value meant to represent a date.
 * This object extends Carbon, which extends PHP's own \DateTime.
 */
class DateTime extends Carbon implements PrimitiveLoaderInterface, BoxableInterface
{
    use BoxableTrait;
    use StringCastableTrait;

    /**
     * @param string|null              $time
     * @param DateTimezone|string|null $tz
     */
    public function __construct($time = null, $tz = null)
    {
        parent::__construct($time, $tz);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function fromPrimitive(mixed $mixed): static
    {
        $type = strtolower(gettype($mixed));
        if ($mixed instanceof StringObject || 'string' === $type) {
            return new static((string) $mixed);
        }

        throw new InvalidTransformationException($type, static::class);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getScalar(Primitive $primitive = null): string
    {
        if ((string) Primitive::STRING() === (string) $primitive) {
            return $this->format('Y-m-d H:i:s');
        }

        throw new InvalidTypeCastException($this, $primitive);
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
}
