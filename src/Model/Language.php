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
 * Class Language.
 *
 * @method static self ENGLISH()
 * @method static self FRENCH()
 * @method static self NORWEGIAN_BOKMAL()
 * @method static self PORTUGUESE()
 * @method static self SPANISH()
 * @method static self TURKISH()
 */
class Language extends Enum
{
    private const ENGLISH = 'english';
    private const FRENCH = 'french';
    private const NORWEGIAN_BOKMAL = 'norwegian-bokmal';
    private const PORTUGUESE = 'portuguese';
    private const SPANISH = 'spanish';
    private const TURKISH = 'turkish';
}
