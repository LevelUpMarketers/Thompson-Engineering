<?php
/**
 * Accept Hosted iframe communicator endpoint.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class TEQCIDB_Accept_Hosted_Communicator {

    /**
     * Query var used to detect communicator requests.
     */
    const QUERY_VAR = 'teqcidb_authnet_communicator';

    /**
     * Register hooks.
     *
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rule' ) );
        add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
        add_action( 'template_redirect', array( __CLASS__, 'maybe_render_communicator' ) );
    }

    /**
     * Add the pretty permalink endpoint.
     *
     * @return void
     */
    public static function add_rewrite_rule() {
        add_rewrite_rule(
            '^sp-authnet-communicator/?$',
            'index.php?' . self::QUERY_VAR . '=1',
            'top'
        );
    }

    /**
     * Register custom query var.
     *
     * @param array<int, string> $vars Existing query vars.
     *
     * @return array<int, string>
     */
    public static function register_query_var( $vars ) {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    /**
     * Render communicator page output when route is requested.
     *
     * @return void
     */
    public static function maybe_render_communicator() {
        if ( ! get_query_var( self::QUERY_VAR ) ) {
            return;
        }

        nocache_headers();
        header( 'Content-Type: text/html; charset=utf-8' );

        echo '<!doctype html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="utf-8">';
        echo '<meta name="robots" content="noindex,nofollow">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . esc_html__( 'Authorize.Net Communicator', 'teqcidb' ) . '</title>';
        echo '</head>';
        echo '<body>';
        echo '<script>';
        echo '(function(){';
        echo 'function relayToParent(payload){';
        echo 'try{';
        echo 'if(!payload){return;}';
        echo 'if(typeof payload === "string"){';
        echo 'try{JSON.parse(payload);}catch(e){}';
        echo 'window.parent.postMessage(payload,"*");';
        echo 'return;';
        echo '}';
        echo 'window.parent.postMessage(JSON.stringify(payload),"*");';
        echo '}catch(e){}';
        echo '}';
        echo 'window.addEventListener("message",function(event){relayToParent(event.data);},false);';
        echo '})();';
        echo '</script>';
        echo '</body>';
        echo '</html>';

        exit;
    }
}

TEQCIDB_Accept_Hosted_Communicator::init();
