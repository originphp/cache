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
 * This cache is for use in test suites
 */
class ArrayEngine extends BaseEngine
{
    protected $defaultConfig = [
        'prefix' => null,
    ];
    
    protected $data = [];

    /**
     * Sets a value in the cache
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function write(string $key, $value): bool
    {
        $this->data[$this->key($key)] = $value;

        return true;
    }
    /**
     * Reads a value from the cache, and returns null if there is no hit.
     *
     * @param string $key
     * @return mixed
     */
    public function read(string $key)
    {
        $key = $this->key($key);

        return $this->data[$key] ?? null;
    }
    /**
     * Checks if a key exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return isset($this->data[$this->key($key)]);
    }
    /**
     * Deletes a kehy from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        $key = $this->key($key);
        if ($this->exists($key)) {
            unset($this->data[$key]);

            return true;
        }

        return false;
    }

    /**
     * Clears the Cache
     *
     * @return boolean
     */
    public function clear(): bool
    {
        $this->data = [];

        return true;
    }

    /**
     * Increases a value
     *
     * @param string $key
     * @param integer $offset
     * @return integer|bool
     */
    public function increment(string $key, int $offset = 1)
    {
        $key = $this->key($key);
        if (! isset($this->data[$key])) {
            $this->data[$key] = 0;
        }
        $this->data[$key] += $offset;

        return $this->data[$key];
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
        if (! isset($this->data[$key])) {
            $this->data[$key] = 0;
        }
        $this->data[$key] -= $offset;

        return $this->data[$key];
    }
}
