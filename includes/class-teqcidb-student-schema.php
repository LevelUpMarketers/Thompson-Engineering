<?php
/**
 * Centralizes the student table schema and form definitions.
 *
 * @package Thompson_Engineering_QCI_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TEQCIDB_Student_Schema {

    const PRIMARY_KEY = 'wpuserid';

    /**
     * Retrieve the full set of student field definitions.
     *
     * @return array
     */
    public static function get_fields() {
        static $fields = null;

        if ( null !== $fields ) {
            return $fields;
        }

        $fields = array(
            self::PRIMARY_KEY => array(
                'label'       => __( 'WordPress User ID', 'teqcidb' ),
                'description' => __( 'Primary key for the student record that ultimately aligns with the related WordPress user.', 'teqcidb' ),
                'db_type'     => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                'db_format'   => '%d',
                'data_type'   => 'integer',
                'editable'    => false,
                'searchable'  => false,
                'summary'     => false,
            ),
            'uniqueidentifier' => array(
                'label'       => __( 'Unique Identifier', 'teqcidb' ),
                'description' => __( 'Custom identifier (slug, username, etc.) collected during registration.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable'  => true,
            ),
            'uniqueidentifierintended' => array(
                'label'       => __( 'Identifier Purpose', 'teqcidb' ),
                'description' => __( 'Notes that describe what the unique identifier represents (email, phone, etc.).', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_email' => array(
                'label'       => __( 'Student Email Address', 'teqcidb' ),
                'description' => __( 'Email entered on the front-end registration workflow.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'email',
                'form'        => array(
                    'type' => 'email',
                ),
                'searchable'    => true,
                'summary'       => true,
                'summary_order' => 2,
            ),
            'student_password' => array(
                'label'       => __( 'Student Password (Hashed)', 'teqcidb' ),
                'description' => __( 'Hashed password string stored for support staff who may need to reset access.', 'teqcidb' ),
                'db_type'     => "varchar(255) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'password',
                ),
            ),
            'student_password_plaintext' => array(
                'label'       => __( 'Student Password (Plain Text)', 'teqcidb' ),
                'description' => __( 'Optional column for safely storing the password text before hashing when required by admins.', 'teqcidb' ),
                'db_type'     => 'longtext',
                'db_format'   => '%s',
                'data_type'   => 'text',
                'form'        => array(
                    'type'       => 'textarea',
                    'full_width' => true,
                ),
            ),
            'student_first' => array(
                'label'       => __( 'Student First Name', 'teqcidb' ),
                'description' => __( 'First name collected from the registration form.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable'    => true,
                'summary'       => true,
                'summary_order' => 0,
            ),
            'student_last' => array(
                'label'       => __( 'Student Last Name', 'teqcidb' ),
                'description' => __( 'Last name collected from the registration form.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable'    => true,
                'summary'       => true,
                'summary_order' => 1,
            ),
            'student_class' => array(
                'label'       => __( 'Student Class', 'teqcidb' ),
                'description' => __( 'General class area or track that the student is associated with.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_age' => array(
                'label'       => __( 'Student Age', 'teqcidb' ),
                'description' => __( 'Age entered by the student or guardian during signup.', 'teqcidb' ),
                'db_type'     => 'smallint(5) unsigned NOT NULL DEFAULT 0',
                'db_format'   => '%d',
                'data_type'   => 'integer',
                'form'        => array(
                    'type'  => 'number',
                    'attrs' => 'min="0" step="1"',
                ),
            ),
            'student_gender' => array(
                'label'       => __( 'Student Gender', 'teqcidb' ),
                'description' => __( 'Gender identity or pronouns provided during registration.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_allergies' => array(
                'label'       => __( 'Student Allergies', 'teqcidb' ),
                'description' => __( 'Allergy or sensitivity notes administrators must review.', 'teqcidb' ),
                'db_type'     => 'longtext',
                'db_format'   => '%s',
                'data_type'   => 'text',
                'form'        => array(
                    'type'       => 'textarea',
                    'full_width' => true,
                ),
            ),
            'student_birthday' => array(
                'label'       => __( 'Student Birthday', 'teqcidb' ),
                'description' => __( 'Birth date gathered from the registration form.', 'teqcidb' ),
                'db_type'     => 'date DEFAULT NULL',
                'db_format'   => '%s',
                'data_type'   => 'date',
                'form'        => array(
                    'type' => 'date',
                ),
            ),
            'student_shirt_size' => array(
                'label'       => __( 'Student Shirt Size', 'teqcidb' ),
                'description' => __( 'Preferred shirt size collected for merch or uniforms.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_studentid' => array(
                'label'       => __( 'Student ID', 'teqcidb' ),
                'description' => __( 'Student ID or badge number assigned by administrators.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable' => true,
            ),
            'student_parent_guardian_email' => array(
                'label'       => __( 'Parent or Guardian Email', 'teqcidb' ),
                'description' => __( 'Email address for the parent or guardian associated with the student.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'email',
                'form'        => array(
                    'type' => 'email',
                ),
            ),
            'student_parent_guardian_name' => array(
                'label'       => __( 'Parent or Guardian Name', 'teqcidb' ),
                'description' => __( 'Name of the primary parent or guardian contact.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_parent_guardian_phone' => array(
                'label'       => __( 'Parent or Guardian Phone', 'teqcidb' ),
                'description' => __( 'Phone number for the parent or guardian contact.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_address' => array(
                'label'       => __( 'Student Address', 'teqcidb' ),
                'description' => __( 'Street address provided during signup.', 'teqcidb' ),
                'db_type'     => "varchar(255) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type'       => 'text',
                    'full_width' => true,
                ),
            ),
            'student_city' => array(
                'label'       => __( 'Student City', 'teqcidb' ),
                'description' => __( 'City for the student\'s address.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_state' => array(
                'label'       => __( 'Student State', 'teqcidb' ),
                'description' => __( 'State or province for the student\'s address.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_zip' => array(
                'label'       => __( 'Student ZIP / Postal Code', 'teqcidb' ),
                'description' => __( 'Postal or ZIP code supplied in the registration form.', 'teqcidb' ),
                'db_type'     => "varchar(20) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_studentschool' => array(
                'label'       => __( 'Student School', 'teqcidb' ),
                'description' => __( 'School the student currently attends.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_phone_number' => array(
                'label'       => __( 'Student Phone Number', 'teqcidb' ),
                'description' => __( 'Primary phone number collected for the student.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_phone_type' => array(
                'label'       => __( 'Student Phone Type', 'teqcidb' ),
                'description' => __( 'Phone type (mobile, landline, etc.) chosen by the student.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_phone_type_specifics' => array(
                'label'       => __( 'Student Phone Type Specifics', 'teqcidb' ),
                'description' => __( 'Any additional details about the student phone type selection.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type'       => 'text',
                    'full_width' => true,
                ),
            ),
            'student_ysc_dietary' => array(
                'label'       => __( 'Student Dietary Notes', 'teqcidb' ),
                'description' => __( 'Dietary notes collected via the YSC fields.', 'teqcidb' ),
                'db_type'     => 'longtext',
                'db_format'   => '%s',
                'data_type'   => 'text',
                'form'        => array(
                    'type'       => 'textarea',
                    'full_width' => true,
                ),
            ),
            'student_cultural_background' => array(
                'label'       => __( 'Student Cultural Background', 'teqcidb' ),
                'description' => __( 'Optional entry where the student can describe their cultural background.', 'teqcidb' ),
                'db_type'     => 'longtext',
                'db_format'   => '%s',
                'data_type'   => 'text',
                'form'        => array(
                    'type'       => 'textarea',
                    'full_width' => true,
                ),
            ),
            'student_representative' => array(
                'label'       => __( 'Representative Name', 'teqcidb' ),
                'description' => __( 'Name of the representative authorized to coordinate on behalf of the student.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_rep_relationship' => array(
                'label'       => __( 'Representative Relationship', 'teqcidb' ),
                'description' => __( 'Explains how the representative is tied to the student.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_rep_address' => array(
                'label'       => __( 'Representative Address', 'teqcidb' ),
                'description' => __( 'Mailing address for the representative, if different from the student.', 'teqcidb' ),
                'db_type'     => "varchar(255) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type'       => 'text',
                    'full_width' => true,
                ),
            ),
            'student_rep_phone' => array(
                'label'       => __( 'Representative Phone', 'teqcidb' ),
                'description' => __( 'Primary phone number for the representative.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_rep_phone_type' => array(
                'label'       => __( 'Representative Phone Type', 'teqcidb' ),
                'description' => __( 'Phone type for the representative contact number.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'student_rep_phone_specifics' => array(
                'label'       => __( 'Representative Phone Specifics', 'teqcidb' ),
                'description' => __( 'Further details about the representative phone preference.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type'       => 'text',
                    'full_width' => true,
                ),
            ),
            'student_rep_email' => array(
                'label'       => __( 'Representative Email', 'teqcidb' ),
                'description' => __( 'Email address for the representative contact.', 'teqcidb' ),
                'db_type'     => "varchar(191) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'email',
                'form'        => array(
                    'type' => 'email',
                ),
            ),
            'student_rep_best_contact_method' => array(
                'label'       => __( 'Representative Preferred Contact Method', 'teqcidb' ),
                'description' => __( 'Notes about the best way to reach the representative.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
            ),
            'new_class_signup' => array(
                'label'       => __( 'New Class Signup', 'teqcidb' ),
                'description' => __( 'Class the student most recently requested to join.', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable'    => true,
                'summary'       => true,
                'summary_order' => 4,
            ),
            'current_class_signup' => array(
                'label'       => __( 'Current Class Signup', 'teqcidb' ),
                'description' => __( 'Class the student is currently associated with (waiting list, active enrollment, etc.).', 'teqcidb' ),
                'db_type'     => "varchar(100) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable'    => true,
                'summary'       => true,
                'summary_order' => 3,
            ),
            'new_application_date' => array(
                'label'       => __( 'New Application Date', 'teqcidb' ),
                'description' => __( 'Date the new application was submitted.', 'teqcidb' ),
                'db_type'     => 'date DEFAULT NULL',
                'db_format'   => '%s',
                'data_type'   => 'date',
                'form'        => array(
                    'type' => 'date',
                ),
            ),
            'application_status' => array(
                'label'       => __( 'Application Status', 'teqcidb' ),
                'description' => __( 'Short label describing where the student is in the application process.', 'teqcidb' ),
                'db_type'     => "varchar(50) NOT NULL DEFAULT ''",
                'db_format'   => '%s',
                'data_type'   => 'string',
                'form'        => array(
                    'type' => 'text',
                ),
                'searchable' => true,
            ),
            'expiration_date' => array(
                'label'       => __( 'Expiration Date', 'teqcidb' ),
                'description' => __( 'Optional expiration date for the application or waiting list entry.', 'teqcidb' ),
                'db_type'     => 'date DEFAULT NULL',
                'db_format'   => '%s',
                'data_type'   => 'date',
                'form'        => array(
                    'type' => 'date',
                ),
            ),
            'admin_note' => array(
                'label'       => __( 'Admin Note', 'teqcidb' ),
                'description' => __( 'Internal note for administrators reviewing or updating the record.', 'teqcidb' ),
                'db_type'     => 'longtext',
                'db_format'   => '%s',
                'data_type'   => 'text',
                'form'        => array(
                    'type'       => 'textarea',
                    'full_width' => true,
                ),
            ),
            'created_at' => array(
                'label'       => __( 'Created', 'teqcidb' ),
                'description' => __( 'Timestamp when the record was created.', 'teqcidb' ),
                'db_type'     => 'datetime NOT NULL',
                'db_format'   => '%s',
                'data_type'   => 'datetime',
                'editable'    => false,
                'searchable'  => false,
                'summary'     => false,
            ),
            'updated_at' => array(
                'label'       => __( 'Updated', 'teqcidb' ),
                'description' => __( 'Timestamp for the most recent update.', 'teqcidb' ),
                'db_type'     => 'datetime NOT NULL',
                'db_format'   => '%s',
                'data_type'   => 'datetime',
                'editable'    => false,
                'searchable'  => false,
                'summary'     => false,
            ),
        );

        return $fields;
    }


    /**
     * Primary key accessor.
     *
     * @return string
     */
    public static function get_primary_key() {
        return self::PRIMARY_KEY;
    }

    /**
     * Retrieve only fields that should appear in the admin form.
     *
     * @return array
     */
    public static function get_form_fields() {
        $fields = array();

        foreach ( self::get_fields() as $name => $definition ) {
            if ( empty( $definition['form'] ) ) {
                continue;
            }

            $fields[ $name ] = $definition;
        }

        return $fields;
    }

    /**
     * Fetch column definitions that should appear in the search form.
     *
     * @return array
     */
    public static function get_searchable_fields() {
        $fields = array();

        foreach ( self::get_fields() as $name => $definition ) {
            if ( empty( $definition['searchable'] ) ) {
                continue;
            }

            $fields[] = array(
                'name'  => $name,
                'label' => $definition['label'],
            );
        }

        return $fields;
    }

    /**
     * Fetch the summary columns used in the table list.
     *
     * @return array
     */
    public static function get_summary_fields() {
        $fields = array();

        foreach ( self::get_fields() as $name => $definition ) {
            if ( empty( $definition['summary'] ) ) {
                continue;
            }

            $order = isset( $definition['summary_order'] ) ? (int) $definition['summary_order'] : 99;
            $fields[] = array(
                'name'  => $name,
                'label' => $definition['label'],
                'order' => $order,
            );
        }

        usort(
            $fields,
            function( $a, $b ) {
                return $a['order'] <=> $b['order'];
            }
        );

        return $fields;
    }

    /**
     * Retrieve a single field definition.
     *
     * @param string $name Field key.
     *
     * @return array|null
     */
    public static function get_field( $name ) {
        $fields = self::get_fields();

        return isset( $fields[ $name ] ) ? $fields[ $name ] : null;
    }
}
