<?php

declare(strict_types=1);

namespace Doctrine\Website\Commands;

use Doctrine\Website\Docs\BuildDocs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function is_bool;
use function is_string;

class BuildDocsCommand extends Command
{
    /** @var string|null */
    protected static $defaultName = 'build-docs';

    /** @var BuildDocs */
    private $buildDocs;

    public function __construct(BuildDocs $buildDocs)
    {
        $this->buildDocs = $buildDocs;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Build the RST docs.')
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The project to build the docs for.',
                '',
            )
            ->addOption(
                'v',
                null,
                InputOption::VALUE_REQUIRED,
                'The project version to build the docs for.',
                '',
            )
            ->addOption(
                'search',
                null,
                InputOption::VALUE_NONE,
                'Build the search indexes.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectToBuild = $input->getOption('project');
        assert(is_string($projectToBuild));

        $versionToBuild = $input->getOption('v');
        assert(is_string($versionToBuild));

        $buildSearchIndexes = $input->getOption('search');
        assert(is_bool($buildSearchIndexes));

        $this->buildDocs->build(
            $output,
            $projectToBuild,
            $versionToBuild,
            $buildSearchIndexes,
        );

        return 0;
    }
}
