<?php

namespace VAF\WP\Framework;

class UnscopedFunction
{
    private string $name;

    public static function fromName(string $name): self
    {
        $unscopedFunction = new static();

        $unscopedFunction->name = $name;

        return $unscopedFunction;
    }

    public function scopedReplacement($scope): array
    {

        return [
            "'{$this->name}'" => sprintf("'%s\\\\{$this->name}'", $scope),
            "'{$this->name}(" => sprintf("'%s\\\\{$this->name}(", $scope),
        ];
    }
}
