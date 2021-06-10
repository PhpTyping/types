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

use DivisionByZeroError;
use Mockery;
use Mockery\Mock;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Typing\Exception\InvalidNumberException;
use Typing\Math\DefaultMathAdapter;
use Typing\Math\Library\MathLibraryInterface;
use Typing\Math\MathAdapterInterface;
use Typing\Math\NumberValidatorInterface;
use Typing\Type\Collection;

/**
 * Class DefaultMathAdapterTest.
 */
class DefaultMathAdapterTest extends TestCase
{
    /**
     * @var NumberValidatorInterface|Mock
     */
    private NumberValidatorInterface | Mock $numberValidator;

    /**
     * @var MathAdapterInterface
     */
    private MathAdapterInterface $mathAdapter;

    /**
     * Create objects.
     */
    protected function setUp(): void
    {
        $this->numberValidator = Mockery::mock(NumberValidatorInterface::class);
        $this->numberValidator
            ->shouldReceive('isValid')
            ->with(Mockery::any())
            ->andReturn(true)
        ;

        $this->mathAdapter = new DefaultMathAdapter($this->numberValidator);
    }

    public function testFailsStartWhenInvalidDelegate()
    {
        $this->expectException(OutOfBoundsException::class);
        new DefaultMathAdapter($this->numberValidator, new Collection(['foo']));
    }

    public function testFailsWhenNoDelegateCanPerformAdd()
    {
        $adapter = new DefaultMathAdapter($this->numberValidator, new Collection([], MathLibraryInterface::class));
        $this->expectException(RuntimeException::class);
        $adapter->add('2', '2');
    }

    public function testFailsWhenNoDelegateCanPerformIsPrime()
    {
        $adapter = new DefaultMathAdapter($this->numberValidator, new Collection([], MathLibraryInterface::class));
        $this->expectException(RuntimeException::class);
        $adapter->isPrime(9);
    }

    public function testBadRoundingStrategy()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessageMatches(
            "/Unsupported rounding strategy. Please refer to PHP's documentation on (.*)$/"
        );

        (new DefaultMathAdapter($this->numberValidator, null, 5));
    }

    public function testInvalidLeftOperator()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessageMatches(
            '/Invalid number: Foo$/'
        );
        (new DefaultMathAdapter())->add('Foo', 9);
    }

    public function testInvalidRightOperator()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessageMatches(
            '/Invalid number: Bar/'
        );
        (new DefaultMathAdapter())->add(1, 'Bar');
    }

    public function testPrecision()
    {
        $this->assertEquals(2, $this->mathAdapter->getPrecision('4.23'));
        $this->assertEquals(6, $this->mathAdapter->getPrecision('6.356548'));
        $this->assertEquals(0, $this->mathAdapter->getPrecision('4'));
    }

    public function testAdd()
    {
        $this->assertEquals('4', $this->mathAdapter->add(2, 2));
        $this->assertEquals('4.4', $this->mathAdapter->add(2.2, 2.2, 1));
    }

    public function testSubtract()
    {
        $this->assertEquals('0', $this->mathAdapter->subtract(2, 2));
        $this->assertEquals('0.1', $this->mathAdapter->subtract(2.3, 2.2, 1));
    }

    public function testMultiply()
    {
        $this->assertEquals('4', $this->mathAdapter->multiply(2, 2));
        $this->assertEquals('4', $this->mathAdapter->multiply(2.2, 2.2)); //No Precision
        $this->assertEquals('4.84', $this->mathAdapter->multiply(2.2, 2.2, 2)); //2 Precision points
    }

    public function testBadDivideByZero()
    {
        $this->expectException(DivisionByZeroError::class);
        $this->mathAdapter->divide(10, 0);
    }

    public function testDivide()
    {
        $this->assertEquals('1', $this->mathAdapter->divide(2, 2));
        $this->assertEquals('2.0', $this->mathAdapter->divide(2.2, 1.1, 1));
    }

    public function testCompare()
    {
        $this->assertEquals('1', $this->mathAdapter->compare('1.4', '1.04', 2));
        $this->assertEquals('-1', $this->mathAdapter->compare('1', '10'));
        $this->assertEquals('0', $this->mathAdapter->compare('3', '3'));

        //Version comp
        $this->assertEquals('-1', $this->mathAdapter->compare('0.90.01', '0.91.04', 5));
    }

    public function testModulo()
    {
        $this->assertEquals('5', $this->mathAdapter->modulo('5', '10'));
        $this->assertEquals('5.5', $this->mathAdapter->modulo('5.5', '10', 1));
    }

    public function testPower()
    {
        $this->assertEquals('9765625', $this->mathAdapter->power('5', '10'));
        $this->assertEquals('766217865.41', $this->mathAdapter->power('5.5', '12', 2));
    }

    public function testSquareRoot()
    {
        $this->assertEquals('3', $this->mathAdapter->squareRoot(9));
        $this->assertEquals('7.0278', $this->mathAdapter->squareRoot('49.39', 4));
    }

    public function testAbsolute()
    {
        $this->assertEquals('9', $this->mathAdapter->absolute('-9'));
        $this->assertEquals('49.39', $this->mathAdapter->absolute('-49.39'));
    }

    public function testNegate()
    {
        $this->assertEquals('9', $this->mathAdapter->negate('-9'));
        $this->assertEquals('-9', $this->mathAdapter->negate('9'));
        $this->assertEquals('-49.39', $this->mathAdapter->negate('49.39'));
    }

    public function testFactorial()
    {
        $this->assertEquals('3628800', $this->mathAdapter->factorial('10'));
    }

    public function testBadFactorial()
    {
        $this->expectException(InvalidNumberException::class);
        $this->mathAdapter->factorial('4.4');
    }

    public function testBadGcd()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Arguments must be whole, positive numbers.');
        $this->mathAdapter->gcd('-5', '0');
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Arguments must be whole, positive numbers.');
        $this->mathAdapter->gcd(10.5, 5);
    }

    public function testGcd()
    {
        $this->assertEquals('10', $this->mathAdapter->gcd('10', '50'));
    }

    public function testBadRoot()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Arguments must be whole, positive numbers.');
        $this->mathAdapter->root('-5', 2);
    }

    public function testRoot()
    {
        $this->assertEquals('3', $this->mathAdapter->root('32', 3));
    }

    public function testIsPrime()
    {
        $this->assertFalse($this->mathAdapter->isPrime(1));
        $this->assertTrue($this->mathAdapter->isPrime(2));
        $this->assertTrue($this->mathAdapter->isPrime(5));
    }

    public function testNextPrime()
    {
        $this->assertEquals('7', $this->mathAdapter->nextPrime('5'));
    }

    public function testIsPerfectSquare()
    {
        $this->assertTrue($this->mathAdapter->isPerfectSquare(1));
        $this->assertTrue($this->mathAdapter->isPerfectSquare(4));
        $this->assertTrue($this->mathAdapter->isPerfectSquare(9));
        $this->assertFalse($this->mathAdapter->isPerfectSquare(3));
        $this->assertFalse($this->mathAdapter->isPerfectSquare(5));
        $this->assertFalse($this->mathAdapter->isPerfectSquare(26));
    }

    public function testBadPrecision()
    {
        $this->expectException(InvalidNumberException::class);
        $this->expectExceptionMessage('Invalid number: Foo');
        (new DefaultMathAdapter())->getPrecision('Foo');
    }

    public function testGamma()
    {
        $this->assertEquals('24', $this->mathAdapter->gamma('5'));
        $this->assertEquals('1871.2543057978', $this->mathAdapter->gamma('7.5'));
    }

    public function testLogGamma()
    {
        $this->assertEquals('1.2009736023471', $this->mathAdapter->logGamma('3.5'));
    }
}
