<?php
namespace ExtendedCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Autoloader {

    private static $classes_map = [];

    public static function run() {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    private static function load_class( $relative_class_name ) {
        if ( isset( self::$classes_map[ $relative_class_name ] ) ) {
            $filename = EXTENDED_CORE_PATH . '/' . self::$classes_map[ $relative_class_name ];
        } else {
            $filename = strtolower(
                preg_replace(
                    [ '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
                    [ '$1-$2', '-', DIRECTORY_SEPARATOR ],
                    $relative_class_name
                )
            );

            $is_filename = EXTENDED_CORE_PATH . $filename . '.php';

            if ( ! file_exists( $is_filename ) ){
                $filename = wp_normalize_path( EXTENDED_CORE_PATH . 'includes/' . $filename . '.php' );
            } else {
                $filename = $is_filename;
            }
        }

        if ( is_readable( $filename ) ) {
            require $filename;
        }
    }

    private static function autoload( $class ) {
        if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
            return;
        }

        $relative_class_name = preg_replace( '/^' . __NAMESPACE__ . '\\\/', '', $class );

        $final_class_name = __NAMESPACE__ . '\\' . $relative_class_name;

        if ( ! class_exists( $final_class_name ) ) {
            self::load_class( $relative_class_name );
        }
    }
}