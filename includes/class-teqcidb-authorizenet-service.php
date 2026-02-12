<?php
/**
 * Authorize.Net SDK integration utilities.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\MerchantAuthenticationType;

class TEQCIDB_AuthorizeNet_Service {

    const OPTION_NAME = 'teqcidb_api_settings';
    const API_KEY     = 'payment_gateway';

    const FIELD_ENVIRONMENT    = 'payment_gateway_environment';
    const FIELD_LOGIN_ID       = 'payment_gateway_login_id';
    const FIELD_TRANSACTION_KEY = 'payment_gateway_transaction_key';
    const FIELD_CLIENT_KEY     = 'payment_gateway_client_key';

    /**
     * Retrieve the stored payment gateway settings.
     *
     * @return array<string, string>
     */
    public function get_payment_gateway_settings() {
        $all_settings = get_option( self::OPTION_NAME, array() );

        if ( ! is_array( $all_settings ) ) {
            $all_settings = array();
        }

        $stored = isset( $all_settings[ self::API_KEY ] ) && is_array( $all_settings[ self::API_KEY ] )
            ? $all_settings[ self::API_KEY ]
            : array();

        return array(
            self::FIELD_ENVIRONMENT     => isset( $stored[ self::FIELD_ENVIRONMENT ] ) ? sanitize_key( $stored[ self::FIELD_ENVIRONMENT ] ) : 'sandbox',
            self::FIELD_LOGIN_ID        => isset( $stored[ self::FIELD_LOGIN_ID ] ) ? sanitize_text_field( $stored[ self::FIELD_LOGIN_ID ] ) : '',
            self::FIELD_TRANSACTION_KEY => isset( $stored[ self::FIELD_TRANSACTION_KEY ] ) ? sanitize_text_field( $stored[ self::FIELD_TRANSACTION_KEY ] ) : '',
            self::FIELD_CLIENT_KEY      => isset( $stored[ self::FIELD_CLIENT_KEY ] ) ? sanitize_text_field( $stored[ self::FIELD_CLIENT_KEY ] ) : '',
        );
    }

    /**
     * Determine whether API credentials are available.
     *
     * @return bool
     */
    public function has_credentials() {
        $settings = $this->get_payment_gateway_settings();

        return '' !== $settings[ self::FIELD_LOGIN_ID ] && '' !== $settings[ self::FIELD_TRANSACTION_KEY ];
    }

    /**
     * Build an Authorize.Net merchant authentication object.
     *
     * @return MerchantAuthenticationType|WP_Error
     */
    public function get_merchant_authentication() {
        if ( ! class_exists( MerchantAuthenticationType::class ) ) {
            return new WP_Error(
                'teqcidb_authorizenet_sdk_missing',
                __( 'Authorize.Net SDK is unavailable. Run Composer install for this plugin.', 'teqcidb' )
            );
        }

        $settings = $this->get_payment_gateway_settings();

        if ( '' === $settings[ self::FIELD_LOGIN_ID ] || '' === $settings[ self::FIELD_TRANSACTION_KEY ] ) {
            return new WP_Error(
                'teqcidb_authorizenet_credentials_missing',
                __( 'Authorize.Net API Login ID and Transaction Key are required.', 'teqcidb' )
            );
        }

        $authentication = new MerchantAuthenticationType();
        $authentication->setName( $settings[ self::FIELD_LOGIN_ID ] );
        $authentication->setTransactionKey( $settings[ self::FIELD_TRANSACTION_KEY ] );

        return $authentication;
    }


    /**
     * Determine whether the configured gateway environment is live.
     *
     * @return bool
     */
    public function is_live_mode() {
        $settings    = $this->get_payment_gateway_settings();
        $environment = $settings[ self::FIELD_ENVIRONMENT ];

        return 'live' === $environment;
    }

    /**
     * Retrieve the API environment constant for SDK controllers.
     *
     * @return string
     */
    public function get_api_environment() {
        $settings    = $this->get_payment_gateway_settings();
        $environment = $settings[ self::FIELD_ENVIRONMENT ];

        if ( 'live' === $environment ) {
            return ANetEnvironment::PRODUCTION;
        }

        return ANetEnvironment::SANDBOX;
    }
}
