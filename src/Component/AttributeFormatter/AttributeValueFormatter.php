<?php

namespace Misery\Component\AttributeFormatter;

/**
 * rename or move to Akeneo Namespace
 */
class AttributeValueFormatter
{
    private array $attributeTypesAndCodes = [];
    private PropertyFormatterRegistry $formatterRegistry;
    private array $focusGroup = [];

    public function __construct(PropertyFormatterRegistry $formatterRegistry)
    {
        $this->formatterRegistry = $formatterRegistry;
    }

    public function setAttributeTypesAndCodes($attributeTypesAndCodes): void
    {
        $this->attributeTypesAndCodes = $attributeTypesAndCodes;
    }

    public function setFocusGroup(array $focusGroup): void
    {
        $this->focusGroup = $focusGroup;
    }

    private function getAttributeTypeFromCode(string $code): ?string
    {
        return $this->attributeTypesAndCodes[$code] ?? null;
    }

    public function needsFormatting(string $code): bool
    {
        return null !== $this->getAttributeTypeFromCode($code) && in_array(
                $this->getAttributeTypeFromCode($code),
                $this->focusGroup,
            );
    }

    public function format(string $code, $value, array $context = [])
    {
        $type = $this->getAttributeTypeFromCode($code);
        if (null === $type) {
            return $value;
        }

        $context['current-attribute-code'] = $code;
        $context['current-attribute-type'] = $type;
        $context = isset($context[$type]) ? $context + $context[$type]: $context;

        /** @var PropertyFormatterInterface $formatter */
        foreach ($this->formatterRegistry->findByType($type) as $formatter) {
            if ($formatter instanceof RequiresContextInterface && false === $formatter->requires($context)) {
                continue;
            }

            $value = $formatter->format($value, $context);
        }

        return $value;
    }
}