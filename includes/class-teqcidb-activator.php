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
        $classes_table   = $wpdb->prefix . 'teqcidb_classes';
        $student_history = $wpdb->prefix . 'teqcidb_studenthistory';
        $payment_history = $wpdb->prefix . 'teqcidb_paymenthistory';
        $quizzes_table   = $wpdb->prefix . 'teqcidb_quizzes';
        $questions_table = $wpdb->prefix . 'teqcidb_quiz_questions';
        $attempts_table  = $wpdb->prefix . 'teqcidb_quiz_attempts';
        $answers_table   = $wpdb->prefix . 'teqcidb_quiz_answers';

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

        $sql_classes = "CREATE TABLE $classes_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            uniqueclassid varchar(255) NOT NULL,
            classname varchar(191) NOT NULL DEFAULT '',
            classformat varchar(20) NOT NULL DEFAULT '',
            classtype varchar(20) NOT NULL DEFAULT '',
            classsize int(11) unsigned DEFAULT NULL,
            classregistrantnumber int(11) unsigned NOT NULL DEFAULT 0,
            instructors longtext,
            classsaddress longtext,
            classstartdate date DEFAULT NULL,
            classstarttime time DEFAULT NULL,
            classendtime time DEFAULT NULL,
            classcost decimal(10,2) DEFAULT NULL,
            classdescription longtext,
            classresources longtext,
            teamslink varchar(2048) NOT NULL DEFAULT '',
            classurl varchar(2048) NOT NULL DEFAULT '',
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            classhide tinyint(1) NOT NULL DEFAULT 0,
            allallowedcourse varchar(20) DEFAULT '',
            allallowedquiz varchar(20) DEFAULT '',
            coursestudentsallowed longtext,
            quizstudentsallowed longtext,
            coursestudentsrestricted longtext,
            quizstudentsrestricted longtext,
            PRIMARY KEY  (id),
            UNIQUE KEY uniqueclassid (uniqueclassid),
            KEY classname (classname)
        ) $charset_collate;";

        $sql_student_history = "CREATE TABLE $student_history (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            uniquestudentid varchar(255) NOT NULL,
            wpuserid bigint(20) unsigned DEFAULT NULL,
            classname varchar(191) NOT NULL DEFAULT '',
            uniqueclassid varchar(255) NOT NULL,
            registered varchar(20) NOT NULL DEFAULT 'Pending',
            adminapproved varchar(20) DEFAULT NULL,
            attended varchar(20) NOT NULL DEFAULT 'Upcoming',
            outcome varchar(20) NOT NULL DEFAULT 'Upcoming',
            paymentstatus varchar(20) NOT NULL DEFAULT 'Pending',
            amountpaid decimal(10,2) DEFAULT NULL,
            enrollmentdate date DEFAULT NULL,
            registeredby bigint(20) unsigned DEFAULT NULL,
            courseinprogress varchar(255) NOT NULL DEFAULT 'no',
            quizinprogress varchar(255) NOT NULL DEFAULT 'no',
            PRIMARY KEY  (id),
            KEY uniquestudentid (uniquestudentid),
            KEY wpuserid (wpuserid),
            KEY uniqueclassid (uniqueclassid)
        ) $charset_collate;";

        $sql_payment_history = "CREATE TABLE $payment_history (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wpuserid bigint(20) unsigned DEFAULT NULL,
            uniquestudentid varchar(255) NOT NULL DEFAULT '',
            email varchar(191) NOT NULL DEFAULT '',
            uniqueclassid varchar(255) NOT NULL DEFAULT '',
            totalpaid decimal(10,2) DEFAULT NULL,
            transid varchar(100) NOT NULL DEFAULT '',
            transtime varchar(80) NOT NULL DEFAULT '',
            multiplestudents longtext,
            invoicenumber varchar(50) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY wpuserid (wpuserid),
            KEY uniquestudentid (uniquestudentid),
            KEY uniqueclassid (uniqueclassid),
            KEY transid (transid),
            KEY invoicenumber (invoicenumber)
        ) $charset_collate;";


        $sql_quizzes = "CREATE TABLE $quizzes_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            class_id varchar(255) NOT NULL DEFAULT '',
            public_token varchar(64) NOT NULL,
            name varchar(255) NOT NULL,
            status tinyint NOT NULL DEFAULT 2,
            settings_json longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY public_token (public_token),
            KEY status (status)
        ) $charset_collate;";

        $sql_quiz_questions = "CREATE TABLE $questions_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) unsigned NOT NULL,
            sort_order int NOT NULL DEFAULT 0,
            type varchar(30) NOT NULL,
            prompt longtext NOT NULL,
            choices_json longtext,
            required tinyint(1) NOT NULL DEFAULT 0,
            points int NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY quiz_sort (quiz_id, sort_order),
            KEY quiz_id (quiz_id)
        ) $charset_collate;";

        $sql_quiz_attempts = "CREATE TABLE $attempts_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) unsigned NOT NULL,
            class_id bigint(20) unsigned DEFAULT NULL,
            user_id bigint(20) unsigned NOT NULL,
            status tinyint NOT NULL DEFAULT 2,
            score int DEFAULT NULL,
            started_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            submitted_at datetime DEFAULT NULL,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY quiz_user (quiz_id, user_id),
            KEY class_user (class_id, user_id),
            KEY status (status)
        ) $charset_collate;";

        $sql_quiz_answers = "CREATE TABLE $answers_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            attempt_id bigint(20) unsigned NOT NULL,
            answers_json longtext NOT NULL,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY attempt_id (attempt_id)
        ) $charset_collate;";

        dbDelta( $sql_main );
        dbDelta( $sql_settings );
        dbDelta( $sql_content_log );
        dbDelta( $sql_classes );
        dbDelta( $sql_student_history );
        dbDelta( $sql_payment_history );
        dbDelta( $sql_quizzes );
        dbDelta( $sql_quiz_questions );
        dbDelta( $sql_quiz_attempts );
        dbDelta( $sql_quiz_answers );
        if ( class_exists( 'TEQCIDB_Ajax' ) ) {
            TEQCIDB_Ajax::register_authorizenet_communicator_rewrite();
            TEQCIDB_Ajax::register_class_page_rewrite();
        }

        flush_rewrite_rules();
    }
}
