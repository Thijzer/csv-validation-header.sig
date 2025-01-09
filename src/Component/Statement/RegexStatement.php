<?php

namespace Misery\Component\Statement;

class RegexStatement implements PredeterminedStatementInterface
{
    public const NAME = 'REGEX';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $field->repopulateFromItem($item);
        $fieldValue = $field->getValue();
        $itemValue = $item[$field->getField()] ?? null;

        if (!isset($itemValue) || !is_string($itemValue)) {
            return false;
        }

        // Validate the regex pattern
        if (!$this->isValidRegex($fieldValue)) {
            // Log or handle invalid regex pattern
            return false;
        }

        return preg_match($fieldValue, $itemValue) === 1;
    }

    /**
     * Validates if a given string is a valid regex pattern.
     *
     * @param string $pattern The regex pattern to validate.
     * @return bool Returns true if the pattern is valid, false otherwise.
     */
    private function isValidRegex(string $pattern): bool
    {
        // Suppress errors and check if the regex is valid
        return @preg_match($pattern, '') !== false;
    }
}