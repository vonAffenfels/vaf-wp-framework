<?php

namespace VAF\WP\Framework\System\Parameters;

class Parameter
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly bool $isOptional,
        private readonly mixed $default
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'isOptional' => $this->isOptional,
            'default' => $this->default
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            isOptional: $data['isOptional'],
            default: $data['default']
        );
    }
}
