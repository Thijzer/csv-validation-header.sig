<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Source\SourceCollectionAwareInterface;
use Misery\Component\Source\SourceCollectionTrait;

class StructureModifier implements RowModifier, OptionsInterface, SourceCollectionAwareInterface
{
    use SourceCollectionTrait;
    use OptionsTrait;

    /** @var Registry */
    private $registry;
    private $converter;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public const NAME = 'structure';

    private $options = [
        'name' => null,
        'codes' => [],
    ];

    /** @inheritDoc */
    public function modify(array $item): array
    {
        return $this->getConverter()->convert($item);
    }

    /** @inheritDoc */
    public function reverseModify(array $item): array
    {
        return $this->getConverter()->revert($item);
    }

    private function getConverter(): ? ConverterInterface
    {
        if (null === $this->converter) {

            if ($this->converter = $this->registry->filterByAlias($this->options['name'])) {
                if ($this->converter instanceof SourceCollectionAwareInterface) {
                    $this->converter->setSourceCollection($this->getSourceCollection());
                }
            }
        }

        return $this->converter;
    }
}
