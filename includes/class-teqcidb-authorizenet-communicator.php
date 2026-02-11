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
     * Render communicator response when requested.
     */
    public function maybe_render_communicator() {
        $requested = get_query_var( self::QUERY_VAR, '' );

        if ( '' === $requested ) {
            return;
        }

        status_header( 200 );
        nocache_headers();
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        header( 'X-Robots-Tag: noindex, nofollow' );
        header( 'Content-Type: text/html; charset=utf-8' );

        echo '<!doctype html><html><head><meta charset="utf-8"><title>TEQCIDB Authorize.Net Communicator</title></head><body><!-- TEQCIDB Authorize.net communicator --><script>(function(){var queryString=window.location.search.substring(1);if(window.parent&&window.parent.AuthorizeNetIFrame&&typeof window.parent.AuthorizeNetIFrame.onReceiveCommunication==="function"){window.parent.AuthorizeNetIFrame.onReceiveCommunication(queryString);}}());</script></body></html>';
        exit;
    }
}
