<?php

namespace Misery\Component\Akeneo;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Reader\ReaderInterface;
use function PHPUnit\Framework\throwException;

class AkeneoTypeBasedDataConverter
{
    private array $attributeTypesList;
    private array $attributesList;
    private array $defaultMetricsList;
    private array $localizableCodes;
    private array $scopableCodes;
    private ?string $defaultLocale;
    private ?string $defaultScope;
    private ?string $attributeOptionLabel;
    private ?string $defaultCurrency;

    public function __construct(
        array $attributeTypesList = [],
        array $attributesList = [],
        array $defaultMetricsList = [],
        array $localizableCodes = [],
        array $scopableCodes = [],
        private ?ReaderInterface $reader = null,
        string $attributeOptionLabel = null,
        string $defaultLocale = null,
        string $defaultScope = null,
        string $defaultCurrency = null
    ) {
        $this->attributeTypesList = $attributeTypesList;
        $this->attributesList = $attributesList;
        $this->defaultMetricsList = $defaultMetricsList;
        $this->localizableCodes = $localizableCodes;
        $this->attributeOptionLabel = $attributeOptionLabel;
        $this->scopableCodes = $scopableCodes;
        $this->defaultLocale = $defaultLocale;
        $this->defaultScope = $defaultScope;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function getAkeneoDataStructure(string $attributeCode, $value): array
    {
        $type = $this->attributeTypesList[$attributeCode] ?? null;

        if (null === $type) {
            throw new \Exception(sprintf('Unknown attribute Type for code %s', $attributeCode));
        }

        if (is_array($value)) {
            if (
                array_key_exists('locale', $value) &&
                array_key_exists('data', $value) &&
                array_key_exists('scope', $value)
            ) {
                return $value;
            }
        }

        $localizable = in_array(
            $attributeCode,

            $this->localizableCodes
        );
        $scopable = in_array(
            $attributeCode,
            $this->scopableCodes
        );

        switch ($type) {
            case AkeneoHeaderTypes::TEXT:
                // no changes
                break;
            case AkeneoHeaderTypes::NUMBER:
                $value = $this->numberize($value);
                break;
            case AkeneoHeaderTypes::SELECT:
                // TODO implement attributes reader
                //$value = $this->findAttributeOptionCode($attributeCode, $value);
                break;
            case AkeneoHeaderTypes::MULTISELECT:
                // TODO implement attributes reader
                //$value = [$this->findAttributeOptionCode($attributeCode, $value)];
                break;
            case AkeneoHeaderTypes::METRIC:
                $amount = null;
                $unit = $this->defaultMetricsList[$attributeCode] ?? null;
                if (is_numeric($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('unit', $value)) {
                        $unit = $value['unit'];
                    }
                }

                $value = [
                    'amount' => $amount,
                    'unit' => $unit,
                ];
                break;
            case AkeneoHeaderTypes::PRICE:
                $amount = null;
                $unit = $this->defaultCurrency;
                if (is_numeric($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('currency', $value)) {
                        $unit = $value['currency'];
                    }
                }

                $value = [
                    'amount' => $amount,
                    'currency' => $unit,
                ];
                break;
        }

        return [
            'locale' => $localizable ? $this->defaultLocale : null,
            'scope' => $scopable ? $this->defaultScope : null,
            'data' => $value,
        ];
    }

    private function numberize($value)
    {
        if (is_integer($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    /**
     * This function return the option_code that was made earlier
     * When generating option codes we expect a full export strategy
     */
    private function findAttributeOptionCode(string $attributeCode, string $optionLabel)
    {
        return $this->reader->find([
                'attribute' => $attributeCode,
                $this->attributeOptionLabel => $optionLabel]
        )->getIterator()->current()['code'];
    }

    /**
     * This function will extract attribute values from the item based on the attribute:list
     */
    private function getProductValues(array $item): \Generator
    {
        foreach ($this->att ?? [] as $attributeCode) {
            if (array_key_exists($attributeCode, $item)) {
                yield $attributeCode => $item[$attributeCode];
            }
        }
    }
}