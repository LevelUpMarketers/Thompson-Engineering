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
     * Retrieve the newest Student record prepared for template previews.
     *
     * @return array
     */
    public static function get_latest_preview_data() {
        static $preview_data = null;

        if ( null !== $preview_data ) {
            return $preview_data;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_students';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            $preview_data = self::get_latest_class_preview_data();
            return $preview_data;
        }

        $row = $wpdb->get_row( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1", ARRAY_A );

        if ( ! $row ) {
            $preview_data = self::get_latest_class_preview_data();
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

            $prepared['student_representative'] = trim( $representative['first_name'] . ' ' . $representative['last_name'] );
        }

        $prepared['student_first_name']    = isset( $prepared['first_name'] ) ? $prepared['first_name'] : ( isset( $prepared['placeholder_1'] ) ? $prepared['placeholder_1'] : '' );
        $prepared['student_last_name']     = isset( $prepared['last_name'] ) ? $prepared['last_name'] : '';
        $prepared['student_email']         = isset( $prepared['email'] ) ? $prepared['email'] : ( isset( $prepared['placeholder_2'] ) ? $prepared['placeholder_2'] : '' );
        $prepared['student_company']       = isset( $prepared['company'] ) ? $prepared['company'] : ( isset( $prepared['placeholder_3'] ) ? $prepared['placeholder_3'] : '' );
        $prepared['student_phone_cell']    = isset( $prepared['phone_cell'] ) ? $prepared['phone_cell'] : ( isset( $prepared['placeholder_4'] ) ? $prepared['placeholder_4'] : '' );
        $prepared['student_phone_office']            = isset( $prepared['phone_office'] ) ? $prepared['phone_office'] : '';
        $prepared['student_representative']          = isset( $prepared['student_representative'] ) ? $prepared['student_representative'] : '';
        $prepared['representative_first_name']       = isset( $prepared['first_name'] ) ? $prepared['first_name'] : '';
        $prepared['representative_last_name']        = isset( $prepared['last_name'] ) ? $prepared['last_name'] : '';
        $prepared['individuals_registered']           = self::get_latest_representative_registration_individuals_html();
        $prepared['student_certification_expiration'] = self::format_date_for_token( isset( $prepared['expiration_date'] ) ? $prepared['expiration_date'] : '' );

        $prepared = array_merge( $prepared, self::get_latest_class_preview_data() );

        $preview_data = $prepared;

        return $preview_data;
    }


    /**
     * Retrieve the newest Class record prepared for template previews.
     *
     * @return array
     */
    public static function get_latest_class_preview_data() {
        static $class_preview_data = null;

        if ( null !== $class_preview_data ) {
            return $class_preview_data;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_classes';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            $class_preview_data = array();
            return $class_preview_data;
        }

        $row = $wpdb->get_row( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1", ARRAY_A );

        if ( ! $row ) {
            $class_preview_data = array();
            return $class_preview_data;
        }

        $class_preview_data = array(
            'class_name'                       => isset( $row['classname'] ) ? sanitize_text_field( (string) $row['classname'] ) : '',
            'class_type'                       => self::format_class_type_for_token( isset( $row['classtype'] ) ? $row['classtype'] : '' ),
            'class_date'                       => self::format_date_for_token( isset( $row['classstartdate'] ) ? $row['classstartdate'] : '' ),
            'class_time'                       => self::format_time_for_token( isset( $row['classstarttime'] ) ? $row['classstarttime'] : '' ),
            'class_page'                       => self::format_class_page_url_for_token( isset( $row['classurl'] ) ? $row['classurl'] : '' ),
            'class_team_link'                  => isset( $row['teamslink'] ) ? esc_url_raw( (string) $row['teamslink'] ) : '',
            'class_cost_total_transaction'     => self::format_currency_for_token( isset( $row['classcost'] ) ? $row['classcost'] : '' ),
            'class_cost_student_self'          => self::format_currency_for_token( isset( $row['classcost'] ) ? $row['classcost'] : '' ),
            'class_cost_student_representative' => self::format_currency_for_token( isset( $row['classcost'] ) ? $row['classcost'] : '' ),
        );

        return $class_preview_data;
    }

    /**
     * Backward-compatible wrapper for legacy callers that still request the first preview entity.
     *
     * @return array
     */
    public static function get_first_preview_data() {
        return self::get_latest_preview_data();
    }

    /**
     * Build an email-safe unordered list of students from the most recent representative registration transaction.
     *
     * @return string
     */
    private static function get_latest_representative_registration_individuals_html() {
        global $wpdb;

        $payment_table  = $wpdb->prefix . 'teqcidb_paymenthistory';
        $students_table = $wpdb->prefix . 'teqcidb_students';

        $payment_like  = $wpdb->esc_like( $payment_table );
        $payment_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $payment_like ) );

        if ( $payment_found !== $payment_table ) {
            return '';
        }

        $students_like  = $wpdb->esc_like( $students_table );
        $students_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $students_like ) );

        if ( $students_found !== $students_table ) {
            return '';
        }

        $transaction = $wpdb->get_row(
            "SELECT multiplestudents FROM $payment_table WHERE multiplestudents IS NOT NULL AND multiplestudents != '' ORDER BY id DESC LIMIT 1",
            ARRAY_A
        );

        if ( ! is_array( $transaction ) || empty( $transaction['multiplestudents'] ) ) {
            return '';
        }

        $decoded = json_decode( (string) $transaction['multiplestudents'], true );

        if ( ! is_array( $decoded ) || empty( $decoded ) ) {
            return '';
        }

        $ordered_wpids = array();

        foreach ( $decoded as $entry ) {
            if ( is_array( $entry ) ) {
                $wpid = isset( $entry['wpid'] ) ? absint( $entry['wpid'] ) : 0;
            } else {
                $wpid = absint( $entry );
            }

            if ( $wpid <= 0 || in_array( $wpid, $ordered_wpids, true ) ) {
                continue;
            }

            $ordered_wpids[] = $wpid;
        }

        if ( empty( $ordered_wpids ) ) {
            return '';
        }

        $placeholders = implode( ', ', array_fill( 0, count( $ordered_wpids ), '%d' ) );
        $query        = "SELECT wpuserid, first_name, last_name, email FROM $students_table WHERE wpuserid IN ($placeholders)";
        $rows         = $wpdb->get_results( $wpdb->prepare( $query, $ordered_wpids ), ARRAY_A );

        if ( ! is_array( $rows ) || empty( $rows ) ) {
            return '';
        }

        $rows_by_wpid = array();

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $wpid = isset( $row['wpuserid'] ) ? absint( $row['wpuserid'] ) : 0;

            if ( $wpid <= 0 ) {
                continue;
            }

            $rows_by_wpid[ $wpid ] = $row;
        }

        if ( empty( $rows_by_wpid ) ) {
            return '';
        }

        $items = array();

        foreach ( $ordered_wpids as $wpid ) {
            if ( ! isset( $rows_by_wpid[ $wpid ] ) ) {
                continue;
            }

            $row        = $rows_by_wpid[ $wpid ];
            $first_name = isset( $row['first_name'] ) ? sanitize_text_field( (string) $row['first_name'] ) : '';
            $last_name  = isset( $row['last_name'] ) ? sanitize_text_field( (string) $row['last_name'] ) : '';
            $email      = isset( $row['email'] ) ? sanitize_email( (string) $row['email'] ) : '';
            $full_name  = trim( $first_name . ' ' . $last_name );

            if ( '' === $full_name && '' === $email ) {
                continue;
            }

            $label = '';

            if ( '' !== $full_name && '' !== $email ) {
                $label = sprintf( '%1$s (%2$s)', $full_name, $email );
            } elseif ( '' !== $full_name ) {
                $label = $full_name;
            } else {
                $label = $email;
            }

            $items[] = '<li>' . esc_html( $label ) . '</li>';
        }

        if ( empty( $items ) ) {
            return '';
        }

        return '<ul>' . implode( '', $items ) . '</ul>';
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

        if ( in_array( $key, array( 'phone_cell', 'phone_office', 'fax' ), true ) ) {
            return self::format_phone_value( $value );
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

    /**
     * Format class type values for communications tokens.
     *
     * @param mixed $value Class type value.
     *
     * @return string
     */
    private static function format_class_type_for_token( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = sanitize_text_field( (string) $value );

        return '' === $value ? '' : ucwords( strtolower( $value ) );
    }

    /**
     * Format class page URLs for communications tokens.
     *
     * @param mixed $value Class page URL value.
     *
     * @return string
     */
    private static function format_class_page_url_for_token( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '#^https?://#i', $value ) ) {
            return esc_url_raw( $value );
        }

        $normalized_path = '/' . ltrim( $value, '/' );

        return esc_url_raw( home_url( $normalized_path ) );
    }

    /**
     * Format date-like values for communications tokens.
     *
     * @param mixed $value Date value.
     *
     * @return string
     */
    private static function format_date_for_token( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = trim( (string) $value );

        if ( '' === $value || '0000-00-00' === $value ) {
            return '';
        }

        $date = date_create( $value );

        return $date ? $date->format( 'm-d-Y' ) : '';
    }

    /**
     * Format time-like values for communications tokens.
     *
     * @param mixed $value Time value.
     *
     * @return string
     */
    private static function format_time_for_token( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = trim( (string) $value );

        if ( '' === $value || '00:00:00' === $value || '00:00' === $value ) {
            return '';
        }

        $time = date_create( $value );

        return $time ? $time->format( 'g:i A' ) : '';
    }



    /**
     * Format class cost values for communications tokens.
     *
     * @param mixed $value Stored class cost value.
     *
     * @return string
     */
    private static function format_currency_for_token( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $normalized = preg_replace( '/[^0-9.\-]/', '', (string) $value );

        if ( null === $normalized || '' === $normalized || ! is_numeric( $normalized ) ) {
            return '';
        }

        return '$' . number_format( (float) $normalized, 2, '.', ',' );
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

        if ( isset( $decoded['zip_code'] ) ) {
            $decoded['postal_code'] = $decoded['zip_code'];
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
            'wpuserid'   => '',
            'uniquestudentid' => '',
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

        foreach ( array( 'first_name', 'last_name' ) as $key ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        if ( isset( $decoded['phone'] ) ) {
            $defaults['phone'] = self::format_phone_value( $decoded['phone'] );
        }

        if ( isset( $decoded['email'] ) ) {
            $email = sanitize_email( $decoded['email'] );
            $defaults['email'] = $email ? $email : '';
        }

        if ( isset( $decoded['wpuserid'] ) && is_numeric( $decoded['wpuserid'] ) ) {
            $defaults['wpuserid'] = (string) absint( $decoded['wpuserid'] );
        }

        if ( isset( $decoded['uniquestudentid'] ) && is_scalar( $decoded['uniquestudentid'] ) ) {
            $defaults['uniquestudentid'] = sanitize_text_field( (string) $decoded['uniquestudentid'] );
        }

        return $defaults;
    }

    private static function format_phone_value( $value ) {
        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $digits = preg_replace( '/\D+/', '', (string) $value );

        if ( '' === $digits ) {
            return '';
        }

        if ( strlen( $digits ) > 10 && '1' === substr( $digits, 0, 1 ) ) {
            $digits = substr( $digits, 1 );
        }

        if ( strlen( $digits ) > 10 ) {
            $digits = substr( $digits, 0, 10 );
        }

        if ( 10 !== strlen( $digits ) ) {
            return $digits;
        }

        return sprintf(
            '(%1$s) %2$s-%3$s',
            substr( $digits, 0, 3 ),
            substr( $digits, 3, 3 ),
            substr( $digits, 6, 4 )
        );
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
