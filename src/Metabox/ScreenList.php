<?php

namespace VAF\WP\Framework\Metabox;

class ScreenList
{
    private string|array|null $screen;
    private ?string $feature;
    private array $supportedScreens = [];

    public static function fromScreen(string|array|null $screen): self
    {
        $screenList = new static();

        $screenList->screen = $screen;

        return $screenList;
    }

    public function withSupporting(string|null $feature, callable $screensFromFeature): self
    {
        $screenList = clone $this;

        $screenList->feature = $feature;
        $screenList->supportedScreens = $feature === null ? [] : $screensFromFeature($feature);

        return $screenList;
    }

    public function screens(): string|array|null
    {
        if ($this->feature === null) {
            return $this->screen;
        }

        if ($this->screen === null && empty($this->supportedScreens)) {
            throw new EmptySupportingScreensException();
        }

        if ($this->screen === null) {
            return $this->supportedScreens;
        }

        return [$this->screen, ...$this->supportedScreens];
    }
}
