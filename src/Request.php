<?php

namespace VAF\WP\Framework;

final class Request
{
    /**
     * List of parameter types to access
     */
    public const TYPE_ALL = 0;
    public const TYPE_GET = 1;
    public const TYPE_POST = 2;
    public const TYPE_SERVER = 3;

    /**
     * @var array All available GET parameters
     */
    private readonly array $get;

    /**
     * @var array All available POST parameters
     */
    private readonly array $post;

    /**
     * @var array All available server parameters
     */
    private readonly array $server;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    /**
     * Returns all parameters for a specific type
     *
     * @param int $type
     * @return array
     */
    final public function getParams(int $type = self::TYPE_ALL): array
    {
        return match ($type) {
            self::TYPE_ALL => array_merge($this->get, $this->post, $this->server),
            self::TYPE_GET => $this->get,
            self::TYPE_POST => $this->post,
            self::TYPE_SERVER => $this->server,
            default => [],
        };
    }

    /**
     * Returns the requested parameter
     *
     * @param string $key
     * @param int $type
     * @param $default
     * @return mixed|null
     */
    final public function getParam(string $key, int $type = self::TYPE_ALL, $default = null): mixed
    {
        $params = $this->getParams($type);

        return $params[$key] ?? $default;
    }

    /**
     * Returns true if request is an ajax request.
     *
     * @return bool
     */
    final public function isAjaxRequest(): bool
    {
        return strtolower($this->getParam('HTTP_X_REQUESTED_WITH', self::TYPE_SERVER, '')) == 'xmlhttprequest';
    }

    /**
     * Returns true if request is a POST request.
     *
     * @return bool
     */
    final public function isPost(): bool
    {
        return $this->getParam('REQUEST_METHOD', self::TYPE_SERVER, '') == 'POST';
    }

    /**
     * Returns true if request is a GET request.
     *
     * @return bool
     */
    final public function isGet(): bool
    {
        return $this->getParam('REQUEST_METHOD', self::TYPE_SERVER, '') == 'GET';
    }

    /**
     * Returns true if request is a HTTPS request.
     *
     * @return bool
     */
    final public function isSsl(): bool
    {
        return $this->getParam('HTTPS', self::TYPE_SERVER, '') == 'on';
    }

    /**
     * Returns true if the browser that has sent the request supports WebP
     *
     * @return bool
     */
    final public function supportsWebP(): bool
    {
        return str_contains($this->getParam('HTTP_ACCEPT', self::TYPE_SERVER, ''), 'image/webp');
    }
}
