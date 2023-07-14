<?php

namespace VAF\WP\Framework\Utils;

enum HttpResponseCodes: int
{
    # 4xx
    case HTTP_BAD_REQUEST = 400;
    case HTTP_FORBIDDEN = 403;

    # 5xx
    case HTTP_INTERNAL_SERVER_ERROR = 500;
}
