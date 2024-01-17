<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;
use VAF\WP\Framework\TemplateRenderer\FunctionHandler;

class Extension extends AbstractExtension
{
    public function __construct(
        private readonly FunctionHandler $functionHandler
    ) {
    }

    public function getFunctions(): array
    {
        $registeredFunctions = [];
        foreach ($this->functionHandler->getRegisteredFunctions() as $registeredFunction) {
            $registeredFunctions[] = new TwigFunction(
                $registeredFunction,
                function (...$args) use ($registeredFunction) {
                    return new Markup($this->functionHandler->call($registeredFunction, $args), 'UTF-8');
                }
            );
        }
        return $registeredFunctions;
    }
}
