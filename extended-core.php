<?php
/*
 * Extended Core is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Extended Core is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

use ExtendedCore\Autoloader;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'EXTENDED_CORE_VERSION' ) ):

define( 'EXTENDED_CORE_VERSION', '1.0' );
define( 'EXTENDED_CORE_PREVIOUS_STABLE_VERSION', '1.0' );

define( 'EXTENDED_CORE__FILE__', __FILE__ );
define( 'EXTENDED_CORE_BASE', plugin_basename( EXTENDED_CORE__FILE__ ) );
define( 'EXTENDED_CORE_PATH', plugin_dir_path( EXTENDED_CORE__FILE__ ) );

define( 'EXTENDED_CORE_URL', plugins_url( '/', EXTENDED_CORE__FILE__ ) );

define( 'EXTENDED_CORE_ASSETS_PATH', EXTENDED_CORE_PATH . 'assets/' );
define( 'EXTENDED_CORE_ASSETS_URL', EXTENDED_CORE_URL . 'assets/' );

add_action( 'plugins_loaded', 'extended_core_load_plugin_textdomain' );

define( 'EXTENDED_CORE_TEXT_DOMAIN', 'extended_core' );

if ( ! version_compare( PHP_VERSION, '5.6', '>=' ) ) {
    add_action( 'admin_notices', 'extended_core_fail_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) {
    add_action( 'admin_notices', 'extended_core_fail_wp_version' );
} else {
    require dirname( __FILE__ ) . '/includes/autoloader.php';
    require dirname( __FILE__ ) . '/includes/functions.php';
    Autoloader::run();
}

/**
 * Groundhogg loaded.
 *
 * Fires when Groundhogg was fully loaded and instantiated.
 *
 * @since 1.0.0
 */
do_action( 'extended_core/loaded' );

/**
 * Load Groundhogg textdomain.
 *
 * Load gettext translate for Groundhogg text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function extended_core_load_plugin_textdomain() {
    load_plugin_textdomain( 'extended_core', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Groundhogg admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 2.0
 *
 * @return void
 */
function extended_core_fail_php_version() {
    /* translators: %s: PHP version */
    $message = sprintf( esc_html__( 'Extended Core requires PHP version %s+, plugin is currently NOT RUNNING.', 'extended_core' ), '5.6' );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}

/**
 * Groundhogg admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 2.0
 *
 * @return void
 */
function extended_core_fail_wp_version() {
    /* translators: %s: WordPress version */
    $message = sprintf( esc_html__( 'Extended Core requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'extended_core' ), '4.9' );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}

endif;