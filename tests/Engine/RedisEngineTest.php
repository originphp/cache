<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Origin\Test\Cache\Engine;

use Redis;
use Origin\Cache\Engine\RedisEngine;
use Origin\Cache\Exception\Exception;

class MockRedisEngine extends RedisEngine
{
    public function redis()
    {
        return $this->Redis;
    }
}

class RedisEngineTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded');
        }

        if (! getenv('REDIS_HOST') or ! getenv('REDIS_PORT')) {
            $this->markTestSkipped('Redis settings not found');
        }

        $cache = $this->engine();
        $cache->clear();
    }

    /**
     * Creates a the cache engine and initlaizes it
     *
     * @return void
     */
    public function engine()
    {
        return new MockRedisEngine([
            'host' => getenv('REDIS_HOST'),
            'port' => (int) getenv('REDIS_PORT'),
            'duration' => 3600,
            'prefix' => 'origin_',
        ]);
    }
    public function testSet()
    {
        $cache = $this->engine();
        $this->assertTrue($cache->write('foo', 'bar'));
        $this->assertNotEmpty($cache->redis()->get('origin_foo'));
        $this->assertEquals('bar', $cache->read('foo'));
    }
    /**
     * @depends testSet
     */
    public function testGet()
    {
        $cache = $this->engine();
        $this->assertNull($cache->read('foo'));
        $cache->write('foo', 'bar');
        $this->assertEquals('bar', $cache->read('foo'));
    }
    /**
     * @depends testSet
     */
    public function testHas()
    {
        $cache = $this->engine();
        $this->assertFalse($cache->exists('foo'));
        $cache->write('foo', 'bar');
        $this->assertTrue($cache->exists('foo'));
    }
    /**
     * @depends testHas
     */
    public function testDelete()
    {
        $cache = $this->engine();
        $cache->write('foo', 'bar');
        $this->assertTrue($cache->exists('foo'));
        $this->assertTrue($cache->delete('foo'));

        $this->assertFalse($cache->exists('foo'));
        $this->assertFalse($cache->delete('foo'));
    }
    /**
     * @depends testSet
     */
    public function testClear()
    {
        $cache = $this->engine();
        $cache->write('foo', 'bar');
        $cache->write('bar', 'foo');
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->exists('foo'));
        $this->assertFalse($cache->exists('bar'));
    }
    /**
     * @depends testSet
     */
    public function testIncrement()
    {
        $cache = $this->engine();
        $cache->write('counter', 100);
        $this->assertEquals(101, $cache->increment('counter'));
        $this->assertEquals(110, $cache->increment('counter', 9));
    }
    /**
     * @depends testSet
     */
    public function testDecrement()
    {
        $cache = $this->engine();
        $cache->write('counter', 110);
        $this->assertEquals(109, $cache->decrement('counter'));
        $this->assertEquals(100, $cache->decrement('counter', 9));
    }

    /**
     * Call destruct during test
     */
    public function testCloseConnection()
    {
        $cache = new MockRedisEngine([
            'host' => getenv('REDIS_HOST'),
            'port' => (int) getenv('REDIS_PORT'),
            'duration' => 3600,
            'prefix' => 'origin_',
            'persistent' => false,
        ]);
        $this->assertTrue($cache->closeConnection());
    }

    protected function tearDown(): void
    {
        $cache = $this->engine();
        $cache->clear();
    }

    /**
     * Make sure it runs smothely
     *
     * @return void
     */
    public function testPersistent()
    {
        $redis = new MockRedisEngine([
            'host' => getenv('REDIS_HOST'),
            'port' => (int) getenv('REDIS_PORT'),
            'duration' => 0,
            'prefix' => 'origin_',
            'persistent' => 'persisten-id',
        ]);

        $redis->write('counter', 100);
        $this->assertEquals(101, $redis->increment('counter'));
    }

    public function testSocketException()
    {
        $this->expectException(Exception::class);
        $redis = new MockRedisEngine([
            'engine' => 'Redis',
            'path' => '/var/sockets/redis',
        ]);
    }

    public function testNonPersisentException()
    {
        $this->expectException(Exception::class);
        $engine = new MockRedisEngine([
            'host' => 'foo',
            'port' => 1234,
        ]);
    }

    public function testInvalidPassword()
    {
        $this->expectException(Exception::class);
        $engine = new MockRedisEngine([
            'host' => getenv('REDIS_HOST'),
            'port' => (int) getenv('REDIS_PORT'),
            'password' => 'secret',
        ]);
    }

    /**
     * @depends testSet
     */
    public function testSetGetDataTypes()
    {
        $cache = $this->engine();
        $int = 123;
        $cache->write('int', $int);
        $this->assertEquals($int, $cache->read('int'));

        $cache->write('int', 0);
        $this->assertEquals(0, $cache->read('int'));

        $intString = '123';
        $cache->write('intstring', $intString);
        $this->assertEquals($intString, $cache->read('intstring'));

        $string = '';
        $cache->write('string', $string);
        $this->assertEquals($string, $cache->read('string'));

        $string = 'foo';
        $cache->write('string', $string);
        $this->assertEquals($string, $cache->read('string'));

        $array = ['foo' => 'bar'];
        $cache->write('array', $array);
        $this->assertEquals($array, $cache->read('array'));
        
        $object = (object) $array;
        $cache->write('object', $object);
        $this->assertEquals($object, $cache->read('object'));
    }
}
