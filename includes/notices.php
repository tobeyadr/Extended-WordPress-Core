<?php
namespace ExtendedCore;

/**
 * Notices
 *
 * Easy implementation for notices on admin pages in Groundhogg.
 * This class is used by all admin page classes, thus all notices will appear on any admin page.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Notices
{
    public function __construct()
    {
        add_action( 'admin_notices', [ $this, 'notices' ] );
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
    protected function get_transient_name()
    {
        if ( is_user_logged_in() ){
            return sprintf( '%s_notices_%d', $this->get_filter_prefix(), get_current_user_id() ) ;
        }

        return false;
    }

	/**
	 * @return bool
	 */
    protected function can_add_notices()
    {
        return (bool) $this->get_transient_name();
    }

	/**
	 * @return mixed
	 */
    protected function get_stored_notices()
    {
        return get_transient( $this->get_transient_name() );
    }

	/**
	 * @param array $notices
	 *
	 * @return bool
	 */
    protected function store_notices( $notices=[] )
    {
	    return set_transient( $this->get_transient_name() , $notices, MINUTE_IN_SECONDS );
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
    public function add( $code='', $message='', $type='success', $dismissible=true, $cap=false )
    {
        if ( ! $this->can_add_notices() ){
            return false;
        }

        $notices = $this->get_stored_notices();

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

        $this->store_notices( $notices );

        return true;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function remove( $code='' )
    {
	    if ( ! $this->can_add_notices() ){
	        return false;
        }

        $notices = $this->get_stored_notices();
        unset( $notices[ $code ] );
        $this->store_notices( $notices );

        return true;
    }

    /**
     * Get the notices
     */
    public function notices()
    {
        $notices = $this->get_stored_notices();

        if ( ! $notices ){
            $notices = [];
        }

        foreach ( $notices as $notice ){

            if ( isset_not_empty( $notice, 'cap' ) && ! current_user_can( $notice[ 'cap' ] ) ){
                continue;
            }

            ?>
            <div id="<?php esc_attr_e( $notice['code'] ); ?>" class="notice <?php echo $this->get_filter_prefix(); ?>-notice notice-<?php esc_attr_e( $notice[ 'type' ] ); ?> <?php if ( $notice[ 'dismissible' ] ) echo 'is-dismissible'; ?>"><p><strong><?php echo wp_kses_post( $notice[ 'message' ] ); ?></strong></p>
                <?php if ( $notice[ 'type' ] === 'error' && ! empty( $notice[ 'data' ] ) ): ?>
                    <p><textarea class="code" style="width: 100%;" readonly><?php echo wp_json_encode( $notice[ 'data' ], JSON_PRETTY_PRINT ); ?></textarea></p>
                <?php endif; ?>
            </div>
            <?php
        }

        delete_transient( $this->get_transient_name() );
    }
}
