<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright    Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license      https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);
namespace Origin\Cache\Engine;

/**
 * Should work with phpredis
 * @see https://github.com/phpredis/phpredis
 *
 * Add to docker
 *  redis:
 *  image: redis
 *
 * pecl install redis
 * echo 'extension=redis.so' >> /etc/php/7.2/cli/php.ini
 */
use Redis;

class RedisEngine extends BaseEngine
{

    /**
     * Redis Object
     *
     * @var Redis
     */
    protected $Redis = null;

    protected $defaultConfig = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'timeout' => 0,
        'persistent' => true, // Faster!!!
        'path' => null, // Path to redis unix socket,
        'duration' => 3600,
        'prefix' => 'origin_',
    ];

    /**
     * Constructor
     *
     * @param array $config  duration,prefix,path
     */
    public function initialize(array $config): void
    {
        $mergedWithDefault = $this->config();
        $this->Redis = RedisConnection::connect($mergedWithDefault);
    }

    /**
     * Sets a value in the cache
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function write(string $key, $value): bool
    {
        if (! is_int($value)) {
            $value = serialize($value);
        }
    
        $duration = $this->duration();
        if ($duration === 0) {
            return $this->Redis->set($this->key($key), $value);
        }

        return $this->Redis->setex($this->key($key), $duration, $value);
    }
    /**
     * Reads a value from the cache, and returns null if there is no hit.
     *
     * @param string $key
     * @return mixed
     */
    public function read(string $key)
    {
        $value = $this->Redis->get($this->key($key));

        if ($value === false) {
            return null;
        }
        if (preg_match('/\d+$/', $value)) {
            return (int) $value;
        }
       
        if (is_string($value)) {
            $value = unserialize($value);
        }
        
        return $value;
    }
    /**
     * Checks if a key exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return (bool) $this->Redis->exists($this->key($key));
    }
    /**
     * Deletes a key from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        return $this->Redis->del($this->key($key)) > 0;
    }

    /**
     * Clears the Cache
     *
     * @return bool
     */
    public function clear(): bool
    {
        $keys = $this->Redis->keys($this->config['prefix'] . '*');
        $result = [];
        foreach ($keys as $key) {
            $result[] = (bool) $this->Redis->del($key);
        }

        return ! in_array(false, $result);
    }

    public function closeConnection(): bool
    {
        if ($this->Redis instanceof Redis && ! $this->config['persistent']) {
            $this->Redis->close();

            return true;
        }

        return false;
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Increases a value
     *
     *  Cache::write('my_value',100);
     *  $value = Cache::increment('my_value');
     *
     * @param string $key
     * @param integer $offset
     * @return integer|bool
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = $this->key($key);
        $value = (int) $this->Redis->incrBy($key, $offset);
        $duration = $this->duration();
        if ($duration > 0) {
            $this->Redis->expire($key, $duration);
        }

        return $value;
    }

    /**
     * Decreases a value
     *
     * @param string $key
     * @param integer $offset
     * @return integer|bool
     */
    public function decrement(string $key, int $offset = 1)
    {
        $key = $this->key($key);
        $value = (int) $this->Redis->decr($key, $offset);
        $duration = $this->duration();
        if ($duration > 0) {
            $this->Redis->expire($key, $duration);
        }

        return $value;
    }
}
