<?php

namespace Lemming\FluidLint\Result;

class ParserResult implements ParserResultInterface
{
    /**
     * @var bool
     */
    protected $valid = true;

    /**
     * @var \Throwable
     */
    protected $error;

    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var array
     */
    protected $viewHelpers = [];

    public function setError(\Throwable $error)
    {
        $this->error = $error;
    }

    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    /**
     * @param bool $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function getViewHelpers()
    {
        return $this->viewHelpers;
    }

    /**
     * @param array $viewHelpers
     */
    public function setViewHelpers(array $viewHelpers)
    {
        $this->viewHelpers = $viewHelpers;
    }
}
