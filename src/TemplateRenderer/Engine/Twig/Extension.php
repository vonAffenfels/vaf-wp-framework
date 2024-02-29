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
                    $ret = $this->functionHandler->call($registeredFunction, $args);
                    if (is_string($ret)) {
                        return new Markup($ret, 'UTF-8');
                    } else {
                        return $ret;
                    }
                }
            );
        }
        return $registeredFunctions;
    }
}
