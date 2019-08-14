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