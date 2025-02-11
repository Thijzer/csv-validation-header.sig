<?php

namespace Misery\Component\Akeneo\Header;

class AkeneoHeader
{
    /** @var array */
    private $list = [];
    /** @var array */
    private $context;

    private $lines;

    public function __construct(array $context = [])
    {
        $this->context = array_merge([
            'locale_codes' => [],
            'scope_codes' => [],
            'currencies' => [],
            'is_product_value' => false,
            'has_locale' => false,
            'has_scope' => false,
        ], $context);
    }

    public function addValue($code, string $type, array $context = []): void
    {
        if (isset($this->list[$code])) {
            return;
        }
        $this->list[$code] = array_merge($this->context, $context, [
            'code' => $code,
            'type' => $type,
        ]);
    }

    public function createItemHeader($code, array $context = []):? string
    {
        $code = $this->concat(array_filter([$code, $context['locale'] ?? null, $context['scope'] ?? null, $context['extra'] ?? null]));
        //if (isset($this->getHeaders()[$code])) {
        return $code;
        //}
        return null;
    }

    public function getContext($code):? array
    {
        return $this->list[$code] ?? null;
    }

    public function getHeaders(): array
    {
        if (null === $this->lines) {
            foreach (array_keys($this->list) as $code) {
                $this->createHeaderFromCode($code);
            }
        }

        return $this->lines;
    }

    /**
     * finding the rows
     *
     * <code>-<locale>-<scope>-<type-spec>
     *
     * 3 locales + 2 scopes = 3x2 = 6 headers for 1 code
     *
     * description-en_US-ecommerce|description-en_US
     * scan-size|scan-size-unit
     * price-EUR|price-USD
     *
     */
    private function createHeaderFromCode($code): void
    {
        $header = $this->list[$code] ?? null;

        if (true === $header['has_locale'] && false === $header['has_scope'] ) {
            foreach ($this->context['locale_codes'] as $localeCode) {
                $this->applyWithType($header['type'], $code, $localeCode);
            }
            return;
        }
        if (false === $header['has_locale'] && true === $header['has_scope'] ) {
            foreach ($this->context['locale_codes'] as $localeCode) {
                foreach ($this->context['scope_codes'] as $scopeCode) {
                    $this->applyWithType($header['type'], $code, $localeCode, $scopeCode);
                }
            }
            return;
        }
        if (true === $header['has_locale'] && true === $header['has_scope'] ) {
            foreach ($this->context['scope_codes'] as $scopeCode) {
                $this->applyWithType($header['type'], $code, $scopeCode);
            }
            return;
        }
        $this->applyWithType($header['type'], $code);
    }

    private function addLine(string $line)
    {
        $this->lines[$line] = null;
    }

    private function applyWithType(string $type, ...$strings): void
    {
        if (in_array($type, [AkeneoHeaderTypes::METRIC, 'pim_catalog_metric_as400'])) {
            $this->addLine($this->concat($strings));
            $strings[] = 'unit';
        }
        elseif ($type === AkeneoHeaderTypes::PRICE) {
            $strings[] = 'EUR';
        }
        $this->addLine($this->concat($strings));
    }

    private function concat(array $strings): string
    {
        return implode('-', $strings);
    }
}