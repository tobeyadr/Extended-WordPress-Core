<?php
namespace ExtendedCore\Admin;

use function ExtendedCore\get_request_var;

abstract class Metabox
{

    public function __construct()
    {
        add_action( 'add_meta_boxes', [ $this, 'register' ] );
        add_action( 'save_post', [ $this, 'save_wrap' ] );
    }

    /**
     * The metabox name
     *
     * @return mixed
     */
    abstract protected function get_name();

    /**
     * The metabox ID
     *
     * @return string
     */
    abstract protected function get_id();

    /**
     * The screen for which this metabox should be shown
     * Default is all
     *
     * @return null
     */
    protected function get_screen()
    {
        return null;
    }

    /**
     * The meta box context
     *
     * @return mixed
     */
    protected function get_context()
    {
        return 'side';
    }

    /**
     * The minimum cap for the metabox
     *
     * @return string
     */
    protected function get_cap()
    {
        return 'edit_posts';
    }

    /**
     * Register the metabox
     */
    public function register()
    {
        if ( ! current_user_can( $this->get_cap() ) )
            return;

        add_meta_box(
            $this->get_id(),
            $this->get_name(),
            [ $this, 'render_wrap' ],
            $this->get_screen(),
            $this->get_context(),
            'default'
        );
    }

    /**
     * Render wrap with nonce for the content
     *
     * @param $post
     */
    public function render_wrap( $post )
    {
        wp_nonce_field( $this->get_id() . '_save', $this->get_id() . '_nonce' );

        $this->render( $post );
    }

    /**
     * Render the metabox outbut
     *
     * @param $post
     * @return mixed
     */
    abstract protected function render( $post );

    /**
     * Save wrapper for the metabox
     *
     * @param $post_id
     */
    public function save_wrap( $post_id )
    {
        if ( current_user_can( $this->get_cap() ) && wp_verify_nonce( get_request_var( $this->get_id() . '_nonce' ), $this->get_id() . '_save' ) ){
            $this->save( $post_id );
        }
    }

    /**
     * Save any metabox settings
     *
     * @param $post_id
     * @return mixed
     */
    abstract protected function save( $post_id );

}