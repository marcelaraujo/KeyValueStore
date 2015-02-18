<?php

namespace Doctrine\Tests\KeyValueStore\Storage;

use Doctrine\KeyValueStore\Storage\RedisStorage;

/**
 * @author Marcel Araujo <admin@marcelaraujo.me>
 */
class RedisStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedisStorage
     */
    private $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redis;

    protected function setup()
    {
        if ( ! extension_loaded('redis')) {
            $this->markTestSkipped('Redis Extension is not installed.');
        }

        $this->redis = $this->getMockBuilder('\Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redis->expects($this->any())
            ->method('connect')
            ->with('127.0.0.1', '6379')
            ->will($this->returnValue(TRUE));

        $this->storage = new RedisStorage($this->redis);
    }

    /**
     * @test
     */
    public function isSupportsPartialUpdates()
    {
        $this->assertFalse($this->storage->supportsPartialUpdates());
    }

    /**
     * @test
     */
    public function isSupportsCompositePrimaryKeys()
    {
        $this->assertFalse($this->storage->supportsCompositePrimaryKeys());
    }

    /**
     * @test
     */
    public function isRequiresCompositePrimaryKeys()
    {
        $this->assertFalse($this->storage->requiresCompositePrimaryKeys());
    }

    /**
     * @test
     */
    public function insertData()
    {
        $data = [
            'author' => 'John Doe',
            'title'  => 'example book'
        ];

        $dbDataset = [];

        $this->redis->expects($this->once())
            ->method('set')
            ->will($this->returnCallback(function($key, $data) use (&$dbDataset) {
                $dbDataset[] = [
                    'key' => $key,
                    'value' => $data
                ];
            }));

        $this->storage->insert('redis', '1', $data);

        $this->assertCount(1, $dbDataset);
        $this->assertEquals([
            [
                'key' => $this->storage->getKeyName('1'),
                'value' => json_encode($data)
            ]
        ], $dbDataset);
    }

    /**
     * @test
     */
    public function updateData()
    {

        $data = [
            'author' => 'John Doe Updated',
            'title'  => 'example book updated'
        ];

        $dbDataset = [];

        $this->redis->expects($this->once())
            ->method('set')
            ->will($this->returnCallback(function($key, $data) use (&$dbDataset) {
                $dbDataset[] = [
                    'key' => $key,
                    'value' => $data
                ];
            }));


         $this->storage->update('redis', '1', $data);

         $this->assertCount(1, $dbDataset);
         $this->assertEquals([
             [
                 'key' => $this->storage->getKeyName('1'),
                 'value' => json_encode($data)
             ]
         ], $dbDataset);
    }

    /**
     * @test
     */
    public function getStorageName()
    {
        $this->assertEquals('redis', $this->storage->getName());
    }
}
