<?php

namespace Tests\Misery\Component\Configurator;

use Misery\Component\Common\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Pipeline\Pipeline;
use Misery\Component\Shell\ShellCommands;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Writer\ItemWriterInterface;
use Misery\Component\Reader\ReaderInterface;

class ConfigurationTest extends TestCase
{
    public function testSetAndGetPipeline()
    {
        $configuration = new Configuration();
        $pipeline = $this->createMock(Pipeline::class);

        $configuration->setPipeline($pipeline);

        $this->assertSame($pipeline, $configuration->getPipeline());
    }

    public function testSetAndGetExtensions()
    {
        $configuration = new Configuration();

        // Create a mock for LocalFileManager
        $fileManager = $this->createMock(LocalFileManager::class);

        // Simulate a generator using a closure
        $fileManager->method('listFiles')->willReturn((function () {
            yield '/path/to/file1.txt';
            yield '/path/to/file2.txt';
        })());

        // Set extensions using the mock
        $configuration->setExtensions($fileManager);

        // Retrieve and verify the extensions
        $extensions = $configuration->getExtensions();

        $this->assertCount(2, $extensions);
        $this->assertArrayHasKey('file1.txt', $extensions);
        $this->assertArrayHasKey('file2.txt', $extensions);
        $this->assertInstanceOf(\SplFileInfo::class, $extensions['file1.txt']);
        $this->assertInstanceOf(\SplFileInfo::class, $extensions['file2.txt']);
    }

    public function testIsMultiStep()
    {
        $configuration = new Configuration();

        $this->assertFalse($configuration->isMultiStep());

        $configuration->setAsMultiStep();

        $this->assertTrue($configuration->isMultiStep());
    }

    public function testAddAndGetContext()
    {
        $configuration = new Configuration();
        $context = ['key1' => 'value1', 'key2' => 'value2'];

        $configuration->addContext($context);

        $this->assertEquals($context, $configuration->getContext());
        $this->assertEquals('value1', $configuration->getContext('key1'));
        $this->assertNull($configuration->getContext('nonexistent_key'));
    }

    public function testAddAndGetAccount()
    {
        $configuration = new Configuration();
        $apiClient = $this->createMock(ApiClientInterface::class);

        $configuration->addAccount('test', $apiClient);

        $this->assertSame($apiClient, $configuration->getAccount('test'));
        $this->assertNull($configuration->getAccount('nonexistent'));
    }

    public function testSetAndGetShellCommands()
    {
        $configuration = new Configuration();
        $shellCommands = $this->createMock(ShellCommands::class);

        $configuration->setShellCommands($shellCommands);

        $this->assertSame($shellCommands, $configuration->getShellCommands());

        $configuration->clearShellCommands();

        $this->assertNull($configuration->getShellCommands());
    }

    public function testAddAndGetBlueprints()
    {
        $configuration = new Configuration();
        $bluePrint1 = $this->createMock(BluePrint::class);
        $bluePrint1->method('getName')->willReturn('blueprint1');
        $bluePrint2 = $this->createMock(BluePrint::class);
        $bluePrint2->method('getName')->willReturn('blueprint2');

        $configuration->addBlueprint($bluePrint1);
        $configuration->addBlueprint($bluePrint2);

        $this->assertSame($bluePrint1, $configuration->getBlueprint('blueprint1'));
        $this->assertFalse($configuration->getBlueprint('nonexistent'));
    }

    public function testAddAndGetSources()
    {
        $configuration = new Configuration();
        $sourceCollection = $this->createMock(SourceCollection::class);

        $configuration->addSources($sourceCollection);

        $this->assertSame($sourceCollection, $configuration->getSources());
    }

    public function testSetAndGetReader()
    {
        $configuration = new Configuration();
        $reader = $this->createMock(ReaderInterface::class);

        $configuration->setReader($reader);

        $this->assertSame($reader, $configuration->getReader());
    }

    public function testSetAndGetWriter()
    {
        $configuration = new Configuration();
        $writer = $this->createMock(ItemWriterInterface::class);

        $configuration->setWriter($writer);

        $this->assertSame($writer, $configuration->getWriter());
    }

    public function testClearConfiguration()
    {
        $configuration = new Configuration();
        $writer = $this->createMock(ItemWriterInterface::class);

        $configuration->setWriter($writer);

        $configuration->clear();

        $this->assertNull($configuration->getWriter());
        $this->assertNull($configuration->getPipeline());
    }

    public function testAddAndGetLists()
    {
        $configuration = new Configuration();

        // Adding lists
        $list1 = ['item1', 'item2', 'item3'];
        $list2 = ['itemA', 'itemB', 'itemC'];

        $configuration->addList('list1', $list1);
        $configuration->addList('list2', $list2);

        // Retrieving lists
        $lists = $configuration->getLists();

        $this->assertCount(2, $lists);
        $this->assertArrayHasKey('list1', $lists);
        $this->assertArrayHasKey('list2', $lists);

        $collection1 = new ArrayCollection($list1);
        $collection2 = new ArrayCollection($list2);
        $this->assertEquals($collection1, $lists['list1']);
        $this->assertEquals($collection2, $lists['list2']);

        // Updating a list
        $updatedList = ['itemX', 'itemY', 'itemZ'];
        $configuration->updateList('list1', $updatedList);

        $updatedLists = $configuration->getLists();

        $updatedCollectionList = new ArrayCollection($updatedList);
        $this->assertEquals($updatedCollectionList, $updatedLists['list1']);

        // Retrieving a single list
        $retrievedList = $configuration->getList('list1');
        $this->assertEquals($updatedList, $retrievedList);

        // Non-existent list
        $nonExistentList = $configuration->getList('nonexistent');
        $this->assertNull($nonExistentList);
    }
}