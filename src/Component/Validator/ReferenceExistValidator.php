<?php

namespace Misery\Component\Validator;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Item\Builder\ReferenceBuilder;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;
use Misery\Component\Validator\Constraint\ReferencedColumnConstraint;

class ReferenceExistValidator extends AbstractValidator implements ItemReaderAwareInterface, OptionsInterface
{
    use OptionsTrait;
    use ItemReaderAwareTrait;

    public const NAME = 'reference_exist';

    private $options = [
        'reference' => null,
        'source' => null,
        'file' => null,
    ];

    public function validate($cellValue, array $context = []): void
    {
        if (empty($cellValue)) {
            return;
        }

        $referencedValues = ReferenceBuilder::buildValues(
            $this->getReader(),
            $this->options['reference']
        );

        if (!\in_array($cellValue, $referencedValues, true)) {
            $this->getValidationCollector()->collect(
                new ReferencedColumnConstraint(),
                sprintf(
                    ReferencedColumnConstraint::UNKNOWN_REFERENCE,
                    $this->options['reference'],
                    $cellValue,
                    $this->options['file']
                ),
                $context
            );
        }
    }
}
