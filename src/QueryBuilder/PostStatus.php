<?php

namespace VAF\WP\Framework\QueryBuilder;

enum PostStatus: string
{
    case PUBLISH = 'publish';
    case PENDING = 'pending';
    case DRAFT = 'draft';
    case AUTO_DRAFT = 'auto-draft';
    case FUTURE = 'future';
    case PRIVATE = 'private';
    case INHERIT = 'inherit';
    case TRASH = 'trash';
    case ANY = 'any';
}
