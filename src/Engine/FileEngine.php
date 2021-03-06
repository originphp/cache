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

class FileEngine extends BaseEngine
{
    protected $defaultConfig = [
        'path' => null,
        'duration' => 3600,
        'prefix' => 'origin_',
        'serialize' => true,
        'mode' => 0664
    ];

    public function initialize(array $config = []): void
    {
        if (empty($this->config['path'])) {
            $this->config['path'] = sys_get_temp_dir() . '/cache';
        }

        if (! is_dir($this->config['path'])) {
            @mkdir($this->config['path'], 0775, true);
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

        $cacheFile = $this->config['path'] . '/' . $this->key($key);
        $exists = file_exists($cacheFile);
        
        $result = (bool) file_put_contents($cacheFile, $value, LOCK_EX);

        if (! $exists) {
            $this->applyPermissions($cacheFile);
        }
        
        return $result;
    }

    /**
     * Applies the default permissions to cache file
     *
     * @param string $cacheFile
     * @return void
     */
    private function applyPermissions(string $cacheFile): void
    {
        if (! chmod($cacheFile, (int) $this->config['mode'])) {
            trigger_error(sprintf(
                'Unable to set %s permission for %s',
                $this->config['mode'],
                $cacheFile
            ), E_USER_WARNING);
        }
    }
    /**
     * Reads a value from the cache, and returns null if there is no hit.
     *
     * @param string $key
     * @return mixed
     */
    public function read(string $key)
    {
        if ($this->exists($key)) {
            $filename = $this->config['path'] . '/' . $this->key($key);
            $expires = filemtime($filename) + $this->duration();
            if ($expires > time()) {
                $data = file_get_contents($filename);

                return $this->config['serialize'] ? unserialize($data) : $data;
            }
        }

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
        $filename = $this->config['path'] . '/' . $this->key($key);
        if (file_exists($filename)) {
            $expires = filemtime($filename) + $this->duration();

            return $expires > time();
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
        $result = [];
        $files = array_diff(scandir($this->config['path']), ['..', '.']);
        $prefixLength = strlen($this->config['prefix']);
        foreach ($files as $file) {
            if (substr($file, 0, $prefixLength) === $this->config['prefix']) {
                $result[] = unlink($this->config['path'] . '/' . $file) === true;
            }
        }

        return ! in_array(false, $result);
    }

    /**
     * Increases
     * a value
     *
     * @param string $key
     * @param integer $offset
     * @return integer
     */
    public function increment(string $key, int $offset = 1)
    {
        $value = 0;
        if ($this->exists($key)) {
            $value = $this->read($key);
        }
        $value += $offset;
        $this->write($key, $value);

        return $value;
    }

    /**
     * Decreases a value
     *
     * @param string $key
     * @param integer $offset
     * @return integer
     */
    public function decrement(string $key, int $offset = 1)
    {
        $value = 0;
        if ($this->exists($key)) {
            $value = $this->read($key);
        }
        $value -= $offset;
        $this->write($key, $value);

        return $value;
    }
}
