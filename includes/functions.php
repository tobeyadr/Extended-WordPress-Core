<?php

namespace ExtendedCore;

/**
 * Get a variable from an array or default if it doesn't exist.
 *
 * @param $array
 * @param string $key
 * @param bool $default
 * @return mixed
 */
function get_array_var( $array, $key='', $default=false )
{
    if ( isset_not_empty( $array, $key ) ){
        if ( is_object( $array ) ){
            return $array->$key;
        } elseif ( is_array( $array ) ){
            return $array[ $key ];
        }
    }

    return $default;
}

/**
 * Return if a value in an array isset and is not empty
 *
 * @param $array
 * @param $key
 *
 * @return bool
 */
function isset_not_empty($array, $key='' )
{
    if ( is_object( $array ) ){
        return isset( $array->$key ) && ! empty( $array->$key );
    } elseif ( is_array( $array  ) ){
        return isset( $array[ $key ] ) && ! empty( $array[ $key ] );
    }

    return false;
}

/**
 * Get a variable from the $_POST global
 *
 * @param string $key
 * @param bool $default
 * @return mixed
 */
function get_post_var( $key='', $default=false )
{
    return wp_unslash( get_array_var( $_POST, $key, $default ) );
}

/**
 * Get a variable from the $_REQUEST global
 *
 * @param string $key
 * @param bool $default
 * @return mixed
 */
function get_request_var( $key='', $default=false)
{
    return wp_unslash( get_array_var( $_REQUEST, $key, $default ) );
}

/**
 * Get a variable from the $_GET global
 *
 * @param string $key
 * @param bool $default
 * @return mixed
 */
function get_url_var( $key='', $default=false)
{
    return urlencode( wp_unslash( get_array_var( $_GET, $key, $default ) ) );
}


/**
 * Convert array to HTML tag attributes
 *
 * @param $atts
 * @return string
 */
function array_to_atts( $atts )
{
    $tag = '';
    foreach ($atts as $key => $value) {

        if ( empty( $value ) ){
            continue;
        }

        if ( $key === 'style' && is_array( $value ) ){
            $value = array_to_css( $value );
        }

        if ( is_array( $value ) ){
            $value = implode( ' ', $value );
        }

        $tag .= sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
    }

    return $tag;
}

/**
 * Convert array to CSS style attributes
 *
 * @param $atts
 * @return string
 */
function array_to_css( $atts )
{
    $css = '';

    foreach ($atts as $key => $value) {

        if ( empty( $value ) || is_numeric( $key ) ){
            continue;
        }

        $css .= sanitize_key( $key ) . ':' . esc_attr( $value ) . ';';
    }

    return $css;
}

/**
 * Set a cookie the WP way
 *
 * @param string $name
 * @param string $val
 * @param bool $expiry
 * @return bool
 */
function set_cookie( $name='', $val='', $expiry=false )
{
    return setcookie( $name, $val, time()+$expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
}

/**
 * Retrieve a cookie
 *
 * @param string $cookie
 * @param bool $default
 * @return mixed
 */
function get_cookie( $cookie='', $default=false )
{
    return get_array_var( $_COOKIE, $cookie, $default );
}

/**
 * Delete a cookie
 *
 * @param string $cookie
 * @return bool
 */
function delete_cookie( $cookie='' ){
    unset($_COOKIE[$cookie]);
    // empty value and expiration one hour before
    return setcookie($cookie, '', time() - 3600);
}

/**
 * Ensures an array
 *
 * @param $array
 * @return array
 */
function ensure_array( $array )
{
    if ( is_array( $array ) ){
        return $array;
    }

    return [ $array ];
}

/**
 * Register required scripts
 */
function register_admin_scripts()
{
    // Scripts
    wp_register_script( 'select2', EXTENDED_CORE_ASSETS_URL . 'lib/select2/js/select2.js', [], EXTENDED_CORE_VERSION );
    wp_register_script( 'select2-full', EXTENDED_CORE_ASSETS_URL . 'lib/select2/js/select2.full.js', [], EXTENDED_CORE_VERSION );
    wp_register_script( 'extended-core-admin', EXTENDED_CORE_ASSETS_URL . 'js/admin.js', [ 'jquery', 'jquery-ui-autocomplete', 'select2-full', 'wp-color-picker' ], EXTENDED_CORE_VERSION );

    wp_localize_script( 'extended-core-admin', 'ExtendedCore', [
	    '_wpnonce'  => wp_create_nonce(),
	    '_wprest'   => wp_create_nonce( 'wp_rest' ),
	    '_adminajax' => wp_create_nonce( 'admin_ajax' ),
	    '_ajax_linking_nonce' => wp_create_nonce( 'internal-linking' ),
    ] );

    // Styles
    wp_register_style( 'jquery-ui', EXTENDED_CORE_ASSETS_URL . 'lib/jquery-ui/jquery-ui.min.css', [], EXTENDED_CORE_VERSION );
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_admin_scripts' );