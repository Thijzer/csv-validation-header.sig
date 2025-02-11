<?php

namespace Misery\Component\Source\Command;

use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Component\Source\SourceAwareInterface;
use Misery\Component\Source\SourceTrait;
use Misery\Component\Statement\StatementBuilder;

class HeaderFactoryCommand implements SourceAwareInterface, ExecuteSourceCommandInterface, RegisteredByNameInterface
{
    use SourceTrait;
    use OptionsTrait;

    private $reader;

    private $options = [
        'rules' => [],
        'cache' => [],
        'statement' => null,
        'flip' => false,
    ];

    public function execute(): array
    {
        if ($this->reader === null) {
            $this->reader = $this->getSource()->getCachedReader($this->getOption('cache'));

            # disabled, there is a bug that cannot process a statement after statement
            # Exception : Cannot traverse an already closed generator, /app/src/Component/Reader/ItemReader.php:105

//            if ($this->getOption('statement')) {
//                $statement = StatementBuilder::build(
//                    $this->getOption('statement')
//                );
//
//                $this->reader = $this->reader->filter(function ($item) use ($statement) {
//                    return $statement->isApplicable($item);
//                });
//            }
        }

        $rules = $this->getOption('rules');
        $headers = [];

        foreach ($rules as $rule) {
            $statement = StatementBuilder::build($rule['when'], $rule['context'] ?? []);

            $reader = $this->reader->filter(function ($item) use ($statement) {
                return $statement->isApplicable($item);
            });

            foreach ($reader->getIterator() as $item) {
                $formats = is_string($rule['format']) ? [$rule['format']] : $rule['format'];

                foreach ($formats as $key => $format) {
                    $format = ValueFormatter::format($format, $item);
                    $code = false === is_numeric($key) ? $item['code'] .'|'. $key: $item['code'];
                    $headers[$format] = $code;
                }
            }
        }

        if ($this->getOption('flip')) {
            return array_flip($headers);
        }

        return $headers;
    }

    public function executeWithOptions(array $options)
    {
        $this->setOptions($options);

        return $this->execute();
    }

    public function getName(): string
    {
        return 'header_factory';
    }
}