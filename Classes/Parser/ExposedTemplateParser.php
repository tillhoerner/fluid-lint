<?php

namespace Lemming\FluidLint\Parser;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ExposedTemplateParser extends TemplateParser
{
    protected array $splitTemplate = [];

    protected array $viewHelpersUsed = [];

    public function __construct()
    {
        $factory = GeneralUtility::makeInstance(RenderingContextFactory::class);
        $this->setRenderingContext($factory->create());
    }

    public function getUniqueViewHelpersUsed(): array
    {
        $names = [];
        foreach ($this->viewHelpersUsed as $metadata) {
            list($namespace, $viewhelper) = array_values($metadata);
            $id = $namespace . ':' . $viewhelper;
            if (in_array($id, $names) === false) {
                $names[] = $id;
            }
        }
        return $names;
    }

    /**
     * Parses a given template string and returns a parsed template object.
     *
     * The resulting ParsedTemplate can then be rendered by calling evaluate() on it.
     *
     * Normally, you should use a subclass of AbstractTemplateView instead of calling the
     * TemplateParser directly.
     *
     * @param string $templateString The template to parse as a string
     * @param string|null $templateIdentifier If the template has an identifying string it can be passed here to improve error reporting.
     * @return ParsingState Parsed template
     * @throws Exception
     */
    public function parse($templateString, $templateIdentifier = null): ParsingState
    {
        if (!is_string($templateString)) {
            throw new Exception(
                'Parse requires a template string as argument, ' . gettype($templateString) . ' given.',
                1224237899
            );
        }
        try {
            $this->reset();

            $templateString = $this->preProcessTemplateSource($templateString);

            $splitTemplate = $this->splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
            $parsingState = $this->buildObjectTree($splitTemplate, self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
        } catch (Exception $error) {
            throw $this->createParsingRelatedExceptionWithContext($error, $templateIdentifier);
        }
        $this->parsedTemplates[$templateIdentifier] = $parsingState;
        return $parsingState;
    }

    public function getRenderingContext(): RenderingContextInterface
    {
        return $this->renderingContext;
    }

    protected function initializeViewHelperAndAddItToStack(
        ParsingState $state,
        $namespaceIdentifier,
        $methodIdentifier,
        $argumentsObjectTree
    ): ?NodeInterface {
        $this->viewHelpersUsed[] = [
            'namespace' => $namespaceIdentifier,
            'viewhelper' => $methodIdentifier,
        ];
        return parent::initializeViewHelperAndAddItToStack(
            $state,
            $namespaceIdentifier,
            $methodIdentifier,
            $argumentsObjectTree
        );
    }
}
