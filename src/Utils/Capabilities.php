<?php

namespace VAF\WP\Framework\Utils;

enum Capabilities: string
{
    # None
    case NONE = '';

    # Super Admin
    case CREATE_SITES = 'create_sites';
    case DELETE_SITES = 'delete_sites';
    case MANAGE_NETWORK = 'manage_network';
    case MANAGE_SITES = 'manage_sites';
    case MANAGE_NETWORK_USERS = 'manage_network_users';
    case MANAGE_NETWORK_PLUGINS = 'manage_network_plugins';
    case MANAGE_NETWORK_THEMES = 'manage_network_themes';
    case MANAGE_NETWORK_OPTIONS = 'manage_network_options';
    case UPGRADE_NETWORK = 'upgrade_network';
    case SETUP_NETWORK = 'setup_network';

    # Admin
    case ACTIVATE_PLUGINS = 'activate_plugins';
    case DELETE_OTHERS_PAGES = 'delete_others_pages';
    case DELETE_OTHERS_POSTS = 'delete_others_posts';
    case DELETE_PAGES = 'delete_pages';
    case DELETE_POSTS = 'delete_posts';
    case DELETE_PRIVATE_PAGES = 'delete_private_pages';
    case DELETE_PRIVATE_POSTS = 'delete_private_posts';
    case DELETE_PUBLISHED_PAGES = 'delete_published_pages';
    case DELETE_PUBLISHED_POSTS = 'delete_published_posts';
    case EDIT_DASHBOARD = 'edit_dashboard';
    case EDIT_OTHERS_PAGES = 'edit_others_pages';
    case EDIT_OTHERS_POSTS = 'edit_others_posts';
    case EDIT_PAGES = 'edit_pages';
    case EDIT_POSTS = 'edit_posts';
    case EDIT_PRIVATE_PAGES = 'edit_private_pages';
    case EDIT_PRIVATE_POSTS = 'edit_private_posts';
    case EDIT_PUBLISHED_PAGES = 'edit_published_pages';
    case EDIT_PUBLISHED_POSTS = 'edit_published_posts';
    case EDIT_THEME_OPTIONS = 'edit_theme_options';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case LIST_USERS = 'list_users';
    case MANAGE_CATEGORIES = 'manage_categories';
    case MANAGE_LINKS = 'manage_links';
    case MANAGE_OPTIONS = 'manage_options';
    case MODERATE_COMMENTS = 'moderate_comments';
    case PROMOTE_USERS = 'promote_users';
    case PUBLISH_PAGES = 'publish_pages';
    case PUBLISH_POSTS = 'publish_posts';
    case READ_PRIVATE_PAGES = 'read_private_pages';
    case READ_PRIVATE_POSTS = 'read_private_posts';
    case READ = 'read';
    case REMOVE_USERS = 'remove_users';
    case SWITCH_THEMES = 'switch_themes';
    case UPLOAD_FILES = 'upload_files';
    case CUSTOMIZE = 'customize';
    case DELETE_SITE = 'delete_site';

    # Additional Admin
    case UPDATE_CORE = 'update_core';
    case UPDATE_PLUGINS = 'update_plugins';
    case UPDATE_THEMES = 'update_themes';
    case INSTALL_PLUGINS = 'install_plugins';
    case INSTALL_THEMES = 'install_themes';
    case DELETE_THEMES = 'delete_themes';
    case DELETE_PLUGINS = 'delete_plugins';
    case EDIT_PLUGINS = 'edit_plugins';
    case EDIT_THEMES = 'edit_themes';
    case EDIT_FILES = 'edit_files';
    case EDIT_USERS = 'edit_users';
    case ADD_USERS = 'add_users';
    case CREATE_USERS = 'create_users';
    case DELETE_USERS = 'delete_users';
    case UNFILTERED_HTML = 'unfiltered_html';
}
