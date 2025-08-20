<?php

namespace VAF\WP\Framework\Wordpress;

/**
 * WordPress function wrapper for improved testability
 * 
 * This class provides a testable wrapper around WordPress global functions.
 * It allows you to mock WordPress functions in tests while transparently
 * forwarding calls to the actual WordPress functions in production.
 * 
 * Usage in production code:
 * ```php
 * use VAF\WP\Framework\Wordpress\Wordpress;
 * 
 * // Instead of: if (is_admin()) { ... }
 * if (Wordpress::is_admin()) { ... }
 * ```
 * 
 * Usage in tests:
 * ```php
 * Wordpress::fake();
 * Wordpress::mock()->shouldReceive('is_admin')->andReturn(true);
 * ```
 * 
 * Note: The @method annotations below provide IDE autocomplete support.
 * For full parameter documentation, refer to the WordPress Developer Reference.
 * 
 * @see https://developer.wordpress.org/reference/functions/
 * 
 * Core Functions - Options API
 * @method static mixed get_option(string $option, mixed $default_value = false) @see https://developer.wordpress.org/reference/functions/get_option/
 * @method static bool update_option(string $option, mixed $value, bool $autoload = null) @see https://developer.wordpress.org/reference/functions/update_option/
 * @method static bool delete_option(string $option) @see https://developer.wordpress.org/reference/functions/delete_option/
 * @method static bool add_option(string $option, mixed $value = '', string $deprecated = '', bool $autoload = 'yes') @see https://developer.wordpress.org/reference/functions/add_option/
 * 
 * Core Functions - Hooks (Actions & Filters)
 * @method static bool add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1) @see https://developer.wordpress.org/reference/functions/add_action/
 * @method static bool remove_action(string $hook_name, callable $callback, int $priority = 10) @see https://developer.wordpress.org/reference/functions/remove_action/
 * @method static void do_action(string $hook_name, mixed ...$args) @see https://developer.wordpress.org/reference/functions/do_action/
 * @method static bool add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1) @see https://developer.wordpress.org/reference/functions/add_filter/
 * @method static bool remove_filter(string $hook_name, callable $callback, int $priority = 10) @see https://developer.wordpress.org/reference/functions/remove_filter/
 * @method static mixed apply_filters(string $hook_name, mixed $value, mixed ...$args) @see https://developer.wordpress.org/reference/functions/apply_filters/
 * @method static bool has_action(string $hook_name, callable|false $callback = false) @see https://developer.wordpress.org/reference/functions/has_action/
 * @method static bool|int has_filter(string $hook_name, callable|false $callback = false) @see https://developer.wordpress.org/reference/functions/has_filter/
 * @method static bool doing_action(string|null $hook_name = null) @see https://developer.wordpress.org/reference/functions/doing_action/
 * @method static bool doing_filter(string|null $hook_name = null) @see https://developer.wordpress.org/reference/functions/doing_filter/
 * @method static int did_action(string $hook_name) @see https://developer.wordpress.org/reference/functions/did_action/
 * @method static mixed current_filter() @see https://developer.wordpress.org/reference/functions/current_filter/
 * 
 * Core Functions - Plugin Functions
 * @method static string plugin_dir_url(string $file) @see https://developer.wordpress.org/reference/functions/plugin_dir_url/
 * @method static string plugin_dir_path(string $file) @see https://developer.wordpress.org/reference/functions/plugin_dir_path/
 * @method static string plugin_basename(string $file) @see https://developer.wordpress.org/reference/functions/plugin_basename/
 * @method static bool register_activation_hook(string $file, callable $callback) @see https://developer.wordpress.org/reference/functions/register_activation_hook/
 * @method static bool register_deactivation_hook(string $file, callable $callback) @see https://developer.wordpress.org/reference/functions/register_deactivation_hook/
 * @method static bool register_uninstall_hook(string $file, callable $callback) @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 * @method static array get_plugin_data(string $plugin_file, bool $markup = true, bool $translate = true) @see https://developer.wordpress.org/reference/functions/get_plugin_data/
 * @method static bool is_plugin_active(string $plugin) @see https://developer.wordpress.org/reference/functions/is_plugin_active/
 * @method static bool is_plugin_inactive(string $plugin) @see https://developer.wordpress.org/reference/functions/is_plugin_inactive/
 * 
 * Core Functions - Admin & User
 * @method static bool is_admin() @see https://developer.wordpress.org/reference/functions/is_admin/
 * @method static bool is_network_admin() @see https://developer.wordpress.org/reference/functions/is_network_admin/
 * @method static bool is_user_admin() @see https://developer.wordpress.org/reference/functions/is_user_admin/
 * @method static \WP_User|false wp_get_current_user() @see https://developer.wordpress.org/reference/functions/wp_get_current_user/
 * @method static bool current_user_can(string $capability, mixed ...$args) @see https://developer.wordpress.org/reference/functions/current_user_can/
 * @method static bool is_user_logged_in() @see https://developer.wordpress.org/reference/functions/is_user_logged_in/
 * @method static int get_current_user_id() @see https://developer.wordpress.org/reference/functions/get_current_user_id/
 * @method static void wp_set_current_user(int $id, string $name = '') @see https://developer.wordpress.org/reference/functions/wp_set_current_user/
 * @method static string wp_login_url(string $redirect = '') @see https://developer.wordpress.org/reference/functions/wp_login_url/
 * @method static string wp_logout_url(string $redirect = '') @see https://developer.wordpress.org/reference/functions/wp_logout_url/
 * @method static string wp_registration_url() @see https://developer.wordpress.org/reference/functions/wp_registration_url/
 * @method static string wp_lostpassword_url(string $redirect = '') @see https://developer.wordpress.org/reference/functions/wp_lostpassword_url/
 * @method static void auth_redirect() @see https://developer.wordpress.org/reference/functions/auth_redirect/
 * 
 * Content Management - Posts
 * @method static \WP_Post|null get_post(int|\WP_Post|null $post = null, string $output = OBJECT, string $filter = 'raw') @see https://developer.wordpress.org/reference/functions/get_post/
 * @method static array get_posts(array $args = []) @see https://developer.wordpress.org/reference/functions/get_posts/
 * @method static int|\WP_Error wp_insert_post(array $postarr, bool $wp_error = false, bool $fire_after_hooks = true) @see https://developer.wordpress.org/reference/functions/wp_insert_post/
 * @method static int|\WP_Error wp_update_post(array $postarr = [], bool $wp_error = false, bool $fire_after_hooks = true) @see https://developer.wordpress.org/reference/functions/wp_update_post/
 * @method static \WP_Post|false|\WP_Error|null wp_delete_post(int $postid, bool $force_delete = false) @see https://developer.wordpress.org/reference/functions/wp_delete_post/
 * @method static \WP_Post|false|\WP_Error|null wp_trash_post(int $postid) @see https://developer.wordpress.org/reference/functions/wp_trash_post/
 * @method static \WP_Post|false|\WP_Error|null wp_untrash_post(int $postid) @see https://developer.wordpress.org/reference/functions/wp_untrash_post/
 * @method static int wp_count_posts(string $type = 'post', string $perm = '') @see https://developer.wordpress.org/reference/functions/wp_count_posts/
 * 
 * Content Management - Post Meta
 * @method static mixed get_post_meta(int $post_id, string $key = '', bool $single = false) @see https://developer.wordpress.org/reference/functions/get_post_meta/
 * @method static int|bool update_post_meta(int $post_id, string $meta_key, mixed $meta_value, mixed $prev_value = '') @see https://developer.wordpress.org/reference/functions/update_post_meta/
 * @method static bool delete_post_meta(int $post_id, string $meta_key, mixed $meta_value = '') @see https://developer.wordpress.org/reference/functions/delete_post_meta/
 * @method static int|bool add_post_meta(int $post_id, string $meta_key, mixed $meta_value, bool $unique = false) @see https://developer.wordpress.org/reference/functions/add_post_meta/
 * 
 * Content Management - User Meta
 * @method static mixed get_user_meta(int $user_id, string $key = '', bool $single = false) @see https://developer.wordpress.org/reference/functions/get_user_meta/
 * @method static int|bool update_user_meta(int $user_id, string $meta_key, mixed $meta_value, mixed $prev_value = '') @see https://developer.wordpress.org/reference/functions/update_user_meta/
 * @method static bool delete_user_meta(int $user_id, string $meta_key, mixed $meta_value = '') @see https://developer.wordpress.org/reference/functions/delete_user_meta/
 * @method static int|bool add_user_meta(int $user_id, string $meta_key, mixed $meta_value, bool $unique = false) @see https://developer.wordpress.org/reference/functions/add_user_meta/
 * 
 * Content Management - Terms & Taxonomies
 * @method static array|\WP_Error get_terms(array|string $args = [], array $deprecated = null) @see https://developer.wordpress.org/reference/functions/get_terms/
 * @method static \WP_Term|array|\WP_Error|null get_term(int|\WP_Term|object $term, string $taxonomy = '', string $output = OBJECT, string $filter = 'raw') @see https://developer.wordpress.org/reference/functions/get_term/
 * @method static array|\WP_Error wp_get_post_terms(int $post_id, string|array $taxonomy = 'post_tag', array $args = []) @see https://developer.wordpress.org/reference/functions/wp_get_post_terms/
 * @method static array|\WP_Error wp_set_post_terms(int $post_id, string|array $terms = '', string $taxonomy = 'post_tag', bool $append = false) @see https://developer.wordpress.org/reference/functions/wp_set_post_terms/
 * @method static array|\WP_Error wp_insert_term(string $term, string $taxonomy, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_insert_term/
 * @method static array|\WP_Error wp_update_term(int $term_id, string $taxonomy, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_update_term/
 * @method static bool|\WP_Error wp_delete_term(int $term, string $taxonomy, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_delete_term/
 * 
 * URLs & Navigation
 * @method static string admin_url(string $path = '', string $scheme = 'admin') @see https://developer.wordpress.org/reference/functions/admin_url/
 * @method static string home_url(string $path = '', string $scheme = null) @see https://developer.wordpress.org/reference/functions/home_url/
 * @method static string site_url(string $path = '', string $scheme = null) @see https://developer.wordpress.org/reference/functions/site_url/
 * @method static string|false get_permalink(int|\WP_Post $post = 0, bool $leavename = false) @see https://developer.wordpress.org/reference/functions/get_permalink/
 * @method static string get_post_permalink(int|\WP_Post $post = 0, bool $leavename = false, bool $sample = false) @see https://developer.wordpress.org/reference/functions/get_post_permalink/
 * @method static string|false get_page_link(int|\WP_Post $post = false, bool $leavename = false, bool $sample = false) @see https://developer.wordpress.org/reference/functions/get_page_link/
 * @method static string get_edit_post_link(int|\WP_Post $post = 0, string $context = 'display') @see https://developer.wordpress.org/reference/functions/get_edit_post_link/
 * @method static string get_delete_post_link(int|\WP_Post $post = 0, string $deprecated = '', bool $force_delete = false) @see https://developer.wordpress.org/reference/functions/get_delete_post_link/
 * @method static string|\WP_Error get_term_link(\WP_Term|int|string $term, string $taxonomy = '') @see https://developer.wordpress.org/reference/functions/get_term_link/
 * @method static string get_category_link(int $category) @see https://developer.wordpress.org/reference/functions/get_category_link/
 * @method static string get_tag_link(int $tag) @see https://developer.wordpress.org/reference/functions/get_tag_link/
 * @method static string network_home_url(string $path = '', string $scheme = null) @see https://developer.wordpress.org/reference/functions/network_home_url/
 * @method static string network_site_url(string $path = '', string $scheme = null) @see https://developer.wordpress.org/reference/functions/network_site_url/
 * @method static string network_admin_url(string $path = '', string $scheme = 'admin') @see https://developer.wordpress.org/reference/functions/network_admin_url/
 * 
 * Security - Nonces
 * @method static string wp_nonce_field(string $action = -1, string $name = '_wpnonce', bool $referer = true, bool $display = true) @see https://developer.wordpress.org/reference/functions/wp_nonce_field/
 * @method static int|false wp_verify_nonce(string $nonce, string $action = -1) @see https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @method static string wp_create_nonce(string $action = -1) @see https://developer.wordpress.org/reference/functions/wp_create_nonce/
 * @method static int|false check_admin_referer(string $action = -1, string $query_arg = '_wpnonce') @see https://developer.wordpress.org/reference/functions/check_admin_referer/
 * @method static int|false check_ajax_referer(string $action = -1, string|false $query_arg = false, bool $stop = true) @see https://developer.wordpress.org/reference/functions/check_ajax_referer/
 * @method static string wp_nonce_url(string $actionurl, string $action = -1, string $name = '_wpnonce') @see https://developer.wordpress.org/reference/functions/wp_nonce_url/
 * 
 * Security - Sanitization
 * @method static string sanitize_text_field(string $str) @see https://developer.wordpress.org/reference/functions/sanitize_text_field/
 * @method static string sanitize_textarea_field(string $str) @see https://developer.wordpress.org/reference/functions/sanitize_textarea_field/
 * @method static string sanitize_email(string $email) @see https://developer.wordpress.org/reference/functions/sanitize_email/
 * @method static string sanitize_url(string $url, array $protocols = null) @see https://developer.wordpress.org/reference/functions/sanitize_url/
 * @method static string sanitize_file_name(string $filename) @see https://developer.wordpress.org/reference/functions/sanitize_file_name/
 * @method static string sanitize_title(string $title, string $fallback_title = '', string $context = 'save') @see https://developer.wordpress.org/reference/functions/sanitize_title/
 * @method static string sanitize_title_with_dashes(string $title, string $raw_title = '', string $context = 'display') @see https://developer.wordpress.org/reference/functions/sanitize_title_with_dashes/
 * @method static string sanitize_key(string $key) @see https://developer.wordpress.org/reference/functions/sanitize_key/
 * @method static string sanitize_html_class(string $classname, string $fallback = '') @see https://developer.wordpress.org/reference/functions/sanitize_html_class/
 * @method static string sanitize_hex_color(string $color) @see https://developer.wordpress.org/reference/functions/sanitize_hex_color/
 * @method static string sanitize_hex_color_no_hash(string $color) @see https://developer.wordpress.org/reference/functions/sanitize_hex_color_no_hash/
 * 
 * Security - Escaping
 * @method static string esc_html(string $text) @see https://developer.wordpress.org/reference/functions/esc_html/
 * @method static string esc_attr(string $text) @see https://developer.wordpress.org/reference/functions/esc_attr/
 * @method static string esc_url(string $url, array $protocols = null, string $_context = 'display') @see https://developer.wordpress.org/reference/functions/esc_url/
 * @method static string esc_url_raw(string $url, array $protocols = null) @see https://developer.wordpress.org/reference/functions/esc_url_raw/
 * @method static string esc_js(string $text) @see https://developer.wordpress.org/reference/functions/esc_js/
 * @method static string esc_textarea(string $text) @see https://developer.wordpress.org/reference/functions/esc_textarea/
 * @method static string esc_xml(string $text) @see https://developer.wordpress.org/reference/functions/esc_xml/
 * @method static string wp_kses(string $text, array|string $allowed_html, array $allowed_protocols = []) @see https://developer.wordpress.org/reference/functions/wp_kses/
 * @method static string wp_kses_post(string $text) @see https://developer.wordpress.org/reference/functions/wp_kses_post/
 * @method static string wp_kses_data(string $text) @see https://developer.wordpress.org/reference/functions/wp_kses_data/
 * @method static array wp_kses_allowed_html(string|array $context = '') @see https://developer.wordpress.org/reference/functions/wp_kses_allowed_html/
 * 
 * Scripts & Styles
 * @method static void wp_enqueue_script(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, array|bool $args = []) @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * @method static void wp_enqueue_style(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, string $media = 'all') @see https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @method static bool wp_register_script(string $handle, string|false $src, array $deps = [], string|bool|null $ver = false, array|bool $args = []) @see https://developer.wordpress.org/reference/functions/wp_register_script/
 * @method static bool wp_register_style(string $handle, string|false $src, array $deps = [], string|bool|null $ver = false, string $media = 'all') @see https://developer.wordpress.org/reference/functions/wp_register_style/
 * @method static void wp_dequeue_script(string $handle) @see https://developer.wordpress.org/reference/functions/wp_dequeue_script/
 * @method static void wp_dequeue_style(string $handle) @see https://developer.wordpress.org/reference/functions/wp_dequeue_style/
 * @method static void wp_deregister_script(string $handle) @see https://developer.wordpress.org/reference/functions/wp_deregister_script/
 * @method static void wp_deregister_style(string $handle) @see https://developer.wordpress.org/reference/functions/wp_deregister_style/
 * @method static bool wp_script_is(string $handle, string $status = 'enqueued') @see https://developer.wordpress.org/reference/functions/wp_script_is/
 * @method static bool wp_style_is(string $handle, string $status = 'enqueued') @see https://developer.wordpress.org/reference/functions/wp_style_is/
 * @method static bool wp_localize_script(string $handle, string $object_name, array $l10n) @see https://developer.wordpress.org/reference/functions/wp_localize_script/
 * @method static bool wp_add_inline_script(string $handle, string $data, string $position = 'after') @see https://developer.wordpress.org/reference/functions/wp_add_inline_script/
 * @method static bool wp_add_inline_style(string $handle, string $data) @see https://developer.wordpress.org/reference/functions/wp_add_inline_style/
 * 
 * Transients API
 * @method static mixed get_transient(string $transient) @see https://developer.wordpress.org/reference/functions/get_transient/
 * @method static bool set_transient(string $transient, mixed $value, int $expiration = 0) @see https://developer.wordpress.org/reference/functions/set_transient/
 * @method static bool delete_transient(string $transient) @see https://developer.wordpress.org/reference/functions/delete_transient/
 * @method static mixed get_site_transient(string $transient) @see https://developer.wordpress.org/reference/functions/get_site_transient/
 * @method static bool set_site_transient(string $transient, mixed $value, int $expiration = 0) @see https://developer.wordpress.org/reference/functions/set_site_transient/
 * @method static bool delete_site_transient(string $transient) @see https://developer.wordpress.org/reference/functions/delete_site_transient/
 * 
 * Registration Functions
 * @method static \WP_Post_Type|\WP_Error register_post_type(string $post_type, array $args = []) @see https://developer.wordpress.org/reference/functions/register_post_type/
 * @method static \WP_Taxonomy|\WP_Error register_taxonomy(string $taxonomy, array|string $object_type, array $args = []) @see https://developer.wordpress.org/reference/functions/register_taxonomy/
 * @method static bool register_rest_route(string $route_namespace, string $route, array $args = [], bool $override = false) @see https://developer.wordpress.org/reference/functions/register_rest_route/
 * @method static \WP_Block_Type|false register_block_type(string|\WP_Block_Type $block_type, array $args = []) @see https://developer.wordpress.org/reference/functions/register_block_type/
 * @method static void register_nav_menus(array $locations = []) @see https://developer.wordpress.org/reference/functions/register_nav_menus/
 * @method static void register_nav_menu(string $location, string $description) @see https://developer.wordpress.org/reference/functions/register_nav_menu/
 * @method static int register_sidebar(array $args = []) @see https://developer.wordpress.org/reference/functions/register_sidebar/
 * @method static void register_widget(string|\WP_Widget $widget) @see https://developer.wordpress.org/reference/functions/register_widget/
 * @method static void add_shortcode(string $tag, callable $callback) @see https://developer.wordpress.org/reference/functions/add_shortcode/
 * @method static string|int|false add_meta_box(string $id, string $title, callable $callback, string|array|\WP_Screen $screen = null, string $context = 'advanced', string $priority = 'default', array $callback_args = null) @see https://developer.wordpress.org/reference/functions/add_meta_box/
 * 
 * Localization & Internationalization
 * @method static string __(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/__/
 * @method static void _e(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/_e/
 * @method static string _n(string $single, string $plural, int $number, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/_n/
 * @method static string _x(string $text, string $context, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/_x/
 * @method static void _ex(string $text, string $context, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/_ex/
 * @method static string _nx(string $single, string $plural, int $number, string $context, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/_nx/
 * @method static string esc_html__(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/esc_html__/
 * @method static void esc_html_e(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/esc_html_e/
 * @method static string esc_attr__(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/esc_attr__/
 * @method static void esc_attr_e(string $text, string $domain = 'default') @see https://developer.wordpress.org/reference/functions/esc_attr_e/
 * @method static bool load_plugin_textdomain(string $domain, string|false $deprecated = false, string|false $plugin_rel_path = false) @see https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
 * @method static bool load_theme_textdomain(string $domain, string|false $path = false) @see https://developer.wordpress.org/reference/functions/load_theme_textdomain/
 * 
 * Database & Cache
 * @method static mixed wp_cache_get(int|string $key, string $group = '', bool $force = false, bool &$found = null) @see https://developer.wordpress.org/reference/functions/wp_cache_get/
 * @method static bool wp_cache_set(int|string $key, mixed $data, string $group = '', int $expire = 0) @see https://developer.wordpress.org/reference/functions/wp_cache_set/
 * @method static bool wp_cache_delete(int|string $key, string $group = '') @see https://developer.wordpress.org/reference/functions/wp_cache_delete/
 * @method static bool wp_cache_add(int|string $key, mixed $data, string $group = '', int $expire = 0) @see https://developer.wordpress.org/reference/functions/wp_cache_add/
 * @method static bool wp_cache_replace(int|string $key, mixed $data, string $group = '', int $expire = 0) @see https://developer.wordpress.org/reference/functions/wp_cache_replace/
 * @method static bool wp_cache_flush() @see https://developer.wordpress.org/reference/functions/wp_cache_flush/
 * @method static int wp_cache_incr(int|string $key, int $offset = 1, string $group = '') @see https://developer.wordpress.org/reference/functions/wp_cache_incr/
 * @method static int wp_cache_decr(int|string $key, int $offset = 1, string $group = '') @see https://developer.wordpress.org/reference/functions/wp_cache_decr/
 * 
 * HTTP API
 * @method static array|\WP_Error wp_remote_get(string $url, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_remote_get/
 * @method static array|\WP_Error wp_remote_post(string $url, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_remote_post/
 * @method static array|\WP_Error wp_remote_request(string $url, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_remote_request/
 * @method static array|\WP_Error wp_remote_head(string $url, array $args = []) @see https://developer.wordpress.org/reference/functions/wp_remote_head/
 * @method static string wp_remote_retrieve_body(array|\WP_Error $response) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_body/
 * @method static array|string wp_remote_retrieve_headers(array|\WP_Error $response) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_headers/
 * @method static array|string wp_remote_retrieve_header(array|\WP_Error $response, string $header) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_header/
 * @method static int|string wp_remote_retrieve_response_code(array|\WP_Error $response) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_response_code/
 * @method static string wp_remote_retrieve_response_message(array|\WP_Error $response) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_response_message/
 * @method static array wp_remote_retrieve_cookies(array|\WP_Error $response) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_cookies/
 * @method static \WP_Http_Cookie|string wp_remote_retrieve_cookie(array|\WP_Error $response, string $name) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_cookie/
 * @method static string wp_remote_retrieve_cookie_value(array|\WP_Error $response, string $name) @see https://developer.wordpress.org/reference/functions/wp_remote_retrieve_cookie_value/
 * 
 * Conditional Tags
 * @method static bool is_home() @see https://developer.wordpress.org/reference/functions/is_home/
 * @method static bool is_front_page() @see https://developer.wordpress.org/reference/functions/is_front_page/
 * @method static bool is_page(int|string|int[]|string[] $page = '') @see https://developer.wordpress.org/reference/functions/is_page/
 * @method static bool is_single(int|string|int[]|string[] $post = '') @see https://developer.wordpress.org/reference/functions/is_single/
 * @method static bool is_singular(string|string[] $post_types = '') @see https://developer.wordpress.org/reference/functions/is_singular/
 * @method static bool is_sticky(int $post_id = 0) @see https://developer.wordpress.org/reference/functions/is_sticky/
 * @method static bool is_archive() @see https://developer.wordpress.org/reference/functions/is_archive/
 * @method static bool is_category(int|string|int[]|string[] $category = '') @see https://developer.wordpress.org/reference/functions/is_category/
 * @method static bool is_tag(int|string|int[]|string[] $tag = '') @see https://developer.wordpress.org/reference/functions/is_tag/
 * @method static bool is_tax(string|string[] $taxonomy = '', int|string|int[]|string[] $term = '') @see https://developer.wordpress.org/reference/functions/is_tax/
 * @method static bool is_author(int|string|int[]|string[] $author = '') @see https://developer.wordpress.org/reference/functions/is_author/
 * @method static bool is_date() @see https://developer.wordpress.org/reference/functions/is_date/
 * @method static bool is_year() @see https://developer.wordpress.org/reference/functions/is_year/
 * @method static bool is_month() @see https://developer.wordpress.org/reference/functions/is_month/
 * @method static bool is_day() @see https://developer.wordpress.org/reference/functions/is_day/
 * @method static bool is_time() @see https://developer.wordpress.org/reference/functions/is_time/
 * @method static bool is_search() @see https://developer.wordpress.org/reference/functions/is_search/
 * @method static bool is_404() @see https://developer.wordpress.org/reference/functions/is_404/
 * @method static bool is_attachment(int|string|int[]|string[] $attachment = '') @see https://developer.wordpress.org/reference/functions/is_attachment/
 * @method static bool is_feed(string|string[] $feeds = '') @see https://developer.wordpress.org/reference/functions/is_feed/
 * @method static bool is_trackback() @see https://developer.wordpress.org/reference/functions/is_trackback/
 * @method static bool is_preview() @see https://developer.wordpress.org/reference/functions/is_preview/
 * @method static bool is_robots() @see https://developer.wordpress.org/reference/functions/is_robots/
 * @method static bool is_favicon() @see https://developer.wordpress.org/reference/functions/is_favicon/
 * @method static bool is_post_type_archive(string|string[] $post_types = '') @see https://developer.wordpress.org/reference/functions/is_post_type_archive/
 * @method static bool is_ssl() @see https://developer.wordpress.org/reference/functions/is_ssl/
 * @method static bool is_main_query() @see https://developer.wordpress.org/reference/functions/is_main_query/
 * @method static bool is_multisite() @see https://developer.wordpress.org/reference/functions/is_multisite/
 * @method static bool is_main_site(int $network_id = null) @see https://developer.wordpress.org/reference/functions/is_main_site/
 * @method static bool is_super_admin(int|false $user_id = false) @see https://developer.wordpress.org/reference/functions/is_super_admin/
 * 
 * Utility Functions
 * @method static int absint(mixed $maybeint) @see https://developer.wordpress.org/reference/functions/absint/
 * @method static array wp_parse_args(string|array|object $args, array $defaults = []) @see https://developer.wordpress.org/reference/functions/wp_parse_args/
 * @method static array wp_list_pluck(array $input_list, int|string $field, int|string $index_key = null) @see https://developer.wordpress.org/reference/functions/wp_list_pluck/
 * @method static void wp_die(string|\WP_Error $message = '', string|int $title = '', string|array|int $args = []) @see https://developer.wordpress.org/reference/functions/wp_die/
 * @method static void wp_redirect(string $location, int $status = 302, string|false $x_redirect_by = 'WordPress') @see https://developer.wordpress.org/reference/functions/wp_redirect/
 * @method static void wp_safe_redirect(string $location, int $status = 302, string|false $x_redirect_by = 'WordPress') @see https://developer.wordpress.org/reference/functions/wp_safe_redirect/
 * @method static bool wp_mail(string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = []) @see https://developer.wordpress.org/reference/functions/wp_mail/
 * @method static string wp_generate_password(int $length = 12, bool $special_chars = true, bool $extra_special_chars = false) @see https://developer.wordpress.org/reference/functions/wp_generate_password/
 * @method static string wp_rand(int $min = null, int $max = null) @see https://developer.wordpress.org/reference/functions/wp_rand/
 * @method static string wp_hash(string $data, string $scheme = 'auth') @see https://developer.wordpress.org/reference/functions/wp_hash/
 * @method static string wp_hash_password(string $password) @see https://developer.wordpress.org/reference/functions/wp_hash_password/
 * @method static bool wp_check_password(string $password, string $hash, string|int $user_id = '') @see https://developer.wordpress.org/reference/functions/wp_check_password/
 * @method static string wp_generate_uuid4() @see https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
 * @method static bool wp_is_uuid(mixed $uuid, int $version = null) @see https://developer.wordpress.org/reference/functions/wp_is_uuid/
 * @method static mixed wp_unslash(string|array $value) @see https://developer.wordpress.org/reference/functions/wp_unslash/
 * @method static string|array wp_slash(string|array $value) @see https://developer.wordpress.org/reference/functions/wp_slash/
 * @method static string wp_strip_all_tags(string $text, bool $remove_breaks = false) @see https://developer.wordpress.org/reference/functions/wp_strip_all_tags/
 * @method static string wp_trim_words(string $text, int $num_words = 55, string $more = null) @see https://developer.wordpress.org/reference/functions/wp_trim_words/
 * @method static string wp_trim_excerpt(string $text = '', \WP_Post|object|int $post = null) @see https://developer.wordpress.org/reference/functions/wp_trim_excerpt/
 * 
 * Miscellaneous Functions
 * @method static string get_bloginfo(string $show = '', string $filter = 'raw') @see https://developer.wordpress.org/reference/functions/get_bloginfo/
 * @method static int get_the_ID() @see https://developer.wordpress.org/reference/functions/get_the_ID/
 * @method static array wp_upload_dir(string $time = null, bool $create_dir = true, bool $refresh_cache = false) @see https://developer.wordpress.org/reference/functions/wp_upload_dir/
 * @method static bool wp_mkdir_p(string $target) @see https://developer.wordpress.org/reference/functions/wp_mkdir_p/
 * @method static array wp_handle_upload(array $file, array $overrides = [], string $time = null) @see https://developer.wordpress.org/reference/functions/wp_handle_upload/
 * @method static array wp_check_filetype(string $filename, array $mimes = null) @see https://developer.wordpress.org/reference/functions/wp_check_filetype/
 * @method static array wp_check_filetype_and_ext(string $file, string $filename, array $mimes = null) @see https://developer.wordpress.org/reference/functions/wp_check_filetype_and_ext/
 * @method static array get_allowed_mime_types(int|\WP_User $user = null) @see https://developer.wordpress.org/reference/functions/get_allowed_mime_types/
 * @method static string wp_get_upload_dir() @see https://developer.wordpress.org/reference/functions/wp_get_upload_dir/
 * @method static string wp_upload_bits(string $name, null|string $deprecated, string $bits, string $time = null) @see https://developer.wordpress.org/reference/functions/wp_upload_bits/
 * @method static bool|int wp_delete_file(string $file) @see https://developer.wordpress.org/reference/functions/wp_delete_file/
 * @method static string wp_unique_filename(string $dir, string $filename, callable $unique_filename_callback = null) @see https://developer.wordpress.org/reference/functions/wp_unique_filename/
 * @method static void wp_head() @see https://developer.wordpress.org/reference/functions/wp_head/
 * @method static void wp_footer() @see https://developer.wordpress.org/reference/functions/wp_footer/
 * @method static string body_class(string|array $class = '') @see https://developer.wordpress.org/reference/functions/body_class/
 * @method static string post_class(string|array $class = '', int|\WP_Post $post = null) @see https://developer.wordpress.org/reference/functions/post_class/
 * @method static array get_body_class(string|array $class = '') @see https://developer.wordpress.org/reference/functions/get_body_class/
 * @method static array get_post_class(string|array $class = '', int|\WP_Post $post = null) @see https://developer.wordpress.org/reference/functions/get_post_class/
 * @method static bool is_rtl() @see https://developer.wordpress.org/reference/functions/is_rtl/
 * @method static bool is_child_theme() @see https://developer.wordpress.org/reference/functions/is_child_theme/
 * @method static string get_stylesheet() @see https://developer.wordpress.org/reference/functions/get_stylesheet/
 * @method static string get_stylesheet_uri() @see https://developer.wordpress.org/reference/functions/get_stylesheet_uri/
 * @method static string get_stylesheet_directory() @see https://developer.wordpress.org/reference/functions/get_stylesheet_directory/
 * @method static string get_stylesheet_directory_uri() @see https://developer.wordpress.org/reference/functions/get_stylesheet_directory_uri/
 * @method static string get_template() @see https://developer.wordpress.org/reference/functions/get_template/
 * @method static string get_template_directory() @see https://developer.wordpress.org/reference/functions/get_template_directory/
 * @method static string get_template_directory_uri() @see https://developer.wordpress.org/reference/functions/get_template_directory_uri/
 * @method static string get_theme_root() @see https://developer.wordpress.org/reference/functions/get_theme_root/
 * @method static string get_theme_root_uri() @see https://developer.wordpress.org/reference/functions/get_theme_root_uri/
 * @method static void|bool add_theme_support(string $feature, mixed ...$args) @see https://developer.wordpress.org/reference/functions/add_theme_support/
 * @method static bool|void remove_theme_support(string $feature) @see https://developer.wordpress.org/reference/functions/remove_theme_support/
 * @method static bool|array get_theme_support(string $feature, mixed ...$args) @see https://developer.wordpress.org/reference/functions/get_theme_support/
 * @method static mixed get_theme_mod(string $name, mixed $default = false) @see https://developer.wordpress.org/reference/functions/get_theme_mod/
 * @method static bool set_theme_mod(string $name, mixed $value) @see https://developer.wordpress.org/reference/functions/set_theme_mod/
 * @method static void remove_theme_mod(string $name) @see https://developer.wordpress.org/reference/functions/remove_theme_mod/
 * @method static array|null get_theme_mods() @see https://developer.wordpress.org/reference/functions/get_theme_mods/
 * @method static string get_avatar(mixed $id_or_email, int $size = 96, string $default = '', string $alt = '', array $args = null) @see https://developer.wordpress.org/reference/functions/get_avatar/
 * @method static string|false get_avatar_url(mixed $id_or_email, array $args = null) @see https://developer.wordpress.org/reference/functions/get_avatar_url/
 */
class Wordpress
{
    private static ?object $mock = null;

    /**
     * Set up a mock object for testing WordPress functions
     * 
     * This method enables mocking of WordPress functions for testing purposes.
     * When a mock is set, all calls to WordPress functions through this wrapper
     * will be directed to the mock object instead of the actual WordPress functions.
     * 
     * @param object|null $mock The mock object to use, or null to create a default Mockery spy
     * @return void
     * 
     * @example
     * ```php
     * // Create a default spy mock
     * Wordpress::fake();
     * 
     * // Or provide a custom mock
     * $mock = \Mockery::mock();
     * Wordpress::fake($mock);
     * ```
     */
    public static function fake(?object $mock = null): void
    {
        if ($mock === null) {
            $mock = \Mockery::spy();
        }
        self::$mock = $mock;
    }

    /**
     * Reset the mock object and return to normal WordPress function calls
     * 
     * This method clears any mock object that was set with fake(),
     * returning the wrapper to its normal behavior of calling actual
     * WordPress functions.
     * 
     * @return void
     * 
     * @example
     * ```php
     * Wordpress::fake();
     * // ... run tests ...
     * Wordpress::resetFake(); // Clear the mock
     * ```
     */
    public static function resetFake(): void
    {
        self::$mock = null;
    }

    /**
     * Get the current mock object
     * 
     * Returns the mock object that was set with fake(), allowing you to
     * configure expectations and assertions on it.
     * 
     * @return object|null The current mock object, or null if no mock is set
     * 
     * @example
     * ```php
     * Wordpress::fake();
     * Wordpress::mock()->shouldReceive('is_admin')->andReturn(true);
     * ```
     */
    public static function mock(): ?object
    {
        return self::$mock;
    }

    /**
     * Magic method to handle static calls to WordPress functions
     * 
     * This method intercepts all static method calls and either:
     * - Forwards them to the mock object if one is set (for testing)
     * - Forwards them to the actual WordPress function (in production)
     * 
     * @param string $name The name of the WordPress function being called
     * @param array $arguments The arguments passed to the function
     * @return mixed The return value from the WordPress function or mock
     * @throws \BadMethodCallException If the WordPress function doesn't exist
     * 
     * @internal This method is called automatically by PHP when accessing undefined static methods
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (self::$mock !== null) {
            return self::$mock->$name(...$arguments);
        }

        // When no mock is set, forward to global WordPress functions
        if (function_exists($name)) {
            return call_user_func_array($name, $arguments);
        }

        throw new \BadMethodCallException("WordPress function '$name' does not exist");
    }
}
