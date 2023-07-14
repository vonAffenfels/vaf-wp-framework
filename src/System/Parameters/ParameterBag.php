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

    public function toArray(): array
    {
        return array_map(function (Parameter $param) {
            return $param->toArray();
        }, $this->params);
    }

    public static function fromArray(array $data): self
    {
        $bag = new ParameterBag();
        foreach ($data as $param) {
            $bag->addParam(Parameter::fromArray($param));
        }
        return $bag;
    }
}
