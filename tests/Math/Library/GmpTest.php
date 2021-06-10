<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Math\Library;

use RuntimeException;
use Throwable;
use Typing\Math\Library\Gmp;
use Typing\Math\Library\MathLibraryInterface;

/**
 * Class GmpTest.
 */
class GmpTest extends AbstractMathLibraryTest
{
    /**
     * @var MathLibraryInterface
     */
    protected MathLibraryInterface $mathLibrary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mathLibrary = new Gmp();
    }

    public function testCanDivide()
    {
        $this->assertEquals('5', $this->mathLibrary->divide('20', '4'));
        $this->expectException(RuntimeException::class);
        $this->assertEquals('5', $this->mathLibrary->divide('20.0', '4'));
    }

    public function testCanGetGcd()
    {
        try {
            $this->mathLibrary->gcd(10.5, 5);
        } catch (Throwable $e) {
            $this->assertEquals(
                'gmp_gcd(): Argument #1 ($num1) is not an integer string',
                $e->getMessage()
            );
        }

        parent::testCanGetGcd();
    }

    public function testCanRoot()
    {
        try {
            $this->mathLibrary->root('5.5', 5);
        } catch (Throwable $e) {
            $this->assertEquals(
                'gmp_init(): Argument #1 ($num) is not an integer string',
                $e->getMessage()
            );
        }

        parent::testCanRoot();
    }
}
