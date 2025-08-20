<?php

namespace VAF\WP\Framework\GutenbergBlock;

use ReflectionClass;

class RendererDefinition
{
    private string $type;
    private string $class;

    public static function fromClassReflection(ReflectionClass $reflection): self
    {
        $rendererDefinition = new static();

        $renderReflection = $reflection->getMethod('render');
        if (
            empty($renderReflection->getParameters())
            || !$renderReflection->getParameters()[0]->hasType()
            || $renderReflection->getParameters()[0]->getType()->isBuiltin()
            || !($renderReflection->getParameters()[0]->getType() instanceof \ReflectionNamedType)
        ) {
            $rendererDefinition->type = 'plain';
            return $rendererDefinition;
        }

        $attributeReflection = new ReflectionClass(
            $renderReflection->getParameters()[0]->getType()->getName()
        );

        if (
            !$attributeReflection->hasMethod('fromBlockAttributes')
            || !$attributeReflection->getMethod('fromBlockAttributes')->isStatic()
        ) {
            $rendererDefinition->type = 'plain';
            return $rendererDefinition;
        }

        $rendererDefinition->type = 'typehinted';
        $rendererDefinition->class = $attributeReflection->getName();

        return $rendererDefinition;
    }

    public static function fromRendererDefinition($blockDefinition): self
    {
        $rendererDefinition = new static();

        $rendererDefinition->type = $blockDefinition['type'];
        $rendererDefinition->class = $blockDefinition['class'] ?? '';

        return $rendererDefinition;
    }

    public function renderer($instance): array|callable
    {
        if ($this->type === 'typehinted') {
            return function ($blockArguments, $content) use ($instance) {
                return $instance->render(
                    call_user_func([$this->class, 'fromBlockAttributes'], $blockArguments),
                    $content
                );
            };
        }

        return [$instance, 'render'];
    }

    public function definition(): array
    {
        if ($this->type === 'typehinted') {
            return [
                'type' => 'typehinted',
                'class' => $this->class,
            ];
        }

        return [
            'type' => $this->type,
        ];
    }
}
