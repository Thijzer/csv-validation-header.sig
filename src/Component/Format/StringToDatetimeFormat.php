<?php

namespace Misery\Component\Format;

use Misery\Component\Common\Format\StringFormat;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class StringToDatetimeFormat implements StringFormat, OptionsInterface
{
    // date attribute ak
    const DATE_ISO8601 = 'Y-m-dT00:00:00+0000';
    // date time attribute ak
    const DATETIME_ISO8601 = \DateTime::ISO8601;
    const DATE = 'd/m/Y';
    const DATETIME = 'd/m/Y H:i:s';

    use OptionsTrait;

    public const NAME = 'datetime';

    /** @var array */
    private $options = [
        'format' => self::DATETIME,
    ];

    /** @inheritDoc */
    public function format(string $value): \DateTime
    {
        return \DateTime::createFromFormat($this->options['format'], $value);
    }

    /**
     * @param \DateTime $value
     * @return string
     */
    public function reverseFormat($value): string
    {
        return $value->format($this->options['format']);
    }
}