<?php

namespace Lemming\FluidLint\Service;

use Lemming\FluidLint\Utility\GlobUtility;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CommandService implements SingletonInterface
{
    private SymfonyStyle $output;

    private SyntaxService $syntaxService;

    private ExtensionService $extensionService;

    public function __construct(?SyntaxService $syntaxService = null, ?ExtensionService $extensionService = null)
    {
        $this->syntaxService = $syntaxService ?? GeneralUtility::makeInstance(SyntaxService::class);
        $this->extensionService = $extensionService ?? GeneralUtility::makeInstance(ExtensionService::class);
    }

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
     * @param string $extensions If provided, this CSV list of file extensions are considered Fluid templates
     * @param bool $verbose If TRUE outputs more information about each file check - default is to only output errors
     * @return int
     */
    public function checkFluidSyntax(
        ?string $extension = null,
        ?string $path = null,
        string $extensions = 'html,xml,txt',
        bool $verbose = false
    ): int {
        if ($extension) {
            $path = GlobUtility::getRealPathFromExtensionKeyAndPath($extension, $path);
            $files = GlobUtility::getFilesRecursive($path, $extensions);
        } else {
            // no extension key given, let's lint it all
            $files = [];
            $extensionInformation = $this->extensionService->gatherInformation();
            foreach ($extensionInformation as $extensionName => $extensionInfo) {
                // Syntax service declines linting of inactive extensions
                if ((int)($extensionInfo['installed']) === 0 || $extensionInfo['type'] === 'System') {
                    continue;
                }
                $path = GlobUtility::getRealPathFromExtensionKeyAndPath($extensionName);
                $files = array_merge($files, GlobUtility::getFilesRecursive($path, $extensions));
            }
        }
        $files = array_values($files);
        $errors = false;
        $this->output->writeln('Performing a syntax check on fluid templates (types: ' . $extensions . '; path: ' . $path . ')');

        foreach ($files as $filePathAndFilename) {
            $basePath = str_replace(Environment::getProjectPath(), '', $filePathAndFilename);
            $result = $this->syntaxService->syntaxCheckFluidTemplateFile($filePathAndFilename);
            if ($result->getError()) {
                $this->output->error([
                    'File ' . $basePath,
                    $result->getError()->getMessage() . ' in ' . $result->getError()->getFile() . ' on line ' . $result->getError()->getLine(),
                ]);
                $errors = true;
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
        }
        return $this->stop($files, $errors, $verbose);
    }

    private function stop(array $files, bool $errors, bool $verbose): int
    {
        $code = (int)$errors;
        if ($verbose === true) {
            if ($errors === false) {
                $this->output->write('No errors encountered - ' . count($files) . ' file(s) are all okay' . PHP_EOL);
            } else {
                $this->output->write('Errors were detected - review the summary above' . PHP_EOL);
            }
        }

        return $code;
    }
}
