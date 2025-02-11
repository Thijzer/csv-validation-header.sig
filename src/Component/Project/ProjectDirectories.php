<?php

namespace Misery\Component\Project;

use Assert\Assertion;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class ProjectDirectories implements RegisteredByNameInterface
{
    public function __construct(
        private string $bluePrintPath,
        private string $templatePath,
    ) {
        Assertion::directory($this->bluePrintPath);
        Assertion::directory($this->templatePath);
    }

    public function getBluePrint(): LocalFileManager
    {
        return new LocalFileManager($this->bluePrintPath);
    }

    public function getTemplatePath(): LocalFileManager
    {
        return new LocalFileManager($this->templatePath);
    }

    public function getName(): string
    {
        return 'project_directories';
    }
}