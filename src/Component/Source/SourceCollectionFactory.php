<?php

namespace Misery\Component\Source;

use Assert\Assert;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Item\Processor\DecoderProcessor;
use Misery\Component\Item\Processor\EncoderProcessor;
use Misery\Component\Item\Processor\NullProcessor;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Parser\JsonFileParser;
use Misery\Component\Parser\XmlParser;
use Misery\Component\Parser\YamlParser;

class SourceCollectionFactory implements RegisteredByNameInterface
{
    public function createFromFileManager(LocalFileManager $manager, SourceCollection $sourceCollection = null): SourceCollection
    {
        $sourceCollection = $sourceCollection ?: new SourceCollection('manager');
        foreach ($manager->listFiles() as $file) {
            $this->addSourceFileToCollection($sourceCollection, $file);
        }

        return $sourceCollection;
    }

    public function createFromConfiguration(array $configuration, SourceCollection $sourceCollection = null): SourceCollection
    {
        $sourceCollection = $sourceCollection ?: new SourceCollection('manager');

        foreach ($configuration as $alias => $sourcePath) {
            $this->addSourceFileToCollection($sourceCollection, $sourcePath, is_string($alias) ? $alias : null);
        }

        return $sourceCollection;
    }

    /**
     * This method will convert a regular source into a blueprint compatible source
     */
    public function createFromBluePrint(BluePrint $bluePrint, SourceCollection $sourceCollection = null)
    {
        foreach ($sourceCollection->getAliases() as $alias) {
            // find a blueprint match
            if (in_array($alias, $bluePrint->getFilenames())) {
                $source = $sourceCollection->get($alias);
                $sourceCollection->add(
                    new Source(
                        $source->getCursor(),
                        new EncoderProcessor($bluePrint->getEncoder()),
                        new DecoderProcessor($bluePrint->getDecoder()),
                        $alias
                    )
                );
            }
        }
    }

    private function addSourceFileToCollection(SourceCollection $sourceCollection, string $file, ?string $alias = null): void
    {
        Assert::that($file)->file();

        $path = pathinfo($file);
        if (in_array(strtolower($path['extension']), ['json', 'buffer'])) {
            $sourceCollection->add(
                Source::createSimple(JsonFileParser::create($file), $alias ?? $path['basename'])
            );
        }
        if (strtolower($path['extension']) === 'csv') {
            $sourceCollection->add(
                Source::createSimple(CsvParser::create($file), $alias ?? $path['basename'])
            );
        }
        if (strtolower($path['extension']) === 'xml') {
            $sourceCollection->add(
                Source::createSimple(XmlParser::create($file), $alias ?? $path['basename'])
            );
        }
        if (in_array(strtolower($path['extension']), ['yaml', 'yml'])) {
            $sourceCollection->add(
                Source::createSimple(YamlParser::create($file), $alias ?? $path['basename'])
            );
        }
    }

    public static function create(ItemEncoderFactory $encoderFactory, array $sourcePaths): SourceCollection
    {
        // TODO open up the SourceCollection
        $sources = new SourceCollection('akeneo/csv');

        foreach ($sourcePaths as $reference => $sourcePath) {
            $configuration = $sourcePath['blueprint'] ?? [];
            $sources->add(new Source(
                self::createEncodedCursor(
                    $configuration,
                    $sourcePath['source']
                ),
                new EncoderProcessor($encoderFactory->createItemEncoder($configuration)),
                new NullProcessor(),
                $reference
            ));
        }

        return $sources;
    }

    private static function createEncodedCursor(array $configuration, string $source): CachedCursor
    {
        Assert::that($configuration['parse'])->keyIsset('type')->notEmpty()->inArray(['csv']);

        $format = $configuration['parse']['format'];

        return new CachedCursor(
            CsvParser::create(
                $source,
                $format['delimiter'],
                $format['enclosure']
            ),
            ['cache_size' => CachedCursor::LARGE_CACHE_SIZE]
        );
    }

    public function getName(): string
    {
        return 'source';
    }
}
