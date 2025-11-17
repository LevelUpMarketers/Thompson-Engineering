<?php
/**
 * Define the internationalization functionality
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_I18n {

    public function load_textdomain() {
        load_plugin_textdomain(
            'teqcidb',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
