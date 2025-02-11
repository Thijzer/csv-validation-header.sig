<?php

namespace Component\Common\Cache\Local;

use Misery\Component\Common\Cache\CacheSettings;
use Misery\Component\Common\Cache\Local\LocalFilesystemCache;
use PHPUnit\Framework\TestCase;

class LocalFilesystemCacheTest extends TestCase
{
    private ?LocalFilesystemCache $cache;

    protected function setUp(): void
    {
        $this->cacheDirectory = sys_get_temp_dir() . '/cache_test';
        $this->cache = new LocalFilesystemCache($this->cacheDirectory);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    public function testSetAndGet()
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->assertTrue($this->cache->set($key, $value));
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testRetrieve()
    {
        $key = 'test_key';
        $value = 'test_value';

        // Mock a callback function that simulates fetching data
        $processCallback = function ($settings) use ($value) {
            $this->assertInstanceOf(CacheSettings::class, $settings);
            return $value;
        };

        // Call retrieve and ensure it returns the value from the callback
        $cachedValue = $this->cache->retrieve($key, $processCallback);
        $this->assertEquals($value, $cachedValue);

        // Verify that the value is cached
        $cachedValueFromCache = $this->cache->get($key);
        $this->assertEquals($value, $cachedValueFromCache);
    }

    public function testGetWithDefault()
    {
        $key = 'non_existent_key';
        $defaultValue = 'default_value';

        $result = $this->cache->get($key, $defaultValue);

        $this->assertEquals($defaultValue, $result);
    }

    public function testDelete()
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->set($key, $value);
        $this->assertTrue($this->cache->delete($key));
        $this->assertNull($this->cache->get($key));
    }

    public function testClear()
    {
        $key1 = 'test_key1';
        $value1 = 'test_value1';
        $key2 = 'test_key2';
        $value2 = 'test_value2';

        $this->cache->set($key1, $value1);
        $this->cache->set($key2, $value2);

        $this->assertTrue($this->cache->clear());

        $this->assertNull($this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));
    }

    public function testSetWithTTL()
    {
        $key = 'test_key';
        $value = 'test_value';

        $ttl = 1; // 1 second

        $this->assertTrue($this->cache->set($key, $value, $ttl));
        sleep(2); // Sleep for more than the TTL

        $this->assertNull($this->cache->get($key));
    }

    public function testHas()
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->set($key, $value);

        $this->assertTrue($this->cache->has($key));
        $this->assertFalse($this->cache->has('non_existent_key'));
    }

    public function testGetMultiple()
    {
        $key1 = 'test_key1';
        $value1 = 'test_value1';
        $key2 = 'test_key2';
        $value2 = 'test_value2';

        $this->cache->set($key1, $value1);
        $this->cache->set($key2, $value2);

        $keys = [$key1, $key2];
        $default = 'default_value';

        $result = $this->cache->getMultiple($keys, $default);

        $this->assertEquals([$key1 => $value1, $key2 => $value2], $result);
    }

    public function testSetMultiple()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->assertTrue($this->cache->setMultiple($data));

        $this->assertEquals($data['key1'], $this->cache->get('key1'));
        $this->assertEquals($data['key2'], $this->cache->get('key2'));
        $this->assertEquals($data['key3'], $this->cache->get('key3'));
    }

    public function testDeleteMultiple()
    {
        $key1 = 'test_key1';
        $value1 = 'test_value1';
        $key2 = 'test_key2';
        $value2 = 'test_value2';
        $key3 = 'test_key3';
        $value3 = 'test_value3';

        $this->cache->set($key1, $value1);
        $this->cache->set($key2, $value2);
        $this->cache->set($key3, $value3);

        $keysToDelete = [$key1, $key2];

        $this->assertTrue($this->cache->deleteMultiple($keysToDelete));
        $this->assertNull($this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));
        $this->assertEquals($value3, $this->cache->get($key3));
    }
}
