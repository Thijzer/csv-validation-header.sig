<?php

namespace Misery\Command;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Assert\Assert;
use Assert\Assertion;
use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Compare\ItemCompare;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Reader\ItemReader;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

class CompareCommand extends Command
{
    private $master;
    private $branch;
    private $reference;
    private $delimiter;
    private $excluded;

    public function __construct()
    {
        parent::__construct('compare', 'Compare files');

        $this
            ->option('-m --master', 'The master file')
            ->option('-b --branch', 'The branch file')
            ->option('-r --reference', 'The identities to align (comma sep)')
            ->option('-d --delimiter', 'The delimiter (;)', null, ';')
            ->option('-e --excluded', 'exclude keys you don\'t want to compare (comma sep)')
            ->usage(
                '<bold>  compare</end> <comment>--master /path/to/master --branch /path/to/branch --reference code</end> ## detailed<eol/>'.
                '<bold>  compare</end> <comment>-m /path/to/master -b /path/to/branch -r code</end> ## short<eol/>'
            )
        ;
    }

    public function execute(string $master, string $branch, string $reference, string $delimiter, string $excluded = null)
    {
        $io = $this->app()->io();

        Assertion::file($master);
        Assertion::file($branch);

        $compare = new ItemCompare(
            CsvParser::create($master, $delimiter),
            CsvParser::create($branch, $delimiter),
            array_filter(explode(',', $excluded))
        );

        $report = $compare->compare(...array_filter(explode(',', $reference)));

        dump($report);

        if (isset($report['headers']['REMOVED'])) {
            $report['items'] = [];
        }

        foreach (array_slice($report['items'][ItemCompare::CHANGED], -50) as $item) {
            dump($item);
        }

        // Changed References
        dump(array_column($report['items'][ItemCompare::CHANGED], $reference));
    }
}