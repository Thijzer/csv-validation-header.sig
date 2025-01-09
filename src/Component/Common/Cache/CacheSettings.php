<?php

namespace Misery\Component\Common\Cache;

class CacheSettings
{
    private int $ttl = 3600;

    /**
     * @param int $ttl
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}