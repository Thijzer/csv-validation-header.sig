<?php

namespace Misery\Component\Common\Cache\Local;

use Misery\Component\Common\Cache\CacheSettings;
use Misery\Component\Common\Cache\SimpleCacheInterface;

class LocalFilesystemCache implements SimpleCacheInterface
{
    private string $cacheDirectory;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0777, true);
        }
    }

    public function retrieve($key, callable $process)
    {
        $cachedData = $this->get($key);
        if ($cachedData !== null) {
            return $cachedData;
        }

        $item = $process($setting = new CacheSettings());

        // Cache the API response
        if (!empty($item)) {
            $this->set($key, $item, $setting->getTtl());
        }

        return $item;
    }

    public function get($key, $default = null)
    {
        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $data = file_get_contents($filename);
        $cachedItem = unserialize($data);

        if ($cachedItem['ttl'] !== null && $cachedItem['ttl'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $cachedItem['value'];
    }

    public function set($key, $value, $ttl = null)
    {
        $filename = $this->getCacheFilename($key);
        $cachedItem = [
            'value' => $value,
            'ttl' => $ttl !== null ? time() + $ttl : null,
        ];

        $data = serialize($cachedItem);
        return file_put_contents($filename, $data) !== false;
    }

    public function delete($key)
    {
        $filename = $this->getCacheFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    public function clear()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDirectory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                rmdir($path->getPathname());
            } else {
                unlink($path->getPathname());
            }
        }

        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }
        return $values;
    }

    public function setMultiple($values, $ttl = null)
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $success && $this->set($key, $value, $ttl);
        }
        return $success;
    }

    public function deleteMultiple($keys)
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $success && $this->delete($key);
        }
        return $success;
    }

    public function has($key)
    {
        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename)) {
            return false;
        }

        $cachedItem = unserialize(file_get_contents($filename));

        if ($cachedItem['ttl'] !== null && $cachedItem['ttl'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    private function getCacheFilename($key)
    {
        $key = preg_replace('/[^a-z0-9_\-]/i', '', $key); // Sanitize key
        return $this->cacheDirectory . DIRECTORY_SEPARATOR . $key;
    }
}
