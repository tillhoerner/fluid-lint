<?php

namespace Lemming\FluidLint\Service;

use Lemming\FluidLint\Parser\ExposedTemplateParser;
use Lemming\FluidLint\Result\FluidParserResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SyntaxService
{
    /**
     * Syntax checks a Fluid template file by attempting
     * to load the file and retrieve a parsed template, which
     * will cause traversal of the entire syntax node tree
     * and report any errors about missing or unknown arguments.
     *
     * Will NOT, however, report errors which are caused by
     * variables assigned to the template (there will be no
     * variables while building the syntax tree and listening
     * for errors).
     *
     * @param string $filePathAndFilename
     * @return FluidParserResult
     * @throws \Exception
     */
    public function syntaxCheckFluidTemplateFile($filePathAndFilename)
    {
        $result = new FluidParserResult();
        try {
            $parser = GeneralUtility::makeInstance(ExposedTemplateParser::class);
            $context = $parser->getRenderingContext();
            $parsedTemplate = $parser->parse(file_get_contents($filePathAndFilename));
            if ($parsedTemplate->hasLayout()) {
                $result->setLayoutName($parsedTemplate->getLayoutName($context) ?? '');
            }
            $result->setNamespaces($context->getViewHelperResolver()->getNamespaces());
            $result->setCompilable($parsedTemplate->isCompilable());
            $result->setViewHelpers($parser->getUniqueViewHelpersUsed());
        } catch (\Throwable $error) {
            $result->setError($error);
            $result->setValid(false);
        }
        return $result;
    }
}
