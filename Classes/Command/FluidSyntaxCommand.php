<?php

namespace Lemming\FluidLint\Command;

use Lemming\FluidLint\Service\CommandService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FluidSyntaxCommand extends Command
{
    private CommandService $commandService;

    public function __construct(?CommandService $commandService = null)
    {
        parent::__construct();
        $this->commandService = $commandService ?? GeneralUtility::makeInstance(CommandService::class);
    }

    protected function configure()
    {
        $this->setDescription('Fluid Lint: Check Fluid syntax')
            ->setHelp('Checks the syntax validity of Fluid files')
            ->addOption(
                'base-dir',
                'b',
                InputOption::VALUE_REQUIRED,
                'Extensions in given directory'
            )->addOption(
                'extension',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Extension key to check'
            )->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'File or folder path (if extensionKey is included, path is relative to this extension)'
            )->addOption(
                'regex',
                'r',
                InputOption::VALUE_REQUIRED,
                'Extension key must match regular expression',
                '.*'
            )->addOption(
                'file-extensions',
                'x',
                InputOption::VALUE_REQUIRED,
                'If provided, this CSV list of file extensions are considered Fluid templates',
                'html,xml,txt'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extension = $input->getOption('extension');
        $path = $input->getOption('path');
        $fileExtensions = $input->getOption('file-extensions');
        $verbose = (bool)$input->getOption('verbose');
        $extensionKeyRegex = $input->getOption('regex');
        $extensionBaseDirectory = $input->getOption('base-dir');
        $this->commandService->setOutput(new SymfonyStyle($input, $output));
        return $this->commandService->checkFluidSyntax($extension, $path, $fileExtensions, $verbose, $extensionKeyRegex, $extensionBaseDirectory);
    }
}
