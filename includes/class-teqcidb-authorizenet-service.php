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
use net\authorize\api\contract\v1\ArrayOfSetting;
use net\authorize\api\contract\v1\GetHostedPaymentPageRequest;
use net\authorize\api\contract\v1\SettingType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\GetHostedPaymentPageController;

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

    /**
     * Request an Accept Hosted payment token.
     *
     * @param array<string, mixed> $args Accept Hosted request values.
     *
     * @return string|WP_Error
     */
    public function create_accept_hosted_payment_token( array $args ) {
        if ( ! class_exists( GetHostedPaymentPageRequest::class ) || ! class_exists( GetHostedPaymentPageController::class ) ) {
            return new WP_Error(
                'teqcidb_authorizenet_sdk_missing',
                __( 'Authorize.Net SDK is unavailable. Run Composer install for this plugin.', 'teqcidb' )
            );
        }

        $authentication = $this->get_merchant_authentication();

        if ( is_wp_error( $authentication ) ) {
            return $authentication;
        }

        $amount      = isset( $args['amount'] ) ? (float) $args['amount'] : 0;
        $invoice_id  = isset( $args['invoice_number'] ) ? sanitize_text_field( (string) $args['invoice_number'] ) : '';
        $description = isset( $args['description'] ) ? sanitize_text_field( (string) $args['description'] ) : '';

        if ( $amount <= 0 ) {
            return new WP_Error(
                'teqcidb_authorizenet_invalid_amount',
                __( 'A valid class amount is required to begin checkout.', 'teqcidb' )
            );
        }

        $transaction_request = new TransactionRequestType();
        $transaction_request->setTransactionType( 'authCaptureTransaction' );
        $transaction_request->setAmount( number_format( $amount, 2, '.', '' ) );

        if ( '' !== $invoice_id ) {
            $order = new \net\authorize\api\contract\v1\OrderType();
            $order->setInvoiceNumber( $invoice_id );

            if ( '' !== $description ) {
                $order->setDescription( $description );
            }

            $transaction_request->setOrder( $order );
        }

        $setting_return_options = new SettingType();
        $setting_return_options->setSettingName( 'hostedPaymentReturnOptions' );
        $setting_return_options->setSettingValue(
            wp_json_encode(
                array(
                    'showReceipt' => false,
                )
            )
        );

        $setting_button_options = new SettingType();
        $setting_button_options->setSettingName( 'hostedPaymentButtonOptions' );
        $setting_button_options->setSettingValue(
            wp_json_encode(
                array(
                    'text' => __( 'Pay', 'teqcidb' ),
                )
            )
        );

        $setting_iframe_options = new SettingType();
        $setting_iframe_options->setSettingName( 'hostedPaymentIFrameCommunicatorUrl' );
        $setting_iframe_options->setSettingValue(
            wp_json_encode(
                array(
                    'url' => TEQCIDB_PLUGIN_URL . 'assets/authorizenet-iframe-communicator.html',
                )
            )
        );

        $settings = new ArrayOfSetting();
        $settings->setSetting( array( $setting_return_options, $setting_button_options, $setting_iframe_options ) );

        $request = new GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication( $authentication );
        $request->setTransactionRequest( $transaction_request );
        $request->setHostedPaymentSettings( $settings );

        $controller = new GetHostedPaymentPageController( $request );
        $response   = $controller->executeWithApiResponse( $this->get_api_environment() );

        if ( null === $response ) {
            return new WP_Error(
                'teqcidb_authorizenet_empty_response',
                __( 'Unable to reach Authorize.Net right now. Please try again.', 'teqcidb' )
            );
        }

        if ( 'Ok' === $response->getMessages()->getResultCode() && $response->getToken() ) {
            return sanitize_text_field( $response->getToken() );
        }

        $error_text = __( 'Unable to start payment checkout right now.', 'teqcidb' );
        $messages   = $response->getMessages();

        if ( $messages && $messages->getMessage() ) {
            $message_list = $messages->getMessage();
            $first_error  = is_array( $message_list ) ? reset( $message_list ) : null;

            if ( is_object( $first_error ) && method_exists( $first_error, 'getText' ) ) {
                $error_text = sanitize_text_field( (string) $first_error->getText() );
            }
        }

        return new WP_Error( 'teqcidb_authorizenet_checkout_failed', $error_text );
    }
}
