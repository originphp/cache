<?php

declare(strict_types=1);
/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright     Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * File cache should only be used for storing large objects or sets of data
 */

namespace Origin\Cache\Engine;

use InvalidArgumentException;
use Origin\Cache\Exception\Exception;

class FileEngine extends BaseEngine
{
    protected $defaultConfig = [
        'path' => null,
        'duration' => 3600,
        'prefix' => 'origin_',
        'serialize' => true,
    ];

    public function initialize(array $config = []): void
    {
        if (empty($config['path'])) {
            $config['path'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cache';
        }

        if (! is_dir($config['path'])) {
            @mkdir($config['path'], 0775, true);
        }
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
        if ($value === '') {
            return false;
        }
        if ($this->config['serialize'] === true) {
            $value = serialize($value);
        }

        return (bool) file_put_contents($this->config['path'] . '/' . $this->key($key), $value, LOCK_EX);
    }
    /**
     * Gets the value;
     *
     * @param string $key
     * @return mixed
     */
    public function read(string $key)
    {
        if ($this->exists($key)) {
            $filename = $this->config['path'] . '/' . $this->key($key);
            $expires = filemtime($filename) + $this->config['duration'];
            if ($expires > time()) {
                $data = file_get_contents($filename);
                if ($this->config['serialize'] === true) {
                    return unserialize($data);
                }
            }
        }

        return false;
    }
    /**
     * Checks if a key exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        $filename = $this->config['path'] . '/' . $this->key($key);
        if (file_exists($filename)) {
            $expires = filemtime($filename) + $this->config['duration'];

            return $expires >= time();
        }

        return  false;
    }
    /**
     * Deletes a kehy from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        if ($this->exists($key)) {
            return unlink($this->config['path'] . '/' . $this->key($key));
        }

        return false;
    }

    /**
     * Clears the file cache
     *
     * @return boolean
     */
    public function clear(): bool
    {
        $files = scandir($this->config['path']);
        $result = [];
        foreach ($files as $file) {
            if (substr($file, 0, strlen($this->config['prefix'])) == $this->config['prefix']) {
                $result[] = (unlink($this->config['path'] . '/' . $file) === true);
            }
        }

        return ! in_array(false, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        throw new Exception('File cache cannot be incremented.');
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        throw new Exception('File cache cannot be decremented.');
    }
}
