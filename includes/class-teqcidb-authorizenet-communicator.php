<?php
/**
 * Public endpoint that serves the Authorize.Net Accept Hosted iFrame communicator.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TEQCIDB_AuthorizeNet_Communicator {

    /**
     * Query var used to serve the communicator page.
     */
    const QUERY_VAR = 'teqcidb_iframe_communicator';

    /**
     * Register hooks.
     */
    public function register() {
        add_filter( 'query_vars', array( $this, 'register_query_var' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_communicator' ), 0 );
    }

    /**
     * Register communicator query var.
     *
     * @param array $query_vars Existing query vars.
     *
     * @return array
     */
    public function register_query_var( $query_vars ) {
        if ( ! in_array( self::QUERY_VAR, $query_vars, true ) ) {
            $query_vars[] = self::QUERY_VAR;
        }

        return $query_vars;
    }

    /**
     * Log a single debug line for communicator requests when WP_DEBUG is enabled.
     *
     * @return void
     */
    private function maybe_log_debug_request() {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        $origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
        $query_action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';

        error_log(
            sprintf(
                '[TEQCIDB COMMUNICATOR] method=%s origin=%s referer=%s qs_action=%s',
                $method,
                $origin,
                $referer,
                $query_action
            )
        );
    }

    /**
     * Send no-cache/noindex, CORS, and frame embedding headers for communicator responses.
     *
     * @return void
     */
    private function send_communicator_headers() {
        nocache_headers();
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        header( 'X-Robots-Tag: noindex, nofollow' );

        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: GET, OPTIONS' );
        header( 'Access-Control-Allow-Headers: *' );
        header( 'Access-Control-Max-Age: 86400' );

        header_remove( 'X-Frame-Options' );
        header_remove( 'Content-Security-Policy' );
        header_remove( 'Content-Security-Policy-Report-Only' );
        header( 'Content-Security-Policy: frame-ancestors https://accept.authorize.net https://test.authorize.net;' );
    }

    /**
     * Render communicator response when requested.
     */
    public function maybe_render_communicator() {
        $requested = get_query_var( self::QUERY_VAR, '' );

        if ( '' === $requested ) {
            return;
        }

        $request_method = isset( $_SERVER['REQUEST_METHOD'] )
            ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
            : 'get';

        $this->maybe_log_debug_request();
        $this->send_communicator_headers();

        if ( 'options' === $request_method ) {
            status_header( 204 );
            exit;
        }

        status_header( 200 );
        header( 'Content-Type: text/html; charset=utf-8' );

        echo '<!doctype html><html><head><meta charset="utf-8"><title>TEQCIDB Authorize.Net Communicator</title></head><body><!-- TEQCIDB Authorize.net communicator --><script>(function(){var queryString=window.location.search.substring(1);if(window.parent&&window.parent.AuthorizeNetIFrame&&typeof window.parent.AuthorizeNetIFrame.onReceiveCommunication==="function"){window.parent.AuthorizeNetIFrame.onReceiveCommunication(queryString);}}());</script></body></html>';
        exit;
    }
}
