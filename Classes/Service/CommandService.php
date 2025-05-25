<?php

namespace Lemming\FluidLint\Service;

use Lemming\FluidLint\Utility\GlobUtility;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SingletonInterface;

class CommandService implements SingletonInterface
{
    private SymfonyStyle $output;
    private ?array $databaseConfiguration = null;

    public function __construct(
        protected SyntaxService $syntaxService,
        protected PackageManager $packageManager,
    ) {}

    public function setOutput(SymfonyStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * Syntax check Fluid template
     *
     * Checks one template file, all templates in
     * an extension or a sub-path (which can be used
     * with an extension key for a relative path).
     * If left out, it will lint ALL templates in
     * EVERY local extension.
     *
     * @param string $extension Optional extension key (if path is set too it will apply to sub-folders in extension)
     * @param string $path file or folder path (if extensionKey is included, path is relative to this extension)
     * @param string $fileExtensions If provided, this CSV list of file extensions are considered Fluid templates
     * @param bool $verbose If TRUE outputs more information about each file check - default is to only output errors
     * @return int
     */
    public function checkFluidSyntax(
        ?string $extension = null,
        ?string $path = null,
        string $fileExtensions = 'html,xml,txt,xml',
        bool $verbose = false,
        string $extensionKeyRegex = '.*',
        ?string $extensionBaseDirectory = null
    ): int {
        $this->backupDatabaseConfiguration();
        $files = $this->findFiles($extension, $path, $fileExtensions, $extensionBaseDirectory, $extensionKeyRegex);
        if ($files === []) {
            $this->output->info('No files found');
            return 0;
        }
        $progressBar = new ProgressBar($this->output, count($files));
        $progressBar->start();
        $errors = false;

        foreach ($files as $filePathAndFilename) {
            $error = $this->checkFile($filePathAndFilename, $verbose);
            if ($error) {
                $errors = true;
            }
            $progressBar->advance();
        }
        $this->restoreDatabaseConfiguration();
        return $this->stop($files, $errors, $verbose);
    }

    private function checkFile(string $filePathAndFilename, bool $verbose): bool
    {
        $error = false;
        $basePath = str_replace(Environment::getProjectPath(), '', $filePathAndFilename);
        $result = $this->syntaxService->syntaxCheckFluidTemplateFile($filePathAndFilename);
        if ($result->getError()) {
            $this->output->error([
                'File ' . $basePath,
                $result->getError()->getMessage() . ' in ' . $result->getError()->getFile() . ' on line ' . $result->getError()->getLine(),
            ]);
            $error = true;
        } else {
            $namespaces = $result->getNamespaces();
            $lines = [
                $basePath . ' is valid',
                'Is compilable: ' . ($result->getCompilable() ? 'YES' : 'NO (WARNING)'),
                $result->getLayoutName() ? 'Has layout (' . $result->getLayoutName() . ')' : 'Has NO layout',
                'File has ' . count($namespaces) . ' namespace(s)' .
                (count($namespaces) > 0 ? ': ' . $result->getNamespacesFlattened() : ''),
            ];
            if ($result->getViewHelpers() !== []) {
                $lines[] = 'ViewHelpers: ' . implode(',', $result->getViewHelpers());
            }
            if ($result->getCompilable()) {
                if ($verbose) {
                    $this->output->info($lines);
                }
            } else {
                $this->output->warning($lines);
            }
        }

        return $error;
    }

    private function stop(array $files, bool $errors, bool $verbose): int
    {
        $code = (int)$errors;
        if ($verbose === true) {
            if ($errors === false) {
                $this->output->info('No errors encountered - ' . count($files) . ' file(s) are all okay' . PHP_EOL);
            } else {
                $this->output->error('Errors were detected - review the summary above' . PHP_EOL);
            }
        }

        return $code;
    }

    private function backupDatabaseConfiguration()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'])) {
            $this->databaseConfiguration = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'];
        }
        foreach ($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'] ?? [] as $connectionName => $connectionConfig) {
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][$connectionName] = ['driver' => 'pdo_sqlite'];
        }
    }

    private function restoreDatabaseConfiguration()
    {
        if (!is_null($this->databaseConfiguration)) {
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'] = $this->databaseConfiguration;
        }
    }

    /**
     * @param string|null $extension
     * @param string|null $path
     * @param string $extensions
     * @param string|null $extensionBaseDirectory
     * @param string $extensionKeyRegex
     * @return array
     */
    protected function findFiles(
        ?string $extension,
        ?string $path,
        string $extensions,
        ?string $extensionBaseDirectory,
        string $extensionKeyRegex
    ): array {
        if ($extension) {
            $path = GlobUtility::getRealPathFromExtensionKeyAndPath($extension, $path);
            $files = GlobUtility::getFilesRecursive($path, $extensions);
        } else {
            $files = [];

            $extensionKeys = [];
            if ($extensionBaseDirectory) {
                $extensionKeys = GlobUtility::getExtensionKeysFromPath($extensionBaseDirectory);
            }

            foreach ($this->packageManager->getAvailablePackages() as $package) {
                if ($package->getPackageMetaData()->isFrameworkType()) {
                    continue;
                }
                if ((bool)preg_match('/' . $extensionKeyRegex . '/', $package->getPackageKey()) === false) {
                    continue;
                }
                if ($extensionKeys !== [] && !in_array($package->getPackageKey(), $extensionKeys)) {
                    continue;
                }
                $path = realpath($package->getPackagePath());
                $files = array_merge($files, GlobUtility::getFilesRecursive($path, $extensions));
            }
        }
        return $files;
    }
}
