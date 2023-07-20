<?php

namespace VAF\WP\Framework\AdminAjax;

final class Response
{
    public function __construct(
        private readonly bool $success,
        private readonly array $data = [],
        private readonly string $message = ''
    ) {
    }

    public static function success(array $data): self
    {
        return new self(true, $data);
    }

    public static function error(string $message): self
    {
        return new self(false, [], $message);
    }

    public function toArray(): array
    {
        if ($this->success) {
            return [
                'success' => $this->success,
                'data' => $this->data
            ];
        } else {
            return [
                'success' => $this->success,
                'message' => $this->message
            ];
        }
    }
}
