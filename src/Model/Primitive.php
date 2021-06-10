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

namespace Typing\Model;

use Typing\Type\Enum;

/**
 * Class Primitive.
 *
 * @method static self STRING()
 * @method static self BOOL()
 * @method static self INT()
 * @method static self FLOAT()
 * @method static self ARRAY()
 */
class Primitive extends Enum
{
    private const STRING = 'string';
    private const BOOL = 'boolean';
    private const INTEGER = 'integer';
    private const INT = 'integer';
    private const FLOAT = 'float';
    private const ARRAY = 'array';
}
