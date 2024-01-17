<?php

namespace VAF\WP\Framework\TemplateRenderer;

use VAF\WP\Framework\Kernel\Kernel;

class FunctionHandler
{
    public function __construct(
        private readonly array $functionList,
        private readonly Kernel $kernel
    ) {
    }

    public function getRegisteredFunctions(): array
    {
        return array_keys($this->functionList);
    }

    public function call(string $function, array $args): mixed
    {
        $functionData = $this->functionList[$function];
        $params = [];

        for ($i = 0; $i <= array_key_last($functionData['serviceParams']); $i++) {
            if (isset($functionData['serviceParams'][$i])) {
                $params[] = $this->kernel->getContainer()->get($functionData['serviceParams'][$i]);
            } else {
                $params[] = array_shift($args);
            }
        }

        $params = array_merge($params, $args);

        $functionContainer = $this->kernel->getContainer()->get($functionData['container']);
        $methodName = $functionData['method'];
        $ret = $functionContainer->$methodName(...$params);

        if (!$functionData['isSafeHTML']) {
            $ret = htmlentities($ret);
        }

        return $ret;
    }
}
