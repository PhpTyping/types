<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Math;

use PHPUnit\Framework\TestCase;
use Typing\Math\DefaultNumberValidator;

/**
 * Class StandardNumberValidatorTest.
 */
class StandardNumberValidatorTest extends TestCase
{
    public function testFailsString()
    {
        $validator = new DefaultNumberValidator();
        $this->assertFalse($validator->isValid('ThisIsNotANumber'));
    }

    public function testValidNumber()
    {
        $validator = new DefaultNumberValidator();
        $this->assertTrue($validator->isValid(true));
        $this->assertTrue($validator->isValid(false));
        $this->assertTrue($validator->isValid(3));
        $this->assertTrue($validator->isValid('4'));
        $this->assertTrue($validator->isValid(43.029));
        $this->assertTrue($validator->isValid('39.039'));
        $this->assertTrue($validator->isValid(1.2e3));
        $this->assertTrue($validator->isValid('1.2e3'));
        $this->assertTrue($validator->isValid(7E-10));
        $this->assertTrue($validator->isValid('7E-10'));
    }
}
