<?php

namespace Lemming\FluidLint\Result;

class FluidParserResult extends ParserResult
{
    protected array $namespaces = [];

    protected ?string $layoutName = null;

    protected bool $compilable = false;

    public function setLayoutName(string $layoutName)
    {
        $this->layoutName = $layoutName;
    }

    public function getLayoutName(): ?string
    {
        return $this->layoutName;
    }

    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function getNamespacesFlattened(): string
    {
        $flat = [];
        foreach ($this->namespaces as $namespaceAlias => $classPath) {
            $flat[] = $namespaceAlias . '=[' . implode(', ', (array)$classPath) . ']';
        }
        return implode(', ', $flat);
    }

    /**
     * @param bool $compilable
     */
    public function setCompilable(bool $compilable)
    {
        $this->compilable = $compilable;
    }

    public function getCompilable(): bool
    {
        return $this->compilable;
    }
}
