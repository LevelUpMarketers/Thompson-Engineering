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

        $row = $wpdb->get_row( "SELECT * FROM $table_name ORDER BY id ASC LIMIT 1", ARRAY_A );

        if ( ! $row ) {
            $preview_data = array();
            return $preview_data;
        }

        $prepared = array();

        foreach ( $row as $key => $value ) {
            $prepared[ $key ] = self::normalize_preview_token_value( $key, $value );
        }

        if ( isset( $row['student_address'] ) ) {
            $address = self::decode_student_address_field( $row['student_address'] );

            foreach ( $address as $address_key => $address_value ) {
                $prepared[ 'student_address_' . $address_key ] = $address_value;
            }
        }

        if ( isset( $row['their_representative'] ) ) {
            $representative = self::decode_representative_field( $row['their_representative'] );

            foreach ( $representative as $rep_key => $rep_value ) {
                $prepared[ 'representative_' . $rep_key ] = $rep_value;
            }
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
    private static function normalize_preview_token_value( $key, $value ) {
        if ( null === $value ) {
            return '';
        }

        if ( in_array( $key, array( 'old_companies', 'associations' ), true ) ) {
            $items = self::decode_list_field( $value );

            return empty( $items ) ? '' : implode( ', ', $items );
        }

        if ( 'student_address' === $key ) {
            $address = self::decode_student_address_field( $value );

            return self::format_address_for_tokens( $address );
        }

        if ( 'their_representative' === $key ) {
            $contact = self::decode_representative_field( $value );

            return self::format_representative_for_tokens( $contact );
        }

        if ( 'placeholder_3' === $key ) {
            $value = (string) $value;

            if ( '' === $value || '0000-00-00' === $value ) {
                return '';
            }

            $date = date_create( $value );

            return $date ? $date->format( 'Y-m-d' ) : '';
        }

        if ( in_array( $key, array( 'placeholder_5', 'placeholder_6' ), true ) ) {
            $value = (string) $value;

            if ( preg_match( '/^(\d{2}:\d{2})/', $value, $matches ) ) {
                return $matches[1];
            }

            return '';
        }

        if ( in_array( $key, array( 'placeholder_16', 'placeholder_17', 'placeholder_18' ), true ) ) {
            return number_format( (float) $value, 2, '.', '' );
        }

        if ( in_array( $key, array( 'placeholder_24', 'placeholder_25' ), true ) ) {
            if ( is_array( $value ) ) {
                $items = $value;
            } else {
                $decoded = json_decode( (string) $value, true );
                $items   = is_array( $decoded ) ? $decoded : array();
            }

            if ( empty( $items ) ) {
                return '';
            }

            $items = array_map( 'strval', $items );
            $items = array_map( 'wp_kses_post', $items );
            $items = array_filter( $items, 'strlen' );

            return implode( ', ', $items );
        }

        if ( 'placeholder_26' === $key ) {
            $color = sanitize_hex_color( (string) $value );
            return $color ? $color : '';
        }

        if ( 'placeholder_27' === $key ) {
            $attachment_id = absint( $value );
            $url           = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';

            return $url ? esc_url_raw( $url ) : '';
        }

        if ( 'placeholder_28' === $key ) {
            return wp_kses_post( (string) $value );
        }

        if ( is_scalar( $value ) ) {
            $string_value = (string) $value;

            return wp_kses_post( $string_value );
        }

        return '';
    }

    private static function decode_list_field( $value ) {
        if ( is_array( $value ) ) {
            $items = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
            $items   = is_array( $decoded ) ? $decoded : array();
        }

        if ( empty( $items ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $normalized = wp_kses_post( (string) $item );

            if ( '' !== $normalized ) {
                $sanitized[] = $normalized;
            }
        }

        return $sanitized;
    }

    private static function decode_student_address_field( $value ) {
        $defaults = array(
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        if ( is_array( $value ) ) {
            $decoded = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
        }

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        foreach ( $defaults as $key => $default ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        return $defaults;
    }

    private static function format_address_for_tokens( array $address ) {
        $parts = array();

        if ( $address['street_1'] ) {
            $parts[] = $address['street_1'];
        }

        if ( $address['street_2'] ) {
            $parts[] = $address['street_2'];
        }

        $city_state = trim( $address['city'] );

        if ( $address['state'] ) {
            $city_state = $city_state ? $city_state . ', ' . $address['state'] : $address['state'];
        }

        if ( $city_state ) {
            $parts[] = $city_state;
        }

        if ( $address['postal_code'] ) {
            $parts[] = $address['postal_code'];
        }

        return implode( ', ', array_filter( $parts, 'strlen' ) );
    }

    private static function decode_representative_field( $value ) {
        $defaults = array(
            'first_name' => '',
            'last_name'  => '',
            'email'      => '',
            'phone'      => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        if ( is_array( $value ) ) {
            $decoded = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
        }

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        foreach ( array( 'first_name', 'last_name', 'phone' ) as $key ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        if ( isset( $decoded['email'] ) ) {
            $email = sanitize_email( $decoded['email'] );
            $defaults['email'] = $email ? $email : '';
        }

        return $defaults;
    }

    private static function format_representative_for_tokens( array $contact ) {
        $parts = array();
        $name  = trim( $contact['first_name'] . ' ' . $contact['last_name'] );

        if ( $name ) {
            $parts[] = $name;
        }

        if ( $contact['email'] ) {
            $parts[] = $contact['email'];
        }

        if ( $contact['phone'] ) {
            $parts[] = $contact['phone'];
        }

        return implode( ' | ', array_filter( $parts, 'strlen' ) );
    }
}
