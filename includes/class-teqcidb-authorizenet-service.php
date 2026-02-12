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
use net\authorize\api\controller\GetHostedPaymentPageController;
use net\authorize\api\contract\v1 as AnetAPI;
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
     * Get the Accept Hosted iframe post URL for the configured environment.
     *
     * @return string
     */
    public function get_accept_hosted_iframe_url() {
        if ( ANetEnvironment::PRODUCTION === $this->get_api_environment() ) {
            return 'https://accept.authorize.net/payment/payment';
        }

        return 'https://test.authorize.net/payment/payment';
    }

    /**
     * Create an Accept Hosted payment page token.
     *
     * @param array<string,mixed> $args Token arguments.
     *
     * @return array<string,string>|WP_Error
     */
    public function create_accept_hosted_token( array $args ) {
        if ( ! class_exists( AnetAPI\GetHostedPaymentPageRequest::class ) || ! class_exists( GetHostedPaymentPageController::class ) ) {
            return new WP_Error(
                'teqcidb_authorizenet_sdk_missing',
                __( 'Authorize.Net SDK is unavailable. Run Composer install for this plugin.', 'teqcidb' )
            );
        }

        $merchant_authentication = $this->get_merchant_authentication();

        if ( is_wp_error( $merchant_authentication ) ) {
            return $merchant_authentication;
        }

        $amount = isset( $args['amount'] ) ? (float) $args['amount'] : 0.0;

        if ( $amount <= 0 ) {
            return new WP_Error(
                'teqcidb_authorizenet_invalid_amount',
                __( 'A valid class cost is required before checkout can begin.', 'teqcidb' )
            );
        }

        $invoice_number = isset( $args['invoice_number'] ) ? sanitize_text_field( (string) $args['invoice_number'] ) : '';
        $description    = isset( $args['description'] ) ? sanitize_text_field( (string) $args['description'] ) : '';
        $first_name     = isset( $args['first_name'] ) ? sanitize_text_field( (string) $args['first_name'] ) : '';
        $last_name      = isset( $args['last_name'] ) ? sanitize_text_field( (string) $args['last_name'] ) : '';
        $email          = isset( $args['email'] ) ? sanitize_email( (string) $args['email'] ) : '';
        $customer_id    = isset( $args['customer_id'] ) ? sanitize_text_field( (string) $args['customer_id'] ) : '';

        $transaction_request = new AnetAPI\TransactionRequestType();
        $transaction_request->setTransactionType( 'authCaptureTransaction' );
        $transaction_request->setAmount( number_format( $amount, 2, '.', '' ) );

        $order = new AnetAPI\OrderType();

        if ( '' !== $invoice_number ) {
            $order->setInvoiceNumber( substr( $invoice_number, 0, 20 ) );
        }

        if ( '' !== $description ) {
            $order->setDescription( substr( $description, 0, 255 ) );
        }

        $transaction_request->setOrder( $order );

        if ( '' !== $first_name || '' !== $last_name ) {
            $customer = new AnetAPI\CustomerAddressType();
            $customer->setFirstName( $first_name );
            $customer->setLastName( $last_name );
            $transaction_request->setBillTo( $customer );
        }

        if ( '' !== $email || '' !== $customer_id ) {
            $customer_data = new AnetAPI\CustomerDataType();

            if ( '' !== $email ) {
                $customer_data->setEmail( $email );
            }

            if ( '' !== $customer_id ) {
                $customer_data->setId( substr( $customer_id, 0, 20 ) );
            }

            $transaction_request->setCustomer( $customer_data );
        }

        $request = new AnetAPI\GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication( $merchant_authentication );
        $request->setTransactionRequest( $transaction_request );

        $hosted_settings = array();

        $hosted_payment_button_options = new AnetAPI\SettingType();
        $hosted_payment_button_options->setSettingName( 'hostedPaymentButtonOptions' );
        $hosted_payment_button_options->setSettingValue( wp_json_encode( array( 'text' => __( 'Pay', 'teqcidb' ) ) ) );
        $hosted_settings[] = $hosted_payment_button_options;

        $hosted_payment_iframe_options = new AnetAPI\SettingType();
        $hosted_payment_iframe_options->setSettingName( 'hostedPaymentIFrameCommunicatorUrl' );

        $communicator_url = class_exists( 'TEQCIDB_Ajax' )
            ? TEQCIDB_Ajax::get_authorizenet_communicator_url()
            : admin_url( 'admin-ajax.php?action=teqcidb_authorizenet_iframe_communicator' );

        $hosted_payment_iframe_options->setSettingValue( wp_json_encode( array( 'url' => esc_url_raw( $communicator_url ) ) ) );
        $hosted_settings[] = $hosted_payment_iframe_options;

        $hosted_payment_return_options = new AnetAPI\SettingType();
        $hosted_payment_return_options->setSettingName( 'hostedPaymentReturnOptions' );
        $hosted_payment_return_options->setSettingValue(
            wp_json_encode(
                array(
                    'showReceipt' => false,
                )
            )
        );
        $hosted_settings[] = $hosted_payment_return_options;

        $request->setHostedPaymentSettings( $hosted_settings );

        $controller = new GetHostedPaymentPageController( $request );

        try {
            $response = $controller->executeWithApiResponse( $this->get_api_environment() );
        } catch ( Exception $exception ) {
            return new WP_Error(
                "teqcidb_authorizenet_request_exception",
                sprintf(
                    /* translators: %s: gateway error details. */
                    __( "Unable to initialize Authorize.Net checkout: %s", "teqcidb" ),
                    sanitize_text_field( $exception->getMessage() )
                )
            );
        }

        if ( ! $response ) {
            return new WP_Error(
                'teqcidb_authorizenet_empty_response',
                __( 'Authorize.Net did not return a response. Please try again.', 'teqcidb' )
            );
        }

        $messages = $response->getMessages();

        if ( $messages && 'Ok' === $messages->getResultCode() ) {
            $token = $response->getToken();

            if ( ! is_string( $token ) || '' === $token ) {
                return new WP_Error(
                    'teqcidb_authorizenet_missing_token',
                    __( 'Authorize.Net did not return a payment token. Please try again.', 'teqcidb' )
                );
            }

            return array(
                'token'      => $token,
                'post_url'   => $this->get_accept_hosted_iframe_url(),
                'environment' => ANetEnvironment::PRODUCTION === $this->get_api_environment() ? 'live' : 'sandbox',
            );
        }

        $error_message = __( 'Unable to initialize Authorize.Net checkout. Please try again.', 'teqcidb' );

        if ( $messages && is_array( $messages->getMessage() ) ) {
            $first_message = reset( $messages->getMessage() );

            if ( $first_message && method_exists( $first_message, 'getText' ) ) {
                $text = sanitize_text_field( (string) $first_message->getText() );

                if ( '' !== $text ) {
                    $error_message = $text;
                }
            }
        }

        return new WP_Error( 'teqcidb_authorizenet_token_error', $error_message );
    }
}
