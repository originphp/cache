<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
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

use InvalidArgumentException;
use Origin\Cache\Engine\FileEngine;
use Origin\Cache\Exception\Exception;

class FileEngineTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $cache = new FileEngine(['duration' => '+ 10 seconds']);
        $cache->clear();
    }

    public function testSet()
    {
        $cache = new FileEngine(['path'=> sys_get_temp_dir() .'/cache-test']);
        $this->assertTrue($cache->write('foo', 'bar'));
        $this->assertEquals('bar', unserialize(file_get_contents(sys_get_temp_dir(). '/cache-test/origin_foo')));
        $this->assertFalse($cache->write('foo', ''));
    }
    /**
     * @depends testSet
     */
    public function testGet()
    {
        $cache = new FileEngine();
        $this->assertFalse($cache->read('foo'));
        $cache->write('foo', 'bar');
        $this->assertEquals('bar', $cache->read('foo'));
    }

    /**
     * @depends testSet
     */
    public function testHas()
    {
        $cache = new FileEngine();
        $this->assertFalse($cache->exists('foo'));
        $cache->write('foo', 'bar');
        $this->assertTrue($cache->exists('foo'));
    }
    /**
     * @depends testHas
     */
    public function testDelete()
    {
        $cache = new FileEngine();
        $cache->write('foo', 'bar');
        $this->assertTrue($cache->exists('foo'));
        $this->assertTrue($cache->delete('foo'));

        $this->assertFalse($cache->exists('foo'));
        $this->assertFalse($cache->delete('foo'));
    }
    public function testClear()
    {
        $cache = new FileEngine();
        $cache->write('foo', 'bar');
        $cache->write('bar', 'foo');
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->exists('foo'));
        $this->assertFalse($cache->exists('bar'));
    }
    public function testIncrement()
    {
        $cache = new FileEngine();
        $this->assertEquals(1, $cache->increment('counter'));
        $cache->write('counter', 100);
        $this->assertEquals(101, $cache->increment('counter'));
        $this->assertEquals(110, $cache->increment('counter', 9));
    }
    public function testDecrement()
    {
        $cache = new FileEngine();
        $this->assertEquals(-1, $cache->decrement('counter'));
        $cache->write('counter', 110);
        $this->assertEquals(109, $cache->decrement('counter'));
        $this->assertEquals(100, $cache->decrement('counter', 9));
    }

    public function testSetGetDataTypes()
    {
        $cache = new FileEngine();
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
}
