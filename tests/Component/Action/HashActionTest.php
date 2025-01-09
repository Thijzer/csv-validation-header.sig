<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\HashAction;
use PHPUnit\Framework\TestCase;

class HashActionTest extends TestCase
{
    public function testApplyWithKeyExists()
    {
        $item = ['key' => 'value'];

        $action = new HashAction();
        $action->setOption('key', 'key');
        $result = $action->apply($item);

        $this->assertEquals(['key' => crc32('value')], $result);
    }

    public function testApplyWithKeyMissing()
    {
        $item = ['other_key' => 'value'];

        $action = new HashAction();
        $action->setOption('key', 'key');
        $result = $action->apply($item);

        $this->assertEquals(['other_key' => 'value'], $result);
    }
}