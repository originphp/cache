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

use Memcached;
use Origin\Cache\Exception\Exception;
use Origin\Cache\Engine\MemcachedEngine;

class MockMemcachedEngine extends MemcachedEngine
{
    public function memcached()
    {
        return $this->Memcached;
    }
}

class MemcachedEngineTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension not loaded');
        }
        if (! getenv('MEMCACHED_HOST') || ! getenv('MEMCACHED_PORT')) {
            $this->markTestSkipped('Memcached settings not found');
        }

        $cache = $this->engine();
        $cache->memcached()->flush();
    }
    /**
     * Creates a the cache engine and initlaizes it
     *
     * @return void
     */
    public function engine()
    {
        return new MockMemcachedEngine([
            'host' => getenv('MEMCACHED_HOST'),
            'port' => (int) getenv('MEMCACHED_PORT'),
            'duration' => 3600,
            'prefix' => 'origin_',
        ]);
    }
    public function testSet()
    {
        $cache = $this->engine();
        $this->assertTrue($cache->write('foo', 'bar'));
        $this->assertEquals('bar', $cache->memcached()->get('origin_foo'));
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
    public function testSetGetDataTypes()
    {
        $cache = $this->engine();
        $int = 123;
        $cache->write('int', $int);
        $this->assertEquals($int, $cache->read('int'));

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

    protected function tearDown(): void
    {
        $cache = $this->engine();
        $cache->memcached()->flush();
    }

    public function testBadConnection()
    {
        $this->expectException(Exception::class);
        new MockMemcachedEngine([
            'host' => 'memcached-not-exist',
            'duration' => 0,
            'prefix' => 'origin_',
            'persistent' => 'my-app',
        ]);
    }

    public function testBadConnectionSocket()
    {
        $this->expectException(Exception::class);
        new MockMemcachedEngine([
            'path' => '/tmp/fake-socket',
            'duration' => 0,
            'prefix' => 'origin_',
        ]);
    }

    public function testAddMultipleServers()
    {
        $servers = [
            [getenv('MEMCACHED_HOST'),getenv('MEMCACHED_PORT')],
            [getenv('MEMCACHED_HOST'),getenv('MEMCACHED_PORT')],
        ];
        $memcached = new MockMemcachedEngine([
            'servers' => $servers,
            'duration' => 0,
            'prefix' => 'origin_',
        ]);
        $this->assertInstanceOf(MemcachedEngine::class, $memcached);
    }

    public function testAddUsernamePassword()
    {
        $this->expectException(Exception::class); // Dont have it configured.
        $memcached = new MockMemcachedEngine([
            'host' => 'memcached',
            'username' => 'tony',
            'password' => 'secret',
            'duration' => 0,
            'prefix' => 'origin_',
        ]);
    }
}
