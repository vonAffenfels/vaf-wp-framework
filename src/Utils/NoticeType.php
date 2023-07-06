<?php

namespace VAF\WP\Framework\Utils;

enum NoticeType: string
{
    case ERROR = 'notice-error';
    case WARNING = 'notice-warning';
    case SUCCESS = 'notice-success';
    case INFO = 'notice-info';
}
