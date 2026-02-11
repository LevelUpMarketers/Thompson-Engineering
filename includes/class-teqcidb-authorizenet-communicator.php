<?php
/**
 * Authorize.Net Accept Hosted iframe communicator endpoint.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TEQCIDB_AuthorizeNet_Communicator {

    /**
     * Public route slug for the communicator endpoint.
     */
    const ROUTE_SLUG = 'anet-iframe-communicator';

    /**
     * Query var used to detect communicator requests.
     */
    const QUERY_VAR = 'teqcidb_anet_iframe_communicator';

    /**
     * Register hooks.
     */
    public function register() {
        add_action( 'init', array( __CLASS__, 'register_rewrite_rule' ) );
        add_filter( 'query_vars', array( $this, 'add_query_var' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_communicator' ) );
    }

    /**
     * Register the pretty permalink rewrite rule.
     */
    public static function register_rewrite_rule() {
        add_rewrite_rule(
            '^' . self::ROUTE_SLUG . '/?$',
            'index.php?' . self::QUERY_VAR . '=1',
            'top'
        );
    }

    /**
     * Add communicator query var.
     *
     * @param array<int, string> $vars Existing query vars.
     *
     * @return array<int, string>
     */
    public function add_query_var( $vars ) {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    /**
     * Render communicator page when endpoint is requested.
     */
    public function maybe_render_communicator() {
        if ( ! get_query_var( self::QUERY_VAR ) ) {
            return;
        }

        nocache_headers();
        header( 'Content-Type: text/html; charset=utf-8' );
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <title><?php echo esc_html_x( 'ANet Communicator', 'Authorize.Net iframe communicator page title', 'teqcidb' ); ?></title>
            <script>
                (function () {
                    function forward(str) {
                        try {
                            if (!str) {
                                return;
                            }

                            if (
                                window.parent &&
                                window.parent.parent &&
                                window.parent.parent.CommunicationHandler &&
                                typeof window.parent.parent.CommunicationHandler.onReceiveCommunication === 'function'
                            ) {
                                window.parent.parent.CommunicationHandler.onReceiveCommunication({
                                    qstr: str,
                                    parent: document.referrer || ''
                                });
                            }
                        } catch (e) {
                            // Intentionally swallow communicator errors.
                        }
                    }

                    window.addEventListener('message', function (event) {
                        if (event && event.data) {
                            forward(event.data);
                        }
                    }, false);

                    if (window.location.hash && window.location.hash.length > 1) {
                        forward(window.location.hash.substring(1));
                    }
                })();
            </script>
        </head>
        <body></body>
        </html>
        <?php
        exit;
    }
}
