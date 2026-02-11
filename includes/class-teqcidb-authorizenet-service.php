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
use net\authorize\api\contract\v1\CustomerDataType;
use net\authorize\api\contract\v1\GetHostedPaymentPageRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\contract\v1\OrderType;
use net\authorize\api\contract\v1\SettingType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\controller\GetHostedPaymentPageController;

class TEQCIDB_AuthorizeNet_Service {

    const OPTION_NAME = 'teqcidb_api_settings';
    const API_KEY     = 'payment_gateway';

    const FIELD_ENVIRONMENT    = 'payment_gateway_environment';
    const FIELD_LOGIN_ID       = 'payment_gateway_login_id';
    const FIELD_TRANSACTION_KEY = 'payment_gateway_transaction_key';

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
     * Create an Authorize.Net Accept Hosted payment token.
     *
     * @param float|int|string $amount        Transaction amount.
     * @param string           $description   Order description.
     * @param string           $invoiceNumber Invoice number.
     * @param string           $customerEmail Optional customer email.
     *
     * @return string|WP_Error
     */
    public function create_accept_hosted_token( $amount, $description, $invoiceNumber, $customerEmail = '' ) {
        if ( ! class_exists( GetHostedPaymentPageRequest::class ) || ! class_exists( GetHostedPaymentPageController::class ) ) {
            return new WP_Error(
                'teqcidb_authorizenet_sdk_missing',
                __( 'Authorize.Net SDK is unavailable. Run Composer install for this plugin.', 'teqcidb' )
            );
        }

        $merchant_auth = $this->get_merchant_authentication();

        if ( is_wp_error( $merchant_auth ) ) {
            return $merchant_auth;
        }

        $transaction_request = new TransactionRequestType();
        $transaction_request->setTransactionType( 'authCaptureTransaction' );
        $transaction_request->setAmount( number_format( (float) $amount, 2, '.', '' ) );

        $order = new OrderType();
        $order->setInvoiceNumber( (string) $invoiceNumber );
        $order->setDescription( (string) $description );
        $transaction_request->setOrder( $order );

        if ( '' !== $customerEmail ) {
            $customer = new CustomerDataType();
            $customer->setEmail( sanitize_email( $customerEmail ) );
            $transaction_request->setCustomer( $customer );
        }

        $communicator_setting = new SettingType();
        $communicator_setting->setSettingName( 'hostedPaymentIFrameCommunicatorUrl' );
        $communicator_setting->setSettingValue(
            wp_json_encode(
                array(
                    'url' => home_url( '/?teqcidb_iframe_communicator=1' ),
                )
            )
        );

        $return_options_setting = new SettingType();
        $return_options_setting->setSettingName( 'hostedPaymentReturnOptions' );
        $return_options_setting->setSettingValue(
            wp_json_encode(
                array(
                    'showReceipt' => false,
                )
            )
        );

        $request = new GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication( $merchant_auth );
        $request->setTransactionRequest( $transaction_request );
        $request->setHostedPaymentSettings( array( $communicator_setting, $return_options_setting ) );

        $controller = new GetHostedPaymentPageController( $request );
        $response   = $controller->executeWithApiResponse( $this->get_api_environment() );

        if ( $response && $response->getToken() ) {
            return $response->getToken();
        }

        $messages = $this->extract_authorizenet_error_messages( $response );

        return new WP_Error(
            'teqcidb_authorizenet_accept_hosted_failed',
            __( 'Unable to create an Authorize.Net Accept Hosted token.', 'teqcidb' ),
            array(
                'errors' => $messages,
            )
        );
    }

    /**
     * Extract API error codes and messages from an Authorize.Net response.
     *
     * @param mixed $response Authorize.Net API response object.
     *
     * @return array<int, array<string, string>>
     */
    private function extract_authorizenet_error_messages( $response ) {
        $messages = array();

        if ( ! $response || ! method_exists( $response, 'getMessages' ) ) {
            return $messages;
        }

        $response_messages = $response->getMessages();

        if ( $response_messages && method_exists( $response_messages, 'getMessage' ) ) {
            foreach ( (array) $response_messages->getMessage() as $message ) {
                if ( is_object( $message ) && method_exists( $message, 'getCode' ) && method_exists( $message, 'getText' ) ) {
                    $messages[] = array(
                        'code'    => (string) $message->getCode(),
                        'message' => (string) $message->getText(),
                    );
                }
            }
        }

        if ( method_exists( $response, 'getTransactionResponse' ) ) {
            $transaction_response = $response->getTransactionResponse();

            if ( $transaction_response && method_exists( $transaction_response, 'getErrors' ) ) {
                foreach ( (array) $transaction_response->getErrors() as $error ) {
                    if ( is_object( $error ) && method_exists( $error, 'getErrorCode' ) && method_exists( $error, 'getErrorText' ) ) {
                        $messages[] = array(
                            'code'    => (string) $error->getErrorCode(),
                            'message' => (string) $error->getErrorText(),
                        );
                    }
                }
            }
        }

        return $messages;
    }
}
