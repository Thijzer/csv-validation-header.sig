<?php

namespace Misery\Component\Feed;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

/**
 * @deprecated
 */
class CategoryFeed implements FeedInterface, OptionsInterface, RegisteredByNameInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    private $header;
    private $options = [
        'level_1' => null,
        'level_2' => null,
        'level_3' => null,
    ];

    // in this version the feed supplies all feeds in one call
    // but it should also work like a cursor, reading until false so It could respect the cursor interface

    public function feed(): array
    {
        // L1
        foreach ($this->getOption('level_1') as $l1) {
            $build[] = $l1;
        }

        // L2
        foreach ($this->getOption('level_2') as $l2) {
            $build[] = [
                'code' => $l2['code'],
                'parent' => $l1['code'],
            ];
        }

        // L3
        foreach ($this->getOption('level_1') as $l1) {
            foreach ($this->getOption('level_2') as $l2) {
                foreach ($this->getOption('level_3') as $l3) {
                    $build[] = [
                        'code' => implode('_', [$l2['pre'], $l3['code']]),
                        'parent' => $l2['code'],
                    ];
                }
            }
        }

        return  $build;
    }

    public function getName(): string
    {
        return 'as400/cat-generator/feed';
    }
}