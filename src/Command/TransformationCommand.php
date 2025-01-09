<?php

namespace Misery\Command;

use Ahc\Cli\Input\Command;
use Assert\Assertion;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Logger\OutputLogger;
use Misery\Component\Process\ProcessManager;
use Symfony\Component\Yaml\Yaml;

/**
 * @usage
 * bin/console transformation --file /path/to/transformation_file --sources /path/to/sources/dir --workpath /path/to/work-dir
 */
class TransformationCommand extends Command
{
    private $file;
    private $source;
    private $addSource;
    private $extensions;
    private $debug;
    private $showMappings;
    private $try;
    private $workpath;

    public function __construct()
    {
        parent::__construct('transformation', 'run a transform command based on a transformation file');

        $this
            ->option('-f --file', 'The transformation file location')
            ->option('-s --source', 'The sources location')
            ->option('-s --addSource', 'Add additional sources location', null)
            ->option('-s --extensions', 'Add extensions', null)
            ->option('-d --debug', 'enable debugging', 'boolval', false)
            ->option('-m --showMappings', 'show lists or mappings', 'boolval', false)
            ->option('-t --try', 'tryout a set for larger files')
            ->option('-l --line', 'target a line nr')
            ->option('-w --workpath', 'target work path')

            ->usage(
                '<bold>  transformation</end> <comment>--file /path/to/transformation_file --source /path/to/sources/dir</end> ## detailed<eol/>'.
                '<bold>  transformation</end> <comment>-f /path/to/transformation -s /path/to/sources/dir</end> ## short<eol/>'
            )
        ;
    }

    public function execute(string $file, string $source, string $workpath, bool $debug, string $addSource = null, string $extensions = null, int $line = null, int $try = null, bool $showMappings = null)
    {
        $io = $this->app()->io();

        Assertion::file($file);

        if (null !== $addSource) {
            Assertion::directory($addSource);
        }
        if (null !== $extensions) {
            Assertion::directory($extensions);
        }

        Assertion::directory($source);
        Assertion::directory($workpath);

        require_once __DIR__.'/../../src/bootstrap.php';

        $configurationFactory = initConfigurationFactory();
        $configurationFactory->init(
            new LocalFileManager($workpath),
            $source ? new LocalFileManager($source): null,
            $addSource ? new LocalFileManager($addSource): null,
            $extensions ? new LocalFileManager($extensions): null,
            new OutputLogger()
        );

        $transformationFile = ArrayFunctions::array_filter_recursive(Yaml::parseFile($file), function ($value) {
            return $value !== NULL;
        });
        $configuration = $configurationFactory->parseDirectivesFromConfiguration(
            array_replace_recursive($transformationFile, [
                'context' => [
                    # emulated operation datetime stamps
                    'operation_create_datetime' => (new \DateTime('NOW'))->format('Hd-m-Y-H-i-s'),
                    'last_completed_operation_datetime' => (new \DateTime('NOW'))->modify('-2 hours')->format('Hd-m-Y-H-i-s'),
                    'transformation_file' => $file,
                    'sources' => $source,
                    'scripts' => __DIR__.'/../../scripts',
                    'workpath' => $workpath,
                    'debug' => $debug,
                    'try' => $transformationFile['context']['try'] ?? $try,
                    'line' => $transformationFile['context']['line'] ?? $line,
                    'show_mappings' => $showMappings,
                ]
            ])
        );

        if (false === $configuration->isMultiStep()) {
            (new ProcessManager($configuration))->startProcess();

            // TODO connect the outputs here
            if ($shellCommands = $configuration->getShellCommands()) {
                $shellCommands->exec();
                $configuration->clearShellCommands();
            }
        }
    }
}