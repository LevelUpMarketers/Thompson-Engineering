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
            wpuserid bigint(20) unsigned NOT NULL DEFAULT 0,
            uuid varchar(36) NOT NULL,
            uniqueidentifier2 varchar(191) NOT NULL DEFAULT '',
            student_id varchar(100) NOT NULL DEFAULT '',
            first_name varchar(191) NOT NULL DEFAULT '',
            last_name varchar(191) NOT NULL DEFAULT '',
            class_name varchar(191) NOT NULL DEFAULT '',
            student_uuid varchar(36) NOT NULL DEFAULT '',
            student_email varchar(191) NOT NULL DEFAULT '',
            student_phone varchar(50) NOT NULL DEFAULT '',
            student_address varchar(255) NOT NULL DEFAULT '',
            student_city varchar(100) NOT NULL DEFAULT '',
            student_state varchar(100) NOT NULL DEFAULT '',
            student_zip varchar(20) NOT NULL DEFAULT '',
            student_country varchar(100) NOT NULL DEFAULT '',
            student_school varchar(191) NOT NULL DEFAULT '',
            student_grade varchar(50) NOT NULL DEFAULT '',
            student_year varchar(50) NOT NULL DEFAULT '',
            student_notes longtext,
            opt_in_marketing_email tinyint(1) NOT NULL DEFAULT 0,
            opt_in_marketing_sms tinyint(1) NOT NULL DEFAULT 0,
            opt_in_event_update_email tinyint(1) NOT NULL DEFAULT 0,
            opt_in_event_update_sms tinyint(1) NOT NULL DEFAULT 0,
            rep_uuid varchar(191) NOT NULL DEFAULT '',
            rep_email varchar(191) NOT NULL DEFAULT '',
            rep_phone varchar(50) NOT NULL DEFAULT '',
            rep_address varchar(255) NOT NULL DEFAULT '',
            rep_city varchar(100) NOT NULL DEFAULT '',
            rep_state varchar(100) NOT NULL DEFAULT '',
            rep_zip varchar(20) NOT NULL DEFAULT '',
            rep_country varchar(100) NOT NULL DEFAULT '',
            rep_role varchar(191) NOT NULL DEFAULT '',
            rep_id bigint(20) unsigned NOT NULL DEFAULT 0,
            rep_association_type varchar(100) NOT NULL DEFAULT '',
            rep_relationship varchar(100) NOT NULL DEFAULT '',
            rep_notes longtext,
            rep_is_primary tinyint(1) NOT NULL DEFAULT 0,
            rep_is_financial tinyint(1) NOT NULL DEFAULT 0,
            rep_is_education tinyint(1) NOT NULL DEFAULT 0,
            rep_guardian_verified tinyint(1) NOT NULL DEFAULT 0,
            rep_guardian_verification_method varchar(191) NOT NULL DEFAULT '',
            rep_guardian_verification_date datetime DEFAULT NULL,
            expires_at datetime DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            deleted_at datetime DEFAULT NULL,
            expiration_date datetime DEFAULT NULL,
            confirmation_date datetime DEFAULT NULL,
            checked_in_date datetime DEFAULT NULL,
            expiration_notes longtext,
            other_notes longtext,
            PRIMARY KEY  (id),
            UNIQUE KEY uuid (uuid),
            UNIQUE KEY student_uuid (student_uuid),
            KEY wpuserid (wpuserid),
            KEY student_id (student_id)
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
