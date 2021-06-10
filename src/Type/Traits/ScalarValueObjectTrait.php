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

namespace Typing\Type\Traits;

/**
 * Trait ValueType.
 */
trait ScalarValueObjectTrait
{
    /**
     * @var mixed
     */
    protected mixed $value;

    /**
     * @return mixed
     */
    public function getScalarValue(): mixed
    {
        return $this->value;
    }
}
