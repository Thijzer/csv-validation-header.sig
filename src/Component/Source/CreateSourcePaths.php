<?php

namespace Misery\Component\Source;

use Symfony\Component\Yaml\Yaml;

class CreateSourcePaths
{
    public static function create(array $sources, string $sourcePath, string $bluePrintPath = null): array
    {
        $references = [];
        foreach ($sources as $source) {
            $tmp = [];
            if (is_file($file = sprintf($sourcePath, $source))) {
                $tmp['source'] = $file;
            }
            if ($bluePrintPath && is_file($file = sprintf($bluePrintPath, $source))) {
                $tmp['blueprint'] = Yaml::parseFile($file);
            }
            $references[$source] = $tmp;
        }

        return $references;
    }
}