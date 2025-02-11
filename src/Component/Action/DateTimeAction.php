<?php

namespace Misery\Component\Action;

use DateTime;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class DateTimeAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'date_time';
    public const FORMAT_MAPPING = [
        'ATOM' => "Y-m-d\\TH:i:sP",
        'COOKIE' => "l, d-M-Y H:i:s T",
        'ISO8601' => "Y-m-d\\TH:i:sO",
        'ISO8601_EXPANDED' => "X-m-d\\TH:i:sP",
        'RFC822' => "D, d M y H:i:s O",
        'RFC850' => "l, d-M-y H:i:s T",
        'RFC1036' => "D, d M y H:i:s O",
        'RFC1123' => "D, d M Y H:i:s O",
        'RFC7231' => "D, d M Y H:i:s \\G\\M\\T",
        'RFC2822' => "D, d M Y H:i:s O",
        'RFC3339' => "Y-m-d\\TH:i:sP",
        'RFC3339_EXTENDED' => "Y-m-d\\TH:i:s.vP",
        'RSS' => "D, d M Y H:i:s O",
        'W3C' => "Y-m-d\\TH:i:sP",
    ];

    private $options = [
        'field' => null,
        'inputFormat' => null,
        'outputFormat' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('key')); # legacy key
        if (array_key_exists($field, $item) && !empty($item[$field])) {
            $item[$field] = $this->convertDateTimeFormat($item[$field], $this->getOption('inputFormat'), $this->getOption('outputFormat'));
        }

        return $item;
    }

    private function convertDateTimeFormat($datetimeString, $inputFormat, $outputFormat): string
    {
        $inputFormat = self::FORMAT_MAPPING[$inputFormat] ?? $inputFormat;
        $outputFormat = self::FORMAT_MAPPING[$outputFormat] ?? $outputFormat;

        $dateTimeObj = DateTime::createFromFormat($inputFormat, $datetimeString);

        if ($dateTimeObj instanceof DateTime) {
            return $dateTimeObj->format($outputFormat);
        }

        // Return the given datetime string if an error occurred
        return $datetimeString;
    }
}