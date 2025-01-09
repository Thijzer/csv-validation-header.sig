<?php

namespace Misery\Component\Common\Pipeline\Exception;

class InvalidItemException extends \Exception
{
    private $invalidItem;
    private $item;

    public function __construct(string $message = null, array $invalidItem, array $item = [])
    {
        $this->invalidItem = $invalidItem;

        parent::__construct($message);
        $this->item = $item;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function getInvalidItem(): array
    {
        return $this->invalidItem;
    }
}