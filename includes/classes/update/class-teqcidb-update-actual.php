<?php
/**
 * TEQcidbPlugin TEQcidbPlugin_Toplevel_Update_Actual Class
 *
 * @author   Jake Evans
 * @category admin
 * @package  classes/update
 * @version  1.0.0
 */



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Toplevel_Update_Actual', false ) ) :
	/**
	 * TEQcidbPlugin_Toplevel_Update_Actual Class.
	 */
	class TEQcidbPlugin_Toplevel_Update_Actual {

		public $api_url     = '';
		public $api_data    = array();
		public $name        = '';
		public $slug        = '';
		public $version     = '';
		public $wp_override = false;
		public $cache_key   = '';

		public $health_check_timeout = 5;

		/**
		 * Class constructor.
		 *
		 * @uses plugin_basename()
		 * @uses hook()
		 *
		 * @param string $_api_url     The URL pointing to the custom API endpoint.
		 * @param string $_plugin_file Path to the plugin file.
		 * @param array  $_api_data    Optional data to send with API calls.
		 */
		public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'teqcidb_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Make check for active and valid salts.
				$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'teqcidb_settings' );

				if ( false === stripos( $this->extension_settings->repw, 'aod' ) ) {
					return false;
				}

				global $edd_plugin_data;

				$this->api_url     = trailingslashit( $_api_url );
				$this->api_data    = $_api_data;
				$this->name        = plugin_basename( $_plugin_file );
				$this->slug        = basename( $_plugin_file, '.php' );
				$this->version     = $_api_data['version'];
				$this->wp_override = isset( $_api_data['wp_override'] ) ? (bool) $_api_data['wp_override'] : false;
				$this->beta        = ! empty( $this->api_data['beta'] ) ? true : false;
				$this->cache_key   = 'edd_sl_' . md5( serialize( $this->slug . $this->api_data['license'] . $this->beta ) );

				$edd_plugin_data[ $this->slug ] = $this->api_data;

				/**
				 * Fires after the $edd_plugin_data is setup.
				 *
				 * @since x.x.x
				 *
				 * @param array $edd_plugin_data Array of EDD SL plugin data.
				 */
				do_action( 'post_edd_sl_plugin_updater_toplevel_setup', $edd_plugin_data );

				// Set up hooks.
				$this->init();

			}

		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 */
		public function init() {

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
			remove_action( 'after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10 );
			add_action( 'after_plugin_row_' . $this->name, array( $this, 'show_update_notification' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'show_changelog' ) );

		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update API just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 * @return array Modified update array with custom plugin data.
		 */
		public function check_update( $_transient_data ) {

			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass();
			}

			if ( 'plugins.php' === $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $this->name ] ) && false === $this->wp_override ) {
				return $_transient_data;
			}

			$version_info = $this->get_cached_version_info();

			if ( false === $version_info ) {
				$version_info = $this->api_request( 'plugin_latest_version',
					array(
						'slug' => $this->slug,
						'beta' => $this->beta,
					)
				);

				$this->set_version_info_cache( $version_info );

			}

			if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {

				if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

					$_transient_data->response[ $this->name ] = $version_info;

				}

				$_transient_data->last_checked           = time();
				$_transient_data->checked[ $this->name ] = $this->version;

			}

			return $_transient_data;
		}

		/**
		 * Show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
		 *
		 * @param string $file - file variable.
		 * @param array  $plugin - plugin variable.
		 */
		public function show_update_notification( $file, $plugin ) {

			if ( is_network_admin() ) {
				return;
			}

			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			if ( ! is_multisite() ) {
				return;
			}

			if ( $this->name !== $file ) {
				return;
			}

			// Remove our filter on the site transient.
			remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 10 );

			$update_cache = get_site_transient( 'update_plugins' );

			$update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();

			if ( empty( $update_cache->response ) || empty( $update_cache->response[ $this->name ] ) ) {

				$version_info = $this->get_cached_version_info();

				if ( false === $version_info ) {
					$version_info = $this->api_request( 'plugin_latest_version',
						array(
							'slug' => $this->slug,
							'beta' => $this->beta,
						)
					);

					// Since we disabled our filter for the transient, we aren't running our object conversion on banners, sections, or icons. Do this now...
					if ( isset( $version_info->banners ) && ! is_array( $version_info->banners ) ) {
						$version_info->banners = $this->convert_object_to_array( $version_info->banners );
					}

					if ( isset( $version_info->sections ) && ! is_array( $version_info->sections ) ) {
						$version_info->sections = $this->convert_object_to_array( $version_info->sections );
					}

					if ( isset( $version_info->icons ) && ! is_array( $version_info->icons ) ) {
						$version_info->icons = $this->convert_object_to_array( $version_info->icons );
					}

					$this->set_version_info_cache( $version_info );
				}

				if ( ! is_object( $version_info ) ) {
					return;
				}

				if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

					$update_cache->response[ $this->name ] = $version_info;

				}

				$update_cache->last_checked           = time();
				$update_cache->checked[ $this->name ] = $this->version;

				set_site_transient( 'update_plugins', $update_cache );

			} else {

				$version_info = $update_cache->response[ $this->name ];

			}

			// Restore our filter.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

			if ( ! empty( $update_cache->response[ $this->name ] ) && version_compare( $this->version, $version_info->new_version, '<' ) ) {

				// Build a plugin list row, with update notification.
				$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
				echo '<tr class="plugin-update-tr" id="' . $this->slug . '-update" data-slug="' . $this->slug . '" data-plugin="' . $this->slug . '/' . $file . '">';
				echo '<td colspan="3" class="plugin-update colspanchange">';
				echo '<div class="update-message notice inline notice-warning notice-alt">';

				$changelog_link = self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->name . '&slug=' . $this->slug . '&TB_iframe=true&width=772&height=911' );

				if ( empty( $version_info->download_link ) ) {
					printf(
						__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'easy-digital-downloads' ),
						esc_html( $version_info->name ),
						'<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
						esc_html( $version_info->new_version ),
						'</a>'
					);
				} else {
					printf(
						__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.', 'easy-digital-downloads' ),
						esc_html( $version_info->name ),
						'<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
						esc_html( $version_info->new_version ),
						'</a>',
						'<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->name, 'upgrade-plugin_' . $this->name ) ) .'">',
						'</a>'
					);
				}

				do_action( "in_plugin_update_message-{$file}", $plugin, $version_info );

				echo '</div></td></tr>';
			}
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed  $_data - data.
		 * @param string $_action - action.
		 * @param object $_args - argumantes.
		 * @return object $_data - data object.
		 */
		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {

			if ( 'plugin_information' !== $_action ) {

				return $_data;

			}

			if ( ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {

				return $_data;

			}

			$to_send = array(
				'slug'   => $this->slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners' => array(),
					'reviews' => false,
					'icons'   => array(),
				),
			);

			$cache_key = 'edd_api_request_' . md5( serialize( $this->slug . $this->api_data['license'] . $this->beta ) );

			// Get the transient where we store the api request for this plugin for 24 hours.
			$edd_api_request_transient = $this->get_cached_version_info( $cache_key );

			// If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
			if ( empty( $edd_api_request_transient ) ) {

				$api_response = $this->api_request( 'plugin_information', $to_send );

				// Expires in 3 hours.
				$this->set_version_info_cache( $api_response, $cache_key );

				if ( false !== $api_response ) {
					$_data = $api_response;
				}
			} else {
				$_data = $edd_api_request_transient;
			}

			// Convert sections into an associative array, since we're getting an object, but Core expects an array.
			if ( isset( $_data->sections ) && ! is_array( $_data->sections ) ) {
				$_data->sections = $this->convert_object_to_array( $_data->sections );
			}

			// Convert banners into an associative array, since we're getting an object, but Core expects an array.
			if ( isset( $_data->banners ) && ! is_array( $_data->banners ) ) {
				$_data->banners = $this->convert_object_to_array( $_data->banners );
			}

			// Convert icons into an associative array, since we're getting an object, but Core expects an array.
			if ( isset( $_data->icons ) && ! is_array( $_data->icons ) ) {
				$_data->icons = $this->convert_object_to_array( $_data->icons );
			}

			return $_data;
		}

		/**
		 * Convert some objects to arrays when injecting data into the update API
		 *
		 * Some data like sections, banners, and icons are expected to be an associative array, however due to the JSON
		 * decoding, they are objects. This method allows us to pass in the object and return an associative array.
		 *
		 * @since 3.6.5
		 *
		 * @param stdClass $data - data.
		 *
		 * @return array
		 */
		public function convert_object_to_array( $data ) {

			$new_data = array();
			foreach ( $data as $key => $value ) {
				$new_data[ $key ] = $value;
			}

			return $new_data;
		}

		/**
		 * Disable SSL verification in order to prevent download update failures
		 *
		 * @param array  $args - argumantes.
		 * @param string $url - url.
		 * @return object $array - array.
		 */
		public function http_request_args( $args, $url ) {

			$verify_ssl = $this->verify_ssl();
			if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
				$args['sslverify'] = $verify_ssl;
			}
			return $args;

		}

		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 * @param string $_action The requested action.
		 * @param array  $_data   Parameters for the API action.
		 * @return false|object
		 */
		public function api_request( $_action, $_data ) {

			global $wp_version, $edd_plugin_url_available;

			// Do a quick status check on this domain if we haven't already checked it.
			$store_hash = md5( $this->api_url );
			if ( ! is_array( $edd_plugin_url_available ) || ! isset( $edd_plugin_url_available[ $store_hash ] ) ) {
				$test_url_parts = parse_url( $this->api_url );

				$scheme = ! empty( $test_url_parts['scheme'] ) ? $test_url_parts['scheme'] : 'http';
				$host   = ! empty( $test_url_parts['host'] ) ? $test_url_parts['host'] : '';
				$port   = ! empty( $test_url_parts['port'] ) ? ':' . $test_url_parts['port'] : '';

				if ( empty( $host ) ) {
					$edd_plugin_url_available[ $store_hash ] = false;
				} else {

					$test_url = $scheme . '://' . $host . $port;
					$response = wp_remote_get( $test_url,
						array(
							'timeout'   => $this->health_check_timeout,
							'sslverify' => true,
						)
					);

					$edd_plugin_url_available[ $store_hash ] = is_wp_error( $response ) ? false : true;
				}
			}

			if ( false === $edd_plugin_url_available[ $store_hash ] ) {
				return;
			}

			$data = array_merge( $this->api_data, $_data );

			if ( $data['slug'] != $this->slug ) {
				return;
			}

			if ( $this->api_url == trailingslashit ( home_url() ) ) {

				// Don't allow a plugin to ping itself.
				return false;

			}

			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => ! empty( $data['license'] ) ? $data['license'] : '',
				'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
				'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
				'version'    => isset( $data['version'] ) ? $data['version'] : false,
				'slug'       => $data['slug'],
				'author'     => $data['author'],
				'url'        => home_url(),
				'beta'       => ! empty( $data['beta'] ),
			);

			$verify_ssl = $this->verify_ssl();
			$request    = wp_remote_post( $this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => $verify_ssl,
					'body'      => $api_params,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
			}

			if ( $request && isset( $request->sections ) ) {
				$request->sections = maybe_unserialize( $request->sections );
			} else {
				$request = false;
			}

			if ( $request && isset( $request->banners ) ) {
				$request->banners = maybe_unserialize( $request->banners );
			}

			if ( $request && isset( $request->icons ) ) {
				$request->icons = maybe_unserialize( $request->icons );
			}

			if ( ! empty( $request->sections ) ) {
				foreach ( $request->sections as $key => $section ) {
					$request->$key = (array) $section;
				}
			}

			return $request;
		}

		/**
		 * Changelog stuff.
		 */
		public function show_changelog() {

			global $edd_plugin_data;

			if ( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
				return;
			}

			if ( empty( $_REQUEST['plugin'] ) ) {
				return;
			}

			if ( empty( $_REQUEST['slug'] ) ) {
				return;
			}

			if ( ! current_user_can( 'update_plugins' ) ) {
				wp_die( __( 'You do not have permission to install plugin updates', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
			}

			$data         = $edd_plugin_data[ $_REQUEST['slug'] ];
			$beta         = ! empty( $data['beta'] ) ? true : false;
			$cache_key    = md5( 'edd_plugin_' . sanitize_key( $_REQUEST['plugin'] ) . '_' . $beta . '_version_info' );
			$version_info = $this->get_cached_version_info( $cache_key );

			if ( false === $version_info ) {

				$api_params = array(
					'edd_action' => 'get_version',
					'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
					'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
					'slug'       => $_REQUEST['slug'],
					'author'     => $data['author'],
					'url'        => home_url(),
					'beta'       => ! empty( $data['beta'] )
				);

				$verify_ssl = $this->verify_ssl();
				$request    = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => $verify_ssl, 'body' => $api_params ) );

				if ( ! is_wp_error( $request ) ) {
					$version_info = json_decode( wp_remote_retrieve_body( $request ) );
				}

				if ( ! empty( $version_info ) && isset( $version_info->sections ) ) {
					$version_info->sections = maybe_unserialize( $version_info->sections );
				} else {
					$version_info = false;
				}

				if ( ! empty( $version_info ) ) {
					foreach ( $version_info->sections as $key => $section ) {
						$version_info->$key = (array) $section;
					}
				}

				$this->set_version_info_cache( $version_info, $cache_key );

			}

			if ( ! empty( $version_info ) && isset( $version_info->sections['changelog'] ) ) {
				echo '<div style="background:#fff;padding:10px;">' . $version_info->sections['changelog'] . '</div>';
			}

			exit;
		}

		/**
		 * Checking for cached info.
		 *
		 * @param string $cache_key - the key for the cache.
		 */
		public function get_cached_version_info( $cache_key = '' ) {

			if ( empty( $cache_key ) ) {
				$cache_key = $this->cache_key;
			}

			$cache = get_option( $cache_key );

			if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {

				// Cache is expired.
				return false;

			}

			// We need to turn the icons into an array, thanks to WP Core forcing these into an object at some point.
			$cache['value'] = json_decode( $cache['value'] );
			if ( ! empty( $cache['value']->icons ) ) {
				$cache['value']->icons = (array) $cache['value']->icons;
			}

			return $cache['value'];

		}

		/**
		 * Setting some version stuff for the cache.
		 *
		 * @param string $value - the actual cached value.
		 * @param string $cache_key - the key for the cache.
		 */
		public function set_version_info_cache( $value = '', $cache_key = '' ) {

			if ( empty( $cache_key ) ) {
				$cache_key = $this->cache_key;
			}

			$data = array(
				'timeout' => strtotime( '+3 hours', time() ),
				'value'   => json_encode( $value ),
			);

			update_option( $cache_key, $data, 'no' );

		}

		/**
		 * Returns if the SSL of the store should be verified.
		 *
		 * @since  1.6.13
		 * @return bool
		 */
		public function verify_ssl() {

			return (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this );
		}

	}

endif;
