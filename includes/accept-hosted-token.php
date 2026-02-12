<?php
/**
 * Authorize.Net Accept Hosted token endpoint.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

final class TEQCIDB_Accept_Hosted_Token {

    /**
     * Register hooks.
     *
     * @return void
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @return void
     */
    public static function register_routes() {
        register_rest_route(
            'teqcidb/v1',
            '/accept-hosted/token',
            array(
                'methods'             => 'POST',
                'callback'            => array( __CLASS__, 'handle_token_request' ),
                'permission_callback' => array( __CLASS__, 'permission_check' ),
            )
        );
    }

    /**
     * Permission callback.
     *
     * This is currently restricted to logged-in users. If you later need guest checkout,
     * you will need a different permission model.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return bool
     */
    public static function permission_check( WP_REST_Request $request ) {
        return is_user_logged_in() && current_user_can( 'read' );
    }

    /**
     * Normalize the amount to a strict 2-decimal string.
     *
     * @param mixed $amount Amount input.
     *
     * @return string
     */
    private static function normalize_amount( $amount ) {
        $amount = is_string( $amount ) ? trim( $amount ) : (string) $amount;

        if ( ! preg_match( '/^\d+(\.\d{1,2})?$/', $amount ) ) {
            return '0.00';
        }

        return number_format( (float) $amount, 2, '.', '' );
    }

    /**
     * Sanitize text fields to max length.
     *
     * @param mixed $value   Potential string value.
     * @param int   $max_len Maximum allowed length.
     *
     * @return string
     */
    private static function safe_text( $value, $max_len = 255 ) {
        $value = is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : '';

        if ( strlen( $value ) > $max_len ) {
            $value = substr( $value, 0, $max_len );
        }

        return $value;
    }

    /**
     * Sanitize URL values for redirect and communicator settings.
     *
     * @param mixed $value Potential URL value.
     *
     * @return string
     */
    private static function safe_url( $value ) {
        $url = is_string( $value ) ? trim( $value ) : '';
        $url = esc_url_raw( $url );

        return $url ? $url : '';
    }

    /**
     * Build and return an Accept Hosted token.
     *
     * @param WP_REST_Request $request Incoming token request.
     *
     * @return WP_REST_Response
     */
    public static function handle_token_request( WP_REST_Request $request ) {
        if ( ! class_exists( AnetAPI\GetHostedPaymentPageRequest::class ) ) {
            return new WP_REST_Response(
                array(
                    'ok'    => false,
                    'error' => __( 'Authorize.Net SDK is unavailable. Run Composer install for this plugin.', 'teqcidb' ),
                ),
                500
            );
        }

        $service = new TEQCIDB_AuthorizeNet_Service();

        $settings        = $service->get_payment_gateway_settings();
        $login_id        = isset( $settings[ TEQCIDB_AuthorizeNet_Service::FIELD_LOGIN_ID ] ) ? (string) $settings[ TEQCIDB_AuthorizeNet_Service::FIELD_LOGIN_ID ] : '';
        $transaction_key = isset( $settings[ TEQCIDB_AuthorizeNet_Service::FIELD_TRANSACTION_KEY ] ) ? (string) $settings[ TEQCIDB_AuthorizeNet_Service::FIELD_TRANSACTION_KEY ] : '';

        if ( '' === $login_id && defined( 'SP_AUTHNET_LOGIN_ID' ) ) {
            $login_id = sanitize_text_field( (string) SP_AUTHNET_LOGIN_ID );
        }

        if ( '' === $transaction_key && defined( 'SP_AUTHNET_TRANSACTION_KEY' ) ) {
            $transaction_key = sanitize_text_field( (string) SP_AUTHNET_TRANSACTION_KEY );
        }

        if ( '' === $login_id || '' === $transaction_key ) {
            return new WP_REST_Response(
                array(
                    'ok'    => false,
                    'error' => __( 'Missing Authorize.Net credentials. Save your API Login ID and Transaction Key in TEQCIDB Settings → API Settings → Payment Gateway (stored in wp_options as teqcidb_api_settings).', 'teqcidb' ),
                ),
                500
            );
        }

        $body = $request->get_json_params();
        if ( ! is_array( $body ) ) {
            $body = array();
        }

        $amount = self::normalize_amount( isset( $body['amount'] ) ? $body['amount'] : '' );
        if ( '0.00' === $amount ) {
            return new WP_REST_Response(
                array(
                    'ok'    => false,
                    'error' => __( 'Invalid amount.', 'teqcidb' ),
                ),
                400
            );
        }

        $invoice_number = self::safe_text( isset( $body['invoiceNumber'] ) ? $body['invoiceNumber'] : '', 20 );
        $description    = self::safe_text( isset( $body['description'] ) ? $body['description'] : '', 255 );
        $customer_email = self::safe_text( isset( $body['customerEmail'] ) ? $body['customerEmail'] : '', 255 );
        $customer_id    = self::safe_text( isset( $body['customerId'] ) ? $body['customerId'] : '', 20 );

        $return_url = self::safe_url( isset( $body['returnUrl'] ) ? $body['returnUrl'] : '' );
        $cancel_url = self::safe_url( isset( $body['cancelUrl'] ) ? $body['cancelUrl'] : '' );

        if ( '' === $return_url ) {
            $return_url = home_url( '/register-for-a-class-qci/' );
        }

        if ( '' === $cancel_url ) {
            $cancel_url = home_url( '/register-for-a-class-qci/' );
        }

        $iframe_communicator_url = home_url( '/sp-authnet-communicator/' );

        try {
            $merchant_authentication = new AnetAPI\MerchantAuthenticationType();
            $merchant_authentication->setName( $login_id );
            $merchant_authentication->setTransactionKey( $transaction_key );

            $transaction_request = new AnetAPI\TransactionRequestType();
            $transaction_request->setTransactionType( 'authCaptureTransaction' );
            $transaction_request->setAmount( $amount );

            if ( '' !== $invoice_number || '' !== $description ) {
                $order = new AnetAPI\OrderType();
                if ( '' !== $invoice_number ) {
                    $order->setInvoiceNumber( $invoice_number );
                }
                if ( '' !== $description ) {
                    $order->setDescription( $description );
                }
                $transaction_request->setOrder( $order );
            }

            if ( '' !== $customer_id || '' !== $customer_email ) {
                $customer = new AnetAPI\CustomerDataType();
                if ( '' !== $customer_id ) {
                    $customer->setId( $customer_id );
                }
                if ( '' !== $customer_email ) {
                    $customer->setEmail( $customer_email );
                }
                $transaction_request->setCustomer( $customer );
            }

            $settings_list = array();

            $return_options = new AnetAPI\SettingType();
            $return_options->setSettingName( 'hostedPaymentReturnOptions' );
            $return_options->setSettingValue(
                wp_json_encode(
                    array(
                        'showReceipt' => false,
                        'url'         => $return_url,
                        'cancelUrl'   => $cancel_url,
                    )
                )
            );
            $settings_list[] = $return_options;

            if ( '' !== $iframe_communicator_url ) {
                $communicator = new AnetAPI\SettingType();
                $communicator->setSettingName( 'hostedPaymentIFrameCommunicatorUrl' );
                $communicator->setSettingValue(
                    wp_json_encode(
                        array(
                            'url' => $iframe_communicator_url,
                        )
                    )
                );
                $settings_list[] = $communicator;
            }

            $hosted_payment_request = new AnetAPI\GetHostedPaymentPageRequest();
            $hosted_payment_request->setMerchantAuthentication( $merchant_authentication );
            $hosted_payment_request->setTransactionRequest( $transaction_request );

            foreach ( $settings_list as $setting ) {
                $hosted_payment_request->addToHostedPaymentSettings( $setting );
            }

            $env = $service->get_api_environment();

            if ( defined( 'SP_AUTHNET_ENV' ) && 'PRODUCTION' === strtoupper( (string) SP_AUTHNET_ENV ) ) {
                $env = ANetEnvironment::PRODUCTION;
            }

            $controller = new AnetController\GetHostedPaymentPageController( $hosted_payment_request );
            $response   = $controller->executeWithApiResponse( $env );

            if ( null === $response ) {
                return new WP_REST_Response(
                    array(
                        'ok'    => false,
                        'error' => __( 'Authorize.Net returned a null response.', 'teqcidb' ),
                    ),
                    502
                );
            }

            $result_code = $response->getMessages() ? $response->getMessages()->getResultCode() : null;

            if ( 'Ok' === $result_code && $response->getToken() ) {
                return new WP_REST_Response(
                    array(
                        'ok'    => true,
                        'token' => $response->getToken(),
                    ),
                    200
                );
            }

            $errors = array();
            if ( $response->getMessages() && $response->getMessages()->getMessage() ) {
                foreach ( $response->getMessages()->getMessage() as $msg ) {
                    $errors[] = $msg->getCode() . ': ' . $msg->getText();
                }
            }

            $error_text = ! empty( $errors ) ? implode( ' | ', $errors ) : __( 'Unknown error from Authorize.Net.', 'teqcidb' );

            return new WP_REST_Response(
                array(
                    'ok'    => false,
                    'error' => $error_text,
                ),
                400
            );

        } catch ( Throwable $e ) {
            return new WP_REST_Response(
                array(
                    'ok'    => false,
                    /* translators: %s: exception message from Authorize.Net SDK request execution. */
                    'error' => sprintf( __( 'Exception: %s', 'teqcidb' ), $e->getMessage() ),
                ),
                500
            );
        }
    }
}

TEQCIDB_Accept_Hosted_Token::init();
