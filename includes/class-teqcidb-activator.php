<?php
/**
 * Fired during plugin activation
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Activator {

    public static function activate() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $main_table      = $wpdb->prefix . 'teqcidb_students';
        $settings_table  = $wpdb->prefix . 'teqcidb_settings';
        $content_log     = $wpdb->prefix . 'teqcidb_content_log';

        $sql_main = "CREATE TABLE $main_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wpuserid bigint(20) unsigned DEFAULT NULL,
            uniquestudentid varchar(255) NOT NULL,
            first_name varchar(191) NOT NULL DEFAULT '',
            last_name varchar(191) NOT NULL DEFAULT '',
            company varchar(191) DEFAULT '',
            old_companies longtext,
            student_address longtext,
            phone_cell varchar(50) DEFAULT '',
            phone_office varchar(50) DEFAULT '',
            fax varchar(50) DEFAULT '',
            email varchar(191) NOT NULL,
            initial_training_date date DEFAULT NULL,
            last_refresher_date date DEFAULT NULL,
            is_a_representative tinyint(1) NOT NULL DEFAULT 0,
            their_representative longtext,
            new_class_signup_flag tinyint(1) NOT NULL DEFAULT 0,
            associations text,
            expiration_date date DEFAULT NULL,
            qcinumber varchar(50) DEFAULT '',
            comments longtext,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            UNIQUE KEY uniquestudentid (uniquestudentid),
            KEY wpuserid (wpuserid)
        ) $charset_collate;";

        $sql_settings = "CREATE TABLE $settings_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(191) NOT NULL,
            option_value longtext NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY option_name (option_name)
        ) $charset_collate;";

        $sql_content_log = "CREATE TABLE $content_log (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            post_type varchar(20) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        dbDelta( $sql_main );
        dbDelta( $sql_settings );
        dbDelta( $sql_content_log );
    }
}
