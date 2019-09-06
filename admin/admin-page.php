<?php

namespace ExtendedCore\Admin;

use function ExtendedCore\get_request_var;
use function ExtendedCore\isset_not_empty;

/**
 * Abstract Admin Page
 *
 * This is a base class for all admin pages
 *
 * @package     Admin
 * @subpackage  Admin
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

abstract class Admin_Page
{

    protected $screen_id;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register'], $this->get_priority());

        if (wp_doing_ajax()) {
            $this->add_ajax_actions();
        }

        if ($this->is_current_page()) {
            add_action('admin_enqueue_scripts', [$this, 'scripts']);
            add_filter('admin_title', [$this, 'admin_title'], 10, 2);
            add_action('admin_init', [$this, 'process_action']);
            add_action('admin_notices', [$this, 'admin_notices']);
            $this->add_additional_actions();
        }
    }



    /**
     * Modify the tab title...
     *
     * @param $admin_title string
     * @param $title string
     * @return mixed string
     */
    public function admin_title($admin_title, $title)
    {
        return $admin_title;
    }

    /**
     * Get the parent slug
     *
     * @return string
     */
    abstract protected function get_parent_slug();

    /**
     * Add Ajax actions...
     *
     * @return mixed
     */
    abstract protected function add_ajax_actions();

    /**
     * Adds additional actions.
     *
     * @return mixed
     */
    abstract protected function add_additional_actions();

    /**
     * Get the menu order between 1 - 99
     *
     * @return int
     */
    public function get_priority()
    {
        return 10;
    }

    /**
     * Get the page slug
     *
     * @return string
     */
    abstract public function get_slug();

    /**
     * Get the menu name
     *
     * @return string
     */
    abstract public function get_name();

    /**
     * The required minimum capability required to load the page
     *
     * @return string
     */
    abstract public function get_cap();

    /**
     * Get the item type for this page
     *
     * @return mixed
     */
    abstract public function get_item_type();

    /**
     * Adds an S
     *
     * @return string
     */
    public function get_item_type_plural()
    {
        return $this->get_item_type() . 's';
    }

    /**
     * Whether this page is the current page
     *
     * @return bool
     */
    public function is_current_page()
    {
        // Return basic check to see if we are on the current page doing a normal request
        if (!wp_doing_ajax()) {
            return get_request_var('page') === $this->get_slug();
        }

        return false;
    }

    /**
     * Enqueue any scripts
     */
    abstract public function scripts();

    /**
     * Register the page
     */
    public function register()
    {
        $page = add_submenu_page(
            $this->get_parent_slug(),
            $this->get_name(),
            $this->get_name(),
            $this->get_cap(),
            $this->get_slug(),
            [$this, 'page']
        );

        $this->screen_id = $page;

        add_action("load-" . $page, [$this, 'load_page']);
    }

    /**
     * Add any help items
     *
     * @return mixed
     */
    abstract public function load_page();

    /**
     * Get the affected items on this page
     *
     * @return array|bool
     */
    protected function get_items()
    {
        $items = get_request_var($this->get_item_type(), null);

        if (!$items)
            return false;

        return is_array($items) ? $items : array($items);
    }

    /**
     * Get the current action
     *
     * @return bool|string
     */
    protected function get_current_action()
    {
        if (isset_not_empty($_REQUEST, 'filter_action'))
            return false;

        if (isset_not_empty($_REQUEST, 'action'))
            return sanitize_text_field(get_request_var('action'));

        if (isset_not_empty($_REQUEST, 'action2'))
            return sanitize_text_field(get_request_var('action2'));

        return 'view';
    }

    /**
     * Get the screen title
     */
    protected function get_title()
    {
        return $this->get_name();
    }

    /**
     * @return mixed
     */
    public function get_screen_id()
    {
        return $this->screen_id;
    }

    /**
     * Verify that the current user can perform the action
     *
     * @return bool
     */
    protected function verify_action()
    {
        if (!get_request_var('_wpnonce') || !current_user_can($this->get_cap()))
            return false;

        $nonce = get_request_var('_wpnonce');

        $checks = [
            wp_verify_nonce($nonce),
            wp_verify_nonce($nonce, $this->get_current_action()),
            wp_verify_nonce($nonce, sprintf('bulk-%s', $this->get_item_type_plural()))
        ];

        return in_array(true, $checks);
    }

    /**
     * Die if no access
     */
    protected function wp_die_no_access()
    {
        if (wp_doing_ajax()) {
            return wp_send_json_error(__("Invalid permissions.", 'extended-core'));
        }

        return wp_die(__("Invalid permissions.", 'extended-core'), 'No Access!');
    }

    /**
     * Output a search form
     *
     * @param $title
     * @param string $name
     */
    protected function search_form($title, $name = 's')
    {
        ?>
        <form method="get" class="search-form">
            <?php html()->hidden_GET_inputs(true); ?>
            <input type="hidden" name="page" value="<?php esc_attr_e(get_request_var('page')); ?>">
            <p class="search-box">
                <label class="screen-reader-text" for="post-search-input"><?php echo $title; ?>:</label>
                <input type="search" id="post-search-input" name="<?php echo $name ?>"
                       value="<?php esc_attr_e(get_request_var($name)); ?>">
                <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e($title); ?>">
            </p>
        </form>
        <?php
    }

    /**
     * Process the given action
     */
    public function process_action()
    {

        if (!$this->get_current_action() || !$this->verify_action())
            return;

        $base_url = remove_query_arg(['_wpnonce', 'action', 'process_queue'], wp_get_referer());

        $func = sprintf("process_%s", $this->get_current_action());

        $exitCode = null;

        if (method_exists($this, $func)) {
            $exitCode = call_user_func([$this, $func]);
        }

        if (is_wp_error($exitCode)) {
            $this->add_notice($exitCode);
            return;
        }

        if (is_string($exitCode) && esc_url_raw($exitCode)) {
            wp_redirect($exitCode);
            die();
        }

        // Return to self if true response.
        if ($exitCode === true) {
            return;
        }

        // IF NULL return to main table
        if (!empty($this->get_items())) {
            $base_url = add_query_arg('ids', urlencode(implode(',', $this->get_items())), $base_url);
        }

        wp_redirect($base_url);
        die();
    }

    /**
     * Get an array of links => titles for page title actions
     *
     * @return array[]
     */
    protected function get_title_actions()
    {
        return [
            [
                'link' => $this->admin_url(['action' => 'add']),
                'action' => __('Add New', 'extended-core'),
                'target' => '_self',
            ]
        ];
    }

    /**
     * Output the title actions
     */
    protected function do_title_actions()
    {
        foreach ($this->get_title_actions() as $action):

            $action = wp_parse_args($action, [
                'link' => admin_url(),
                'action' => __('Add New', 'extended-core'),
                'target' => '_self',
            ])

            ?>
            <a class="page-title-action aria-button-if-js" target="<?php esc_attr_e($action['target']); ?>"
               href="<?php esc_attr_e($action['link']); ?>"><?php _e($action['action']); ?></a>
        <?php
        endforeach;

    }

    /**
     * Output the basic view.
     *
     * @return mixed
     */
    abstract public function view();


    /**
     * Display the title and dependent action include the appropriate page content
     */
    public function page()
    {

        do_action("admin/{$this->get_slug()}/before");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $this->get_title(); ?></h1>
            <?php $this->do_title_actions(); ?>
            <hr class="wp-header-end">
            <?php

            if (method_exists($this, $this->get_current_action())) {
                call_user_func([$this, $this->get_current_action()]);
            } else if (has_action("admin/{$this->get_slug()}/display/{$this->get_current_action()}")) {
                do_action("admin/{$this->get_slug()}/display/{$this->get_current_action()}", $this);
            } else {
                call_user_func([$this, 'view']);
            }

            ?>
        </div>
        <?php

        do_action("admin/{$this->get_slug()}/after");
    }

    /**
     * Get the admin url with the given query string.
     *
     * @param string|array $query
     * @return string
     */
    public function admin_url($query = [])
    {
        $base = add_query_arg(['page' => $this->get_slug()], admin_url('admin.php'));

        if (empty($query)) {
            return $base;
        }

        $url = $base;

        if (is_array($query)) {
            $url = add_query_arg($query, $base);
        }

        if (is_string($query)) {
            $url = $base . '&' . $query;
        }

        return $url;
    }

    /**
     * Default process view
     */
    public function process_view()
    {
        $paged = get_request_var('paged', 1);
        return add_query_arg('paged', $paged, wp_get_referer());
    }

    /**
     * Get filter prefix
     *
     * @return string
     */
    abstract protected function get_filter_prefix();

    /**
     * @return bool|string
     */
    private function get_notice_transient_name()
    {
        return sprintf( '%s_notices_%d', $this->get_slug(), get_current_user_id() ) ;
    }

    /**
     * Add a notice
     *
     * @param $code string|\WP_Error ID of the notice
     * @param $message string message
     * @param string $type
     * @param bool $dismissible
     * @param string|bool $cap
     *
     * @return true|false
     */
    public function add_notice( $code='', $message='', $type='success', $dismissible=true, $cap=false )
    {
        $notices = get_transient( $this->get_notice_transient_name() );

        if ( ! $notices || ! is_array( $notices ) ) {
            $notices = array();
        }

        $data = [];

        if ( is_wp_error( $code ) ){
            $data = $code->get_error_data();
            $error = $code;
            $code = $error->get_error_code();
            $message = esc_html($error->get_error_message() );
            $type = 'error';
        }

        $notices[$code][ 'code' ]    = $code;
        $notices[$code][ 'message' ] = $message;
        $notices[$code][ 'type' ]    = $type;
        $notices[$code][ 'data' ]    = $data;
        $notices[$code][ 'cap' ]     = $cap;
        $notices[$code][ 'dismissible' ] = $dismissible;

        set_transient( $this->get_notice_transient_name(), $notices, MINUTE_IN_SECONDS );

        return true;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function remove_notice( $code='' )
    {

        $notices = get_transient( $this->get_notice_transient_name() );
        unset( $notices[ $code ] );
        set_transient( $this->get_notice_transient_name(), $notices, MINUTE_IN_SECONDS );

        return true;
    }

    /**
     * Get the notices
     */
    public function admin_notices()
    {
        $notices = get_transient( $this->get_notice_transient_name() );

        if ( ! $notices ){
            return;
        }

        foreach ( $notices as $notice ){

            if ( isset_not_empty( $notice, 'cap' ) && ! current_user_can( $notice[ 'cap' ] ) ){
                continue;
            }

            ?>
            <div id="<?php esc_attr_e( $notice['code'] ); ?>" class="notice <?php echo $this->get_slug(); ?>-notice notice-<?php esc_attr_e( $notice[ 'type' ] ); ?> <?php if ( $notice[ 'dismissible' ] ) echo 'is-dismissible'; ?>"><p><strong><?php echo wp_kses_post( $notice[ 'message' ] ); ?></strong></p>
                <?php if ( $notice[ 'type' ] === 'error' && ! empty( $notice[ 'data' ] ) ): ?>
                    <p><textarea class="code" style="width: 100%;" readonly><?php echo wp_json_encode( $notice[ 'data' ], JSON_PRETTY_PRINT ); ?></textarea></p>
                <?php endif; ?>
            </div>
            <?php
        }

        delete_transient( $this->get_notice_transient_name() );
    }
}