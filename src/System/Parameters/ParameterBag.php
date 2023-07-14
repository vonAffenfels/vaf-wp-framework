<?php

namespace VAF\WP\Framework\System\Parameters;

class ParameterBag
{
    private array $params = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function addParam(Parameter $param): self
    {
        $this->params[] = $param;
        return $this;
    }
}
