<?php
/**
 * Shared helper methods for working with Student data.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TEQCIDB_Student_Helper {

    /**
     * Retrieve the first Student record prepared for template previews.
     *
     * @return array
     */
    public static function get_first_preview_data() {
        static $preview_data = null;

        if ( null !== $preview_data ) {
            return $preview_data;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_students';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            $preview_data = array();
            return $preview_data;
        }

        $primary_key = TEQCIDB_Student_Schema::get_primary_key();
        $row         = $wpdb->get_row( "SELECT * FROM $table_name ORDER BY $primary_key ASC LIMIT 1", ARRAY_A );

        if ( ! $row ) {
            $preview_data = array();
            return $preview_data;
        }

        $prepared = array();

        foreach ( $row as $key => $value ) {
            $definition        = TEQCIDB_Student_Schema::get_field( $key );
            $prepared[ $key ] = self::normalize_preview_token_value( $definition, $value );
        }

        $preview_data = $prepared;

        return $preview_data;
    }

    /**
     * Normalize a stored value so it can be injected into a template token.
     *
     * @param string $key   Database column key.
     * @param mixed  $value Stored value.
     *
     * @return string
     */
    private static function normalize_preview_token_value( $definition, $value ) {
        if ( null === $value ) {
            return '';
        }

        $data_type = is_array( $definition ) && isset( $definition['data_type'] ) ? $definition['data_type'] : 'string';

        switch ( $data_type ) {
            case 'date':
                $value = (string) $value;

                if ( '' === $value || '0000-00-00' === $value ) {
                    return '';
                }

                $date = date_create( $value );

                return $date ? $date->format( 'Y-m-d' ) : '';
            case 'boolean':
                return ( (int) $value ) ? '1' : '0';
            case 'integer':
                return (string) (int) $value;
            case 'email':
            case 'string':
            case 'datetime':
                return is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
            case 'text':
                return is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
            default:
                return is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
        }
    }
}
