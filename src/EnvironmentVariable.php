<?php

namespace VAF\WP\Framework;

class EnvironmentVariable
{

    private string $name;

    public static function fromName(string $name): self
    {
    	$environmentVariable = new static();

        $environmentVariable->name = $name;

    	return $environmentVariable;
    }

    public function boolOrNull(): ?bool
    {
        return match (strtolower(getenv($this->name))) {
            'true', '1', 'on', 'yes' => true,
            'false', '0', 'off', 'no' => false,
            default => null,
        };
    }
}
