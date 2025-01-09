<?php

namespace Tests\Misery\Component\Converter\Akeneo;

use Misery\Component\Akeneo\AkeneoTypeBasedDataConverter;
use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

class AkeneoTypeBasedDataConverterTest extends TestCase
{
    public function testGetAkeneoDataStructure()
    {
        // Define test data
        $attributeTypesList = ['text_attribute' => AkeneoHeaderTypes::TEXT, 'metric_attribute' => AkeneoHeaderTypes::METRIC];
        $attributesList = [];
        $defaultMetricsList = ['metric_attribute' => 'METER'];
        $localizableCodes = ['localizable_attribute'];
        $scopableCodes = ['scopable_attribute'];
        $readerMock = $this->createMock(ReaderInterface::class);
        $attributeOptionLabel = 'option_label';
        $defaultLocale = 'en_US';
        $defaultScope = 'ecommerce';
        $defaultCurrency = 'USD';

        // Instantiate the AkeneoTypeBasedDataConverter with test data
        $converter = new AkeneoTypeBasedDataConverter(
            $attributeTypesList,
            $attributesList,
            $defaultMetricsList,
            $localizableCodes,
            $scopableCodes,
            $readerMock,
            $attributeOptionLabel,
            $defaultLocale,
            $defaultScope,
            $defaultCurrency
        );

        // Test case 1: Text attribute
        $textAttributeCode = 'text_attribute';
        $textValue = 'Test Text Value';
        $result = $converter->getAkeneoDataStructure($textAttributeCode, $textValue);
        $expectedResult = [
            'locale' => null,
            'scope' => null,
            'data' => $textValue,
        ];
        $this->assertEquals($expectedResult, $result);

        // Test case 2: Metric attribute
        $metricAttributeCode = 'metric_attribute';
        $metricValue = 42.5;
        $result = $converter->getAkeneoDataStructure($metricAttributeCode, $metricValue);
        $expectedResult = [
            'locale' => null,
            'scope' => null,
            'data' => [
                'amount' => $metricValue,
                'unit' => 'METER',
            ],
        ];
        $this->assertEquals($expectedResult, $result);

        // Add more test cases for other attribute types as needed
    }
}
