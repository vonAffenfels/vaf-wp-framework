<?php

namespace VAF\WP\Framework\RestAPI;

class SuppressOutput
{

    private bool $enabled;

    public static function enabled(bool $enabled): self
    {
    	$suppressOutput = new static();

        $suppressOutput->enabled = $enabled;

    	return $suppressOutput;
    }

    public function start(): void
    {
        if(!$this->enabled) {
            return;
        }

        ob_start();
    }

    public function finish(): void
    {
        if(!$this->enabled) {
            return;
        }

        ob_end_clean();
    }
}
