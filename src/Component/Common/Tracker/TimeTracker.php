<?php

namespace Misery\Component\Common\Tracker;

class TimeTracker
{
    /** @var float */
    private $time;

    public function __construct()
    {
        $this->time = microtime(true);
    }

    function check()
    {
        return (float) substr((string) (microtime(true) - $this->time) ,0,5);
    }
}