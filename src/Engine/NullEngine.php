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
 * NullCache is for disabling cache
 */
namespace Origin\Cache\Engine;

class NullEngine extends BaseEngine
{
    /**
     * Sets a value in the cache
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function write(string $key, $value): bool
    {
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
        return null;
    }

    /**
     * Checks if a key exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return false;
    }
    /**
     * Deletes a key from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        return true;
    }
    /**
     * Clears the Cache
     *
     * @return boolean
     */
    public function clear(): bool
    {
        return false;
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
        return true;
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
        return true;
    }
}
