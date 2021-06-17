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

namespace Typing\Tests\Type;

use MyCLabs\Enum\Enum as BaseEnum;
use PHPUnit\Framework\TestCase;
use Typing\Model\Primitive;

/**
 * Class EnumTest.
 */
class EnumTest extends TestCase
{
    public function testIsInstanceOf()
    {
        $enum = new Primitive((string) Primitive::BOOL());
        $this->assertInstanceOf(BaseEnum::class, $enum);
    }
}
