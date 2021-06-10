<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera at outlook.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Typing\Tests\Reference;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Typing\Reference\DefaultManager;
use Typing\Reference\ManagerInterface;

/**
 * Class DefaultManagerTest.
 */
class DefaultManagerTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $manager;

    protected function setUp(): void
    {
        $this->manager = DefaultManager::getInstance();
    }

    public function testGetPointerFailsWhenEmpty()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->manager->getPointer(PHP_INT_MAX);
    }

    public function testShouldFree()
    {
        $this->assertFalse($this->manager->shouldFree(PHP_INT_MAX));
        $pointer = '';
        $address = $this->manager->getNewAddress($pointer);
        $this->assertIsNumeric($address);
        $this->assertTrue($this->manager->shouldFree($address));
    }

    public function testRemoveReferences()
    {
        $pointer = '';
        $address = $this->manager->getNewAddress($pointer);
        $this->assertIsNumeric($address);
        $this->manager->removeReferences();
        $this->assertFalse($this->manager->shouldFree($address));
    }

    public function testGeneratesHashes()
    {
        $noop = 'noop';
        $this->manager->setAddress(PHP_INT_MAX, $noop);
        $hash = 'uat';
        $address = $this->manager->getNewAddress($hash);
        $this->assertTrue(!is_numeric($address));
    }

    public function tearDown(): void
    {
        $this->manager->removeReferences();
    }
}
