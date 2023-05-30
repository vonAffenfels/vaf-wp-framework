<?php

namespace VAF\WP\Library\Shortcode;

use VAF\WP\Library\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $shortcodeContainer)
    {
    }

    public function registerShortcodes(): void
    {
        foreach ($this->shortcodeContainer as $serviceId => $shortcodeContainer) {
            foreach ($shortcodeContainer as $shortcode => $data) {
                add_shortcode(
                    $shortcode,
                    function ($attributes, ?string $content, string $tag) use ($serviceId, $data): string {
                        if (!is_array($attributes)) {
                            $attributes = [];
                        }

                        $attributes = array_change_key_case($attributes);

                        $attributes = shortcode_atts($data['params'], $attributes, $tag);

                        $passedParameters = [];
                        foreach ($attributes as $key => $value) {
                            # Handle type
                            switch ($data['paramTypes'][$key]) {
                                case 'int':
                                    $value = (int)$value;
                                    break;

                                case 'bool':
                                    $value = in_array(strtolower($value), ['1', 'on', 'true']);
                                    break;

                                case 'string':
                                default:
                                    # Nothing to do as $value is already a string
                                    break;
                            }
                            $passedParameters[$data['paramsLower'][$key]] = $value;
                        }

                        foreach ($data['serviceParams'] as $param => $service) {
                            $passedParameters[$param] = $this->kernel->getContainer()->get($service);
                        }

                        $methodName = $data['method'];

                        $shortcodeContainer = $this->kernel->getContainer()->get($serviceId);
                        return $shortcodeContainer->$methodName(...$passedParameters);
                    }
                );
            }
        }
    }
}
