<?php
/**
 * Shortcode for displaying the student dashboard auth section.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Shortcode_Student_Dashboard {

    const SHORTCODE_TAG = 'teqcidb_student_dashboard_shortcode';

    public function register() {
        add_shortcode( self::SHORTCODE_TAG, array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function render( $atts = array(), $content = '' ) {
        if ( is_user_logged_in() ) {
            $is_representative = false;
            $current_user      = wp_get_current_user();
            $student_row       = array();

            if ( $current_user instanceof WP_User && $current_user->exists() ) {
                global $wpdb;

                $table_name = $wpdb->prefix . 'teqcidb_students';
                $like       = $wpdb->esc_like( $table_name );
                $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

                if ( $found === $table_name ) {
                    $student_row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM $table_name WHERE wpuserid = %d LIMIT 1",
                            $current_user->ID
                        ),
                        ARRAY_A
                    );

                    if ( ! is_array( $student_row ) ) {
                        $student_row = array();
                    }

                    $is_representative = ! empty( $student_row['is_a_representative'] );
                }
            }

            $tabs = array(
                'profile-info' => array(
                    'label' => esc_html_x( 'Profile Info', 'Student dashboard tab label', 'teqcidb' ),
                ),
                'class-history' => array(
                    'label' => esc_html_x( 'Class History', 'Student dashboard tab label', 'teqcidb' ),
                ),
                'certificates-dates' => array(
                    'label' => esc_html_x( 'Certificates & Important Dates', 'Student dashboard tab label', 'teqcidb' ),
                ),
                'payment-history' => array(
                    'label' => esc_html_x( 'Payment History', 'Student dashboard tab label', 'teqcidb' ),
                ),
            );

            if ( $is_representative ) {
                $tabs['your-students'] = array(
                    'label' => esc_html_x( 'Your Students', 'Student dashboard tab label', 'teqcidb' ),
                );
                $tabs['register-students'] = array(
                    'label' => esc_html_x( 'Register Students & Pay Online', 'Student dashboard tab label', 'teqcidb' ),
                );
            }

            $address = $this->decode_student_address_field(
                isset( $student_row['student_address'] ) ? $student_row['student_address'] : ''
            );
            $representative = $this->decode_representative_field(
                isset( $student_row['their_representative'] ) ? $student_row['their_representative'] : ''
            );
            $associations = $this->decode_list_field(
                isset( $student_row['associations'] ) ? $student_row['associations'] : ''
            );
            $old_companies = $this->decode_list_field(
                isset( $student_row['old_companies'] ) ? $student_row['old_companies'] : ''
            );
            $profile = array(
                'first_name' => isset( $student_row['first_name'] ) ? sanitize_text_field( (string) $student_row['first_name'] ) : '',
                'last_name'  => isset( $student_row['last_name'] ) ? sanitize_text_field( (string) $student_row['last_name'] ) : '',
                'company'    => isset( $student_row['company'] ) ? sanitize_text_field( (string) $student_row['company'] ) : '',
                'phone_cell' => isset( $student_row['phone_cell'] ) ? sanitize_text_field( (string) $student_row['phone_cell'] ) : '',
                'phone_office' => isset( $student_row['phone_office'] ) ? sanitize_text_field( (string) $student_row['phone_office'] ) : '',
                'email'      => isset( $student_row['email'] ) ? sanitize_email( (string) $student_row['email'] ) : '',
                'address_street_1' => $address['street_1'],
                'address_street_2' => $address['street_2'],
                'address_city' => $address['city'],
                'address_state' => $address['state'],
                'address_postal_code' => $address['postal_code'],
                'representative_first_name' => $representative['first_name'],
                'representative_last_name' => $representative['last_name'],
                'representative_email' => $representative['email'],
                'representative_phone' => $representative['phone'],
            );
            $qci_number = isset( $student_row['qcinumber'] ) ? sanitize_text_field( (string) $student_row['qcinumber'] ) : '';
            $association_options = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );
            $student_history_entries = $this->get_student_history_entries( $current_user->ID );
            $expiration_date_raw = isset( $student_row['expiration_date'] )
                ? sanitize_text_field( (string) $student_row['expiration_date'] )
                : '';
            $expiration_timestamp = $expiration_date_raw ? strtotime( $expiration_date_raw ) : false;
            $expiration_date_display = $expiration_timestamp
                ? wp_date( get_option( 'date_format' ), $expiration_timestamp )
                : '';
            $expiration_date_iso = $expiration_timestamp ? wp_date( 'c', $expiration_timestamp ) : '';
            $expiration_date_card = $expiration_timestamp ? wp_date( 'm-d-Y', $expiration_timestamp ) : '';
            $initial_training_date_raw = isset( $student_row['initial_training_date'] )
                ? sanitize_text_field( (string) $student_row['initial_training_date'] )
                : '';
            $initial_training_timestamp = $initial_training_date_raw ? strtotime( $initial_training_date_raw ) : false;
            $initial_training_date_card = $initial_training_timestamp ? wp_date( 'm-d-Y', $initial_training_timestamp ) : '';
            $last_refresher_date_raw = isset( $student_row['last_refresher_date'] )
                ? sanitize_text_field( (string) $student_row['last_refresher_date'] )
                : '';
            $last_refresher_timestamp = $last_refresher_date_raw ? strtotime( $last_refresher_date_raw ) : false;
            $last_refresher_date_card = $last_refresher_timestamp ? wp_date( 'm-d-Y', $last_refresher_timestamp ) : '';

            $states = array(
                'Alabama',
                'Alaska',
                'Arizona',
                'Arkansas',
                'California',
                'Colorado',
                'Connecticut',
                'Delaware',
                'Florida',
                'Georgia',
                'Hawaii',
                'Idaho',
                'Illinois',
                'Indiana',
                'Iowa',
                'Kansas',
                'Kentucky',
                'Louisiana',
                'Maine',
                'Maryland',
                'Massachusetts',
                'Michigan',
                'Minnesota',
                'Mississippi',
                'Missouri',
                'Montana',
                'Nebraska',
                'Nevada',
                'New Hampshire',
                'New Jersey',
                'New Mexico',
                'New York',
                'North Carolina',
                'North Dakota',
                'Ohio',
                'Oklahoma',
                'Oregon',
                'Pennsylvania',
                'Rhode Island',
                'South Carolina',
                'South Dakota',
                'Tennessee',
                'Texas',
                'Utah',
                'Vermont',
                'Virginia',
                'Washington',
                'West Virginia',
                'Wisconsin',
                'Wyoming',
            );

            ob_start();
            ?>
            <section class="teqcidb-dashboard" data-teqcidb-dashboard="true">
                <div class="teqcidb-dashboard-inner">
                    <div class="teqcidb-dashboard-welcome">
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: 1: student first name, 2: QCI number. */
                                __( 'Welcome to your QCI Dashboard, %1$s! Your QCI Number is %2$s', 'teqcidb' ),
                                $profile['first_name'],
                                $qci_number
                            )
                        );
                        ?>
                    </div>
                    <div class="teqcidb-dashboard-layout">
                        <nav
                            class="teqcidb-dashboard-tabs"
                            role="tablist"
                            aria-label="<?php echo esc_attr_x( 'Student dashboard navigation', 'Student dashboard tab list label', 'teqcidb' ); ?>"
                        >
                            <?php
                            $is_first_tab = true;
                            foreach ( $tabs as $tab_key => $tab_data ) :
                                $tab_id = 'teqcidb-dashboard-tab-' . $tab_key;
                                $panel_id = 'teqcidb-dashboard-panel-' . $tab_key;
                                ?>
                                <button
                                    class="teqcidb-dashboard-tab<?php echo $is_first_tab ? ' is-active' : ''; ?>"
                                    type="button"
                                    role="tab"
                                    id="<?php echo esc_attr( $tab_id ); ?>"
                                    aria-selected="<?php echo $is_first_tab ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                                    tabindex="<?php echo $is_first_tab ? '0' : '-1'; ?>"
                                    data-teqcidb-tab="<?php echo esc_attr( $tab_key ); ?>"
                                >
                                    <?php echo esc_html( $tab_data['label'] ); ?>
                                </button>
                                <?php
                                $is_first_tab = false;
                            endforeach;
                            ?>
                        </nav>

                        <div class="teqcidb-dashboard-panels">
                            <?php
                            $is_first_panel = true;
                            foreach ( $tabs as $tab_key => $tab_data ) :
                                $tab_id = 'teqcidb-dashboard-tab-' . $tab_key;
                                $panel_id = 'teqcidb-dashboard-panel-' . $tab_key;
                                ?>
                                <div
                                    class="teqcidb-dashboard-panel<?php echo $is_first_panel ? ' is-active' : ''; ?>"
                                    role="tabpanel"
                                    id="<?php echo esc_attr( $panel_id ); ?>"
                                    aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
                                    <?php echo $is_first_panel ? '' : 'hidden'; ?>
                                >
                                    <?php if ( 'profile-info' === $tab_key ) : ?>
                                        <div class="teqcidb-dashboard-section">
                                            <div class="teqcidb-dashboard-section-header">
                                                <h2 class="teqcidb-dashboard-section-title">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Your Profile Info',
                                                        'Student dashboard profile info heading',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </h2>
                                                <p class="teqcidb-dashboard-section-description">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Below is your profile info, including info about yourself and your Representative, if you have one. All information below can be edited if needed.',
                                                        'Student dashboard profile info description',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            </div>
                                            <form class="teqcidb-profile-form" data-teqcidb-profile-form>
                                            <div class="teqcidb-form-grid">
                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-first-name">
                                                        <?php echo esc_html_x( 'First Name', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-first-name"
                                                        name="teqcidb_profile_first_name"
                                                        value="<?php echo esc_attr( $profile['first_name'] ); ?>"
                                                        autocomplete="given-name"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-last-name">
                                                        <?php echo esc_html_x( 'Last Name', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-last-name"
                                                        name="teqcidb_profile_last_name"
                                                        value="<?php echo esc_attr( $profile['last_name'] ); ?>"
                                                        autocomplete="family-name"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-company">
                                                        <?php echo esc_html_x( 'Company', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-company"
                                                        name="teqcidb_profile_company"
                                                        value="<?php echo esc_attr( $profile['company'] ); ?>"
                                                        autocomplete="organization"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-cell-phone">
                                                        <?php echo esc_html_x( 'Cell Phone', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="tel"
                                                        id="teqcidb-profile-cell-phone"
                                                        name="teqcidb_profile_cell_phone"
                                                        value="<?php echo esc_attr( $profile['phone_cell'] ); ?>"
                                                        autocomplete="tel"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-office-phone">
                                                        <?php echo esc_html_x( 'Office Phone', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="tel"
                                                        id="teqcidb-profile-office-phone"
                                                        name="teqcidb_profile_office_phone"
                                                        value="<?php echo esc_attr( $profile['phone_office'] ); ?>"
                                                        autocomplete="tel"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-email">
                                                        <?php echo esc_html_x( 'Email', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="email"
                                                        id="teqcidb-profile-email"
                                                        name="teqcidb_profile_email"
                                                        value="<?php echo esc_attr( $profile['email'] ); ?>"
                                                        autocomplete="email"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-street-address">
                                                        <?php echo esc_html_x( 'Street Address', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-street-address"
                                                        name="teqcidb_profile_street_address"
                                                        value="<?php echo esc_attr( $profile['address_street_1'] ); ?>"
                                                        autocomplete="street-address"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-street-address-2">
                                                        <?php echo esc_html_x( 'Address Line 2', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-street-address-2"
                                                        name="teqcidb_profile_street_address_2"
                                                        value="<?php echo esc_attr( $profile['address_street_2'] ); ?>"
                                                        autocomplete="address-line2"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-city">
                                                        <?php echo esc_html_x( 'City', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-city"
                                                        name="teqcidb_profile_city"
                                                        value="<?php echo esc_attr( $profile['address_city'] ); ?>"
                                                        autocomplete="address-level2"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-state">
                                                        <?php echo esc_html_x( 'State', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <select
                                                        id="teqcidb-profile-state"
                                                        name="teqcidb_profile_state"
                                                        autocomplete="address-level1"
                                                        disabled
                                                    >
                                                        <option value="">
                                                            <?php echo esc_html_x( 'Select a state', 'Profile form state placeholder option', 'teqcidb' ); ?>
                                                        </option>
                                                        <?php foreach ( $states as $state ) : ?>
                                                            <option value="<?php echo esc_attr( $state ); ?>" <?php selected( $profile['address_state'], $state ); ?>>
                                                                <?php echo esc_html( $state ); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-zip">
                                                        <?php echo esc_html_x( 'Zip Code', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-zip"
                                                        name="teqcidb_profile_zip"
                                                        value="<?php echo esc_attr( $profile['address_postal_code'] ); ?>"
                                                        autocomplete="postal-code"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-rep-first-name">
                                                        <?php echo esc_html_x( 'Representative/Alternate Contact First Name', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-rep-first-name"
                                                        name="teqcidb_profile_rep_first_name"
                                                        value="<?php echo esc_attr( $profile['representative_first_name'] ); ?>"
                                                        autocomplete="given-name"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-rep-last-name">
                                                        <?php echo esc_html_x( 'Representative/Alternate Contact Last Name', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="teqcidb-profile-rep-last-name"
                                                        name="teqcidb_profile_rep_last_name"
                                                        value="<?php echo esc_attr( $profile['representative_last_name'] ); ?>"
                                                        autocomplete="family-name"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-rep-email">
                                                        <?php echo esc_html_x( 'Representative/Alternate Contact Email', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="email"
                                                        id="teqcidb-profile-rep-email"
                                                        name="teqcidb_profile_rep_email"
                                                        value="<?php echo esc_attr( $profile['representative_email'] ); ?>"
                                                        autocomplete="email"
                                                        disabled
                                                    />
                                                </div>

                                                <div class="teqcidb-form-field">
                                                    <label for="teqcidb-profile-rep-phone">
                                                        <?php echo esc_html_x( 'Representative/Alternate Contact Phone', 'Profile form field label', 'teqcidb' ); ?>
                                                    </label>
                                                    <input
                                                        type="tel"
                                                        id="teqcidb-profile-rep-phone"
                                                        name="teqcidb_profile_rep_phone"
                                                        value="<?php echo esc_attr( $profile['representative_phone'] ); ?>"
                                                        autocomplete="tel"
                                                        disabled
                                                    />
                                                </div>
                                            </div>

                                            <fieldset class="teqcidb-form-fieldset teqcidb-profile-old-companies">
                                                <legend>
                                                    <?php
                                                    echo esc_html_x(
                                                        'Previous Companies',
                                                        'Profile form old companies legend',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </legend>
                                                <?php if ( empty( $old_companies ) ) : ?>
                                                    <p class="teqcidb-dashboard-empty">
                                                        <?php
                                                        echo esc_html_x(
                                                            'No previous companies.',
                                                            'Profile form old companies empty state',
                                                            'teqcidb'
                                                        );
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div
                                                    class="teqcidb-form-grid"
                                                    data-teqcidb-old-companies
                                                    data-old-company-count="<?php echo esc_attr( count( $old_companies ) ); ?>"
                                                >
                                                    <?php foreach ( array_values( $old_companies ) as $index => $old_company ) : ?>
                                                        <div class="teqcidb-form-field">
                                                            <label class="screen-reader-text" for="<?php echo esc_attr( 'teqcidb-profile-old-company-' . ( $index + 1 ) ); ?>">
                                                                <?php
                                                                echo esc_html(
                                                                    sprintf(
                                                                        /* translators: %d is the previous company number. */
                                                                        _x(
                                                                            'Previous Company %d',
                                                                            'Profile form old company field label',
                                                                            'teqcidb'
                                                                        ),
                                                                        $index + 1
                                                                    )
                                                                );
                                                                ?>
                                                            </label>
                                                            <input
                                                                type="text"
                                                                id="<?php echo esc_attr( 'teqcidb-profile-old-company-' . ( $index + 1 ) ); ?>"
                                                                name="teqcidb_profile_old_companies[]"
                                                                value="<?php echo esc_attr( $old_company ); ?>"
                                                                autocomplete="organization"
                                                                disabled
                                                            />
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <button
                                                    class="teqcidb-button teqcidb-button-secondary teqcidb-profile-old-companies-add"
                                                    type="button"
                                                    data-teqcidb-add-old-company
                                                    disabled
                                                >
                                                    <?php echo esc_html_x( 'Add a Previous Company', 'Profile form old companies add button', 'teqcidb' ); ?>
                                                </button>
                                            </fieldset>

                                            <fieldset class="teqcidb-form-fieldset teqcidb-profile-associations">
                                                <legend>
                                                    <?php echo esc_html_x( 'Affiliated Associations', 'Profile form associations legend', 'teqcidb' ); ?>
                                                </legend>
                                                <div class="teqcidb-checkbox-grid">
                                                    <?php foreach ( $association_options as $association ) : ?>
                                                        <?php
                                                        $field_id = 'teqcidb-profile-association-' . strtolower( $association );
                                                        $is_checked = in_array( $association, $associations, true );
                                                        ?>
                                                        <label class="teqcidb-checkbox" for="<?php echo esc_attr( $field_id ); ?>">
                                                            <input
                                                                type="checkbox"
                                                                id="<?php echo esc_attr( $field_id ); ?>"
                                                                name="teqcidb_profile_associations[]"
                                                                value="<?php echo esc_attr( $association ); ?>"
                                                                <?php checked( $is_checked ); ?>
                                                                disabled
                                                            />
                                                            <span><?php echo esc_html( $association ); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </fieldset>

                                            <div class="teqcidb-profile-actions">
                                                <button
                                                    class="teqcidb-button teqcidb-button-primary"
                                                    type="button"
                                                    data-teqcidb-profile-edit
                                                >
                                                    <?php echo esc_html_x( 'Edit Profile Info', 'Profile form edit button label', 'teqcidb' ); ?>
                                                </button>
                                                <button
                                                    class="teqcidb-button teqcidb-button-secondary"
                                                    type="button"
                                                    data-teqcidb-profile-save
                                                    disabled
                                                >
                                                    <?php echo esc_html_x( 'Save Profile Info', 'Profile form save button label', 'teqcidb' ); ?>
                                                </button>
                                                <div class="teqcidb-form-feedback" aria-live="polite">
                                                    <span class="teqcidb-spinner" aria-hidden="true"></span>
                                                    <span class="teqcidb-form-message"></span>
                                                </div>
                                            </div>
                                            </form>
                                        </div>
                                    <?php elseif ( 'certificates-dates' === $tab_key ) : ?>
                                        <div class="teqcidb-dashboard-section">
                                            <div class="teqcidb-dashboard-section-header">
                                                <h2 class="teqcidb-dashboard-section-title">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Certificates & Important Dates',
                                                        'Student dashboard certificates tab heading',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </h2>
                                                <p class="teqcidb-dashboard-section-description">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Review your QCI expiration date and any important renewal reminders below.',
                                                        'Student dashboard certificates tab description',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            </div>

                                            <?php if ( ! $expiration_timestamp ) : ?>
                                                <p class="teqcidb-dashboard-empty">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Your expiration date is not available yet.',
                                                        'Student dashboard certificates tab empty state',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            <?php else : ?>
                                                <?php
                                                $wallet_card_data = array(
                                                    'name' => trim( $profile['first_name'] . ' ' . $profile['last_name'] ),
                                                    'company' => $profile['company'],
                                                    'qci_number' => $qci_number,
                                                    'address_line_1' => $profile['address_street_1'],
                                                    'address_line_2' => trim(
                                                        $profile['address_city']
                                                        . ( $profile['address_state'] ? ', ' . $profile['address_state'] : '' )
                                                        . ( $profile['address_postal_code'] ? ' ' . $profile['address_postal_code'] : '' )
                                                    ),
                                                    'phone' => $profile['phone_cell'] ? $profile['phone_cell'] : $profile['phone_office'],
                                                    'email' => $profile['email'],
                                                    'expiration_date' => $expiration_date_card,
                                                    'initial_training_date' => $initial_training_date_card,
                                                    'last_refresher_date' => $last_refresher_date_card,
                                                );
                                                ?>
                                                <div
                                                    class="teqcidb-countdown"
                                                    data-teqcidb-countdown
                                                    data-teqcidb-countdown-target="<?php echo esc_attr( $expiration_date_iso ); ?>"
                                                    data-teqcidb-countdown-warning-days="45"
                                                    data-teqcidb-wallet-card="<?php echo esc_attr( wp_json_encode( $wallet_card_data ) ); ?>"
                                                >
                                                    <div class="teqcidb-countdown-meta">
                                                        <p class="teqcidb-countdown-label">
                                                            <?php
                                                            echo esc_html_x(
                                                                'QCI Expiration Date',
                                                                'Student dashboard certificates expiration label',
                                                                'teqcidb'
                                                            );
                                                            ?>
                                                        </p>
                                                        <p class="teqcidb-countdown-date">
                                                            <?php echo esc_html( $expiration_date_display ); ?>
                                                        </p>
                                                    </div>
                                                    <p class="teqcidb-countdown-timer" data-teqcidb-countdown-timer aria-live="polite">
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="months"></span>
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="weeks"></span>
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="days"></span>
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="hours"></span>
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="minutes"></span>
                                                        <span class="teqcidb-countdown-unit" data-teqcidb-countdown-unit="seconds"></span>
                                                    </p>
                                                    <p class="teqcidb-countdown-expired" data-teqcidb-countdown-expired hidden>
                                                        <?php
                                                        echo esc_html_x(
                                                            'You\'re currently expired and required to take the Initial QCI Course once again.',
                                                            'Student dashboard certificates expiration message',
                                                            'teqcidb'
                                                        );
                                                        ?>
                                                    </p>
                                                    <div class="teqcidb-wallet-card-actions" role="group" aria-label="<?php echo esc_attr_x( 'Wallet card actions', 'Student dashboard wallet card actions label', 'teqcidb' ); ?>">
                                                        <button class="teqcidb-button teqcidb-button-secondary" type="button" data-teqcidb-wallet-card-action="print">
                                                            <?php echo esc_html_x( 'Print Wallet Card', 'Student dashboard wallet card print button label', 'teqcidb' ); ?>
                                                        </button>
                                                        <button class="teqcidb-button teqcidb-button-primary" type="button" data-teqcidb-wallet-card-action="download">
                                                            <?php echo esc_html_x( 'Download Wallet Card', 'Student dashboard wallet card download button label', 'teqcidb' ); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ( 'class-history' === $tab_key ) : ?>
                                        <div class="teqcidb-dashboard-section">
                                            <div class="teqcidb-dashboard-section-header">
                                                <h2 class="teqcidb-dashboard-section-title">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Your Class History',
                                                        'Student dashboard class history heading',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </h2>
                                                <p class="teqcidb-dashboard-section-description">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Below are all of the classes you\'re registered for, including past and upcoming classes.',
                                                        'Student dashboard class history description',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            </div>

                                            <?php if ( empty( $student_history_entries ) ) : ?>
                                                <p class="teqcidb-dashboard-empty">
                                                    <?php
                                                    echo esc_html_x(
                                                        'No class history entries are available yet.',
                                                        'Student dashboard class history empty state',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            <?php else : ?>
                                                <div class="teqcidb-class-history-list">
                                                    <?php foreach ( $student_history_entries as $history_entry ) : ?>
                                                        <article class="teqcidb-class-history-card">
                                                            <div class="teqcidb-class-history-card-header">
                                                                <div>
                                                                    <p class="teqcidb-class-history-eyebrow">
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Class Name',
                                                                            'Student dashboard class history class name label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </p>
                                                                    <h3 class="teqcidb-class-history-title">
                                                                        <?php echo $this->format_history_display_value( $history_entry['classname'] ); ?>
                                                                    </h3>
                                                                </div>
                                                                <div class="teqcidb-class-history-date">
                                                                    <span class="teqcidb-class-history-eyebrow">
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Enrollment Date',
                                                                            'Student dashboard class history enrollment date label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </span>
                                                                    <span class="teqcidb-class-history-value">
                                                                        <?php echo $this->format_history_display_value( $history_entry['enrollmentdate'] ); ?>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <dl class="teqcidb-class-history-meta">
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Registered?',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['registered'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Attended?',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['attended'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Outcome',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['outcome'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Payment Status',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['paymentstatus'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Amount Paid',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['amountpaid'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Registered By',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['registeredby'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Course in Progress?',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['courseinprogress'] ); ?></dd>
                                                                </div>
                                                                <div class="teqcidb-class-history-meta-item">
                                                                    <dt>
                                                                        <?php
                                                                        echo esc_html_x(
                                                                            'Quiz Progress',
                                                                            'Student dashboard class history field label',
                                                                            'teqcidb'
                                                                        );
                                                                        ?>
                                                                    </dt>
                                                                    <dd><?php echo $this->format_history_display_value( $history_entry['quizinprogress'] ); ?></dd>
                                                                </div>
                                                            </dl>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ( 'your-students' === $tab_key ) : ?>
                                        <div class="teqcidb-dashboard-section teqcidb-dashboard-section--students">
                                            <div class="teqcidb-dashboard-section-header">
                                                <h2 class="teqcidb-dashboard-section-title">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Manage Your Students',
                                                        'Student dashboard your students tab heading',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </h2>
                                                <p class="teqcidb-dashboard-section-description">
                                                    <?php
                                                    echo esc_html_x(
                                                        'Below you can manage the students you\'re responsible for, to include adding new students, removing students, and editing their information.',
                                                        'Student dashboard your students tab description',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            </div>

                                            <div class="teqcidb-student-search">
                                                <form class="teqcidb-student-search-form" data-teqcidb-student-search>
                                                    <div class="teqcidb-student-search-fields">
                                                        <div class="teqcidb-form-field">
                                                            <label for="teqcidb-student-search-name">
                                                                <?php
                                                                echo esc_html_x(
                                                                    'Student Name',
                                                                    'Student dashboard student search name label',
                                                                    'teqcidb'
                                                                );
                                                                ?>
                                                            </label>
                                                            <input
                                                                type="text"
                                                                id="teqcidb-student-search-name"
                                                                name="student_name"
                                                                placeholder="<?php echo esc_attr_x( 'Search by name', 'Student dashboard student search name placeholder', 'teqcidb' ); ?>"
                                                            />
                                                        </div>
                                                        <div class="teqcidb-form-field">
                                                            <label for="teqcidb-student-search-email">
                                                                <?php
                                                                echo esc_html_x(
                                                                    'Email Address',
                                                                    'Student dashboard student search email label',
                                                                    'teqcidb'
                                                                );
                                                                ?>
                                                            </label>
                                                            <input
                                                                type="email"
                                                                id="teqcidb-student-search-email"
                                                                name="student_email"
                                                                placeholder="<?php echo esc_attr_x( 'Search by email', 'Student dashboard student search email placeholder', 'teqcidb' ); ?>"
                                                            />
                                                        </div>
                                                        <div class="teqcidb-form-field">
                                                            <label for="teqcidb-student-search-company">
                                                                <?php
                                                                echo esc_html_x(
                                                                    'Company',
                                                                    'Student dashboard student search company label',
                                                                    'teqcidb'
                                                                );
                                                                ?>
                                                            </label>
                                                            <input
                                                                type="text"
                                                                id="teqcidb-student-search-company"
                                                                name="student_company"
                                                                placeholder="<?php echo esc_attr_x( 'Search by company', 'Student dashboard student search company placeholder', 'teqcidb' ); ?>"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="teqcidb-student-search-actions">
                                                        <button class="teqcidb-button teqcidb-button-primary" type="submit">
                                                            <?php echo esc_html_x( 'Search', 'Student dashboard student search button label', 'teqcidb' ); ?>
                                                        </button>
                                                        <button
                                                            class="teqcidb-button teqcidb-button-secondary"
                                                            type="button"
                                                            data-teqcidb-student-search-clear
                                                        >
                                                            <?php echo esc_html_x( 'Clear Search', 'Student dashboard student search clear button label', 'teqcidb' ); ?>
                                                        </button>
                                                        <div class="teqcidb-form-feedback" aria-live="polite">
                                                            <span class="teqcidb-spinner" aria-hidden="true"></span>
                                                            <span class="teqcidb-form-message"></span>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <div class="teqcidb-student-results" data-teqcidb-student-results aria-hidden="true">
                                                <div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="student-dashboard-students">
                                                    <table class="teqcidb-accordion-table">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--placeholder-1">
                                                                    <?php echo esc_html_x( 'Name', 'Student dashboard student search table column label', 'teqcidb' ); ?>
                                                                </th>
                                                                <th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--placeholder-2">
                                                                    <?php echo esc_html_x( 'Email', 'Student dashboard student search table column label', 'teqcidb' ); ?>
                                                                </th>
                                                                <th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--placeholder-3">
                                                                    <?php echo esc_html_x( 'Company', 'Student dashboard student search table column label', 'teqcidb' ); ?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody data-teqcidb-student-list></tbody>
                                                    </table>
                                                </div>
                                                <p class="teqcidb-dashboard-empty" data-teqcidb-student-empty hidden>
                                                    <?php
                                                    echo esc_html_x(
                                                        'Search for students to view their details.',
                                                        'Student dashboard student search empty state',
                                                        'teqcidb'
                                                    );
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <p class="teqcidb-dashboard-placeholder">
                                            <?php
                                            echo esc_html_x(
                                                'Content coming soon.',
                                                'Student dashboard placeholder text',
                                                'teqcidb'
                                            );
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $is_first_panel = false;
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php

            return ob_get_clean();
        }

        ob_start();
        ?>
        <section class="teqcidb-auth-section" data-teqcidb-dashboard="true">
            <div class="teqcidb-auth-grid">
                <article class="teqcidb-auth-card">
                    <h2 class="teqcidb-auth-title">
                        <?php
                        echo esc_html_x(
                            'Already a registered QCI Student or Alternate Contact/Representative? Log in below!',
                            'Login form headline',
                            'teqcidb'
                        );
                        ?>
                    </h2>
                    <p class="teqcidb-auth-description">
                        <?php
                        echo esc_html_x(
                            'Log in below to view your profile information, including important QCI dates & deadlines, your QCI Certificate, Wallet Cards, and more!',
                            'Login form description text',
                            'teqcidb'
                        );
                        ?>
                    </p>

                    <form class="teqcidb-login-form" method="post" action="">
                        <div class="teqcidb-form-field">
                            <label for="teqcidb-login-username">
                                <?php
                                echo esc_html_x(
                                    'Username or Email Address',
                                    'Login form field label',
                                    'teqcidb'
                                );
                                ?>
                            </label>
                            <input
                                type="text"
                                id="teqcidb-login-username"
                                name="teqcidb_login_username"
                                autocomplete="username"
                                placeholder="<?php echo esc_attr_x( 'Your username or email', 'Login form field placeholder', 'teqcidb' ); ?>"
                            />
                        </div>

                        <div class="teqcidb-form-field">
                            <label for="teqcidb-login-password">
                                <?php
                                echo esc_html_x(
                                    'Password',
                                    'Login form field label',
                                    'teqcidb'
                                );
                                ?>
                            </label>
                            <input
                                type="password"
                                id="teqcidb-login-password"
                                name="teqcidb_login_password"
                                autocomplete="current-password"
                                placeholder="<?php echo esc_attr_x( 'Your password', 'Login form field placeholder', 'teqcidb' ); ?>"
                            />
                        </div>

                        <div class="teqcidb-form-field teqcidb-form-checkbox">
                            <label for="teqcidb-login-remember">
                                <input
                                    type="checkbox"
                                    id="teqcidb-login-remember"
                                    name="teqcidb_login_remember"
                                />
                                <span>
                                    <?php
                                    echo esc_html_x(
                                        'Remember me',
                                        'Login form checkbox label',
                                        'teqcidb'
                                    );
                                    ?>
                                </span>
                            </label>
                        </div>

                        <button class="teqcidb-button teqcidb-button-primary" type="submit">
                            <?php
                            echo esc_html_x(
                                'Log In',
                                'Login form submit button label',
                                'teqcidb'
                            );
                            ?>
                        </button>
                        <div class="teqcidb-form-feedback" aria-live="polite">
                            <span class="teqcidb-spinner" aria-hidden="true"></span>
                            <span class="teqcidb-form-message"></span>
                        </div>

                        <a class="teqcidb-auth-link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
                            <?php
                            echo esc_html_x(
                                'Forgot your password? Reset it here!',
                                'Login form password reset link label',
                                'teqcidb'
                            );
                            ?>
                        </a>
                    </form>
                </article>

                <article class="teqcidb-auth-card">
                    <h2 class="teqcidb-auth-title">
                        <?php
                        echo esc_html_x(
                            'Don\'t Have a QCI Account Yet? Create One Below!',
                            'Create account form headline',
                            'teqcidb'
                        );
                        ?>
                    </h2>
                    <p class="teqcidb-auth-description">
                        <?php
                        echo esc_html_x(
                            'Create your QCI account by completing the form below.',
                            'Create account form description text',
                            'teqcidb'
                        );
                        ?>
                    </p>

                    <form class="teqcidb-create-form" method="post" action="">
                        <div class="teqcidb-form-grid">
                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-first-name">
                                    <?php
                                    echo esc_html_x(
                                        'First Name',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-first-name"
                                    name="teqcidb_create_first_name"
                                    autocomplete="given-name"
                                    placeholder="<?php echo esc_attr_x( 'Your first name', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-last-name">
                                    <?php
                                    echo esc_html_x(
                                        'Last Name',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-last-name"
                                    name="teqcidb_create_last_name"
                                    autocomplete="family-name"
                                    placeholder="<?php echo esc_attr_x( 'Your last name', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-company">
                                    <?php
                                    echo esc_html_x(
                                        'Company',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-company"
                                    name="teqcidb_create_company"
                                    autocomplete="organization"
                                    placeholder="<?php echo esc_attr_x( 'Your company', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-cell-phone">
                                    <?php
                                    echo esc_html_x(
                                        'Cell Phone',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="tel"
                                    id="teqcidb-create-cell-phone"
                                    name="teqcidb_create_cell_phone"
                                    autocomplete="tel"
                                    placeholder="<?php echo esc_attr_x( 'Your cell phone', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-email">
                                    <?php
                                    echo esc_html_x(
                                        'Email',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="email"
                                    id="teqcidb-create-email"
                                    name="teqcidb_create_email"
                                    autocomplete="email"
                                    placeholder="<?php echo esc_attr_x( 'Your email address', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-verify-email">
                                    <?php
                                    echo esc_html_x(
                                        'Verify Email',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="email"
                                    id="teqcidb-create-verify-email"
                                    name="teqcidb_create_verify_email"
                                    autocomplete="email"
                                    placeholder="<?php echo esc_attr_x( 'Verify your email address', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-password">
                                    <?php
                                    echo esc_html_x(
                                        'Password',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <div class="teqcidb-password-input">
                                    <input
                                        type="password"
                                        id="teqcidb-create-password"
                                        name="teqcidb_create_password"
                                        autocomplete="new-password"
                                        placeholder="<?php echo esc_attr_x( 'Create a strong password', 'Create account field placeholder', 'teqcidb' ); ?>"
                                    />
                                    <button
                                        class="teqcidb-password-toggle"
                                        type="button"
                                        data-teqcidb-toggle-target="teqcidb-create-password"
                                        aria-pressed="false"
                                        aria-label="<?php echo esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ); ?>"
                                        title="<?php echo esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ); ?>"
                                    >
                                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                        <span class="screen-reader-text">
                                            <?php echo esc_html_x( 'Show', 'Password field toggle button text', 'teqcidb' ); ?>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-verify-password">
                                    <?php
                                    echo esc_html_x(
                                        'Verify Password',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <div class="teqcidb-password-input">
                                    <input
                                        type="password"
                                        id="teqcidb-create-verify-password"
                                        name="teqcidb_create_verify_password"
                                        autocomplete="new-password"
                                        placeholder="<?php echo esc_attr_x( 'Verify your password', 'Create account field placeholder', 'teqcidb' ); ?>"
                                    />
                                    <button
                                        class="teqcidb-password-toggle"
                                        type="button"
                                        data-teqcidb-toggle-target="teqcidb-create-verify-password"
                                        aria-pressed="false"
                                        aria-label="<?php echo esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ); ?>"
                                        title="<?php echo esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ); ?>"
                                    >
                                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                        <span class="screen-reader-text">
                                            <?php echo esc_html_x( 'Show', 'Password field toggle button text', 'teqcidb' ); ?>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-office-phone">
                                    <?php
                                    echo esc_html_x(
                                        'Office Phone',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="tel"
                                    id="teqcidb-create-office-phone"
                                    name="teqcidb_create_office_phone"
                                    autocomplete="tel"
                                    placeholder="<?php echo esc_attr_x( 'Your office phone', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-street-address">
                                    <?php
                                    echo esc_html_x(
                                        'Street Address',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-street-address"
                                    name="teqcidb_create_street_address"
                                    autocomplete="street-address"
                                    placeholder="<?php echo esc_attr_x( 'Your street address', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-city">
                                    <?php
                                    echo esc_html_x(
                                        'City',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-city"
                                    name="teqcidb_create_city"
                                    autocomplete="address-level2"
                                    placeholder="<?php echo esc_attr_x( 'Your city', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-state">
                                    <?php
                                    echo esc_html_x(
                                        'State',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <select id="teqcidb-create-state" name="teqcidb_create_state" autocomplete="address-level1" required>
                                    <option value="">
                                        <?php
                                        echo esc_html_x(
                                            'Select a state',
                                            'Create account state placeholder option',
                                            'teqcidb'
                                        );
                                        ?>
                                    </option>
                                    <option value="Alabama"><?php echo esc_html_x( 'Alabama', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Alaska"><?php echo esc_html_x( 'Alaska', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Arizona"><?php echo esc_html_x( 'Arizona', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Arkansas"><?php echo esc_html_x( 'Arkansas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="California"><?php echo esc_html_x( 'California', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Colorado"><?php echo esc_html_x( 'Colorado', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Connecticut"><?php echo esc_html_x( 'Connecticut', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Delaware"><?php echo esc_html_x( 'Delaware', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Florida"><?php echo esc_html_x( 'Florida', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Georgia"><?php echo esc_html_x( 'Georgia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Hawaii"><?php echo esc_html_x( 'Hawaii', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Idaho"><?php echo esc_html_x( 'Idaho', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Illinois"><?php echo esc_html_x( 'Illinois', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Indiana"><?php echo esc_html_x( 'Indiana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Iowa"><?php echo esc_html_x( 'Iowa', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Kansas"><?php echo esc_html_x( 'Kansas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Kentucky"><?php echo esc_html_x( 'Kentucky', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Louisiana"><?php echo esc_html_x( 'Louisiana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Maine"><?php echo esc_html_x( 'Maine', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Maryland"><?php echo esc_html_x( 'Maryland', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Massachusetts"><?php echo esc_html_x( 'Massachusetts', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Michigan"><?php echo esc_html_x( 'Michigan', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Minnesota"><?php echo esc_html_x( 'Minnesota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Mississippi"><?php echo esc_html_x( 'Mississippi', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Missouri"><?php echo esc_html_x( 'Missouri', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Montana"><?php echo esc_html_x( 'Montana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Nebraska"><?php echo esc_html_x( 'Nebraska', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Nevada"><?php echo esc_html_x( 'Nevada', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="New Hampshire"><?php echo esc_html_x( 'New Hampshire', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="New Jersey"><?php echo esc_html_x( 'New Jersey', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="New Mexico"><?php echo esc_html_x( 'New Mexico', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="New York"><?php echo esc_html_x( 'New York', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="North Carolina"><?php echo esc_html_x( 'North Carolina', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="North Dakota"><?php echo esc_html_x( 'North Dakota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Ohio"><?php echo esc_html_x( 'Ohio', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Oklahoma"><?php echo esc_html_x( 'Oklahoma', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Oregon"><?php echo esc_html_x( 'Oregon', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Pennsylvania"><?php echo esc_html_x( 'Pennsylvania', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Rhode Island"><?php echo esc_html_x( 'Rhode Island', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="South Carolina"><?php echo esc_html_x( 'South Carolina', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="South Dakota"><?php echo esc_html_x( 'South Dakota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Tennessee"><?php echo esc_html_x( 'Tennessee', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Texas"><?php echo esc_html_x( 'Texas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Utah"><?php echo esc_html_x( 'Utah', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Vermont"><?php echo esc_html_x( 'Vermont', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Virginia"><?php echo esc_html_x( 'Virginia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Washington"><?php echo esc_html_x( 'Washington', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="West Virginia"><?php echo esc_html_x( 'West Virginia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Wisconsin"><?php echo esc_html_x( 'Wisconsin', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="Wyoming"><?php echo esc_html_x( 'Wyoming', 'State option label', 'teqcidb' ); ?></option>
                                </select>
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-zip">
                                    <?php
                                    echo esc_html_x(
                                        'Zip Code',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-zip"
                                    name="teqcidb_create_zip"
                                    autocomplete="postal-code"
                                    placeholder="<?php echo esc_attr_x( 'Your zip code', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-rep-first-name">
                                    <?php
                                    echo esc_html_x(
                                        'Representative/Alternate Contact First Name',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-rep-first-name"
                                    name="teqcidb_create_rep_first_name"
                                    autocomplete="given-name"
                                    placeholder="<?php echo esc_attr_x( 'Alternate contact first name', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-rep-last-name">
                                    <?php
                                    echo esc_html_x(
                                        'Representative/Alternate Contact Last Name',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="text"
                                    id="teqcidb-create-rep-last-name"
                                    name="teqcidb_create_rep_last_name"
                                    autocomplete="family-name"
                                    placeholder="<?php echo esc_attr_x( 'Alternate contact last name', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-rep-email">
                                    <?php
                                    echo esc_html_x(
                                        'Representative/Alternate Contact Email',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="email"
                                    id="teqcidb-create-rep-email"
                                    name="teqcidb_create_rep_email"
                                    autocomplete="email"
                                    placeholder="<?php echo esc_attr_x( 'Alternate contact email', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>

                            <div class="teqcidb-form-field">
                                <label for="teqcidb-create-rep-phone">
                                    <?php
                                    echo esc_html_x(
                                        'Representative/Alternate Contact Phone',
                                        'Create account field label',
                                        'teqcidb'
                                    );
                                    ?>
                                </label>
                                <input
                                    type="tel"
                                    id="teqcidb-create-rep-phone"
                                    name="teqcidb_create_rep_phone"
                                    autocomplete="tel"
                                    placeholder="<?php echo esc_attr_x( 'Alternate contact phone', 'Create account field placeholder', 'teqcidb' ); ?>"
                                />
                            </div>
                        </div>

                        <fieldset class="teqcidb-form-fieldset">
                            <legend>
                                <?php
                                echo esc_html_x(
                                    'Affiliated Associations',
                                    'Create account fieldset legend',
                                    'teqcidb'
                                );
                                ?>
                            </legend>
                            <div class="teqcidb-checkbox-grid">
                                <?php
                                $associations = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );
                                foreach ( $associations as $association ) :
                                    $field_id = 'teqcidb-association-' . strtolower( $association );
                                    ?>
                                    <label class="teqcidb-checkbox" for="<?php echo esc_attr( $field_id ); ?>">
                                        <input
                                            type="checkbox"
                                            id="<?php echo esc_attr( $field_id ); ?>"
                                            name="teqcidb_create_associations[]"
                                            value="<?php echo esc_attr( $association ); ?>"
                                        />
                                        <span><?php echo esc_html( $association ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>

                        <button class="teqcidb-button teqcidb-button-primary" type="submit">
                            <?php
                            echo esc_html_x(
                                'Create Account',
                                'Create account form submit button label',
                                'teqcidb'
                            );
                            ?>
                        </button>
                        <div class="teqcidb-form-feedback" aria-live="polite">
                            <span class="teqcidb-spinner" aria-hidden="true"></span>
                            <span class="teqcidb-form-message"></span>
                        </div>
                    </form>
                </article>
            </div>
        </section>
        <?php

        return ob_get_clean();
    }

    private function get_student_dashboard_fields() {
        return array(
            array(
                'key'   => 'first_name',
                'label' => __( 'First Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'last_name',
                'label' => __( 'Last Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'email',
                'label' => __( 'Email Address', 'teqcidb' ),
            ),
            array(
                'key'   => 'company',
                'label' => __( 'Current Company', 'teqcidb' ),
            ),
            array(
                'key'   => 'old_companies',
                'label' => __( 'Previous Companies', 'teqcidb' ),
            ),
            array(
                'key'   => 'student_address_street_1',
                'label' => __( 'Address Line 1', 'teqcidb' ),
            ),
            array(
                'key'   => 'student_address_street_2',
                'label' => __( 'Address Line 2', 'teqcidb' ),
            ),
            array(
                'key'   => 'student_address_city',
                'label' => __( 'City', 'teqcidb' ),
            ),
            array(
                'key'   => 'student_address_state',
                'label' => __( 'State', 'teqcidb' ),
            ),
            array(
                'key'   => 'student_address_postal_code',
                'label' => __( 'Zip Code', 'teqcidb' ),
            ),
            array(
                'key'   => 'phone_cell',
                'label' => __( 'Cell Phone', 'teqcidb' ),
            ),
            array(
                'key'   => 'phone_office',
                'label' => __( 'Office Phone', 'teqcidb' ),
            ),
            array(
                'key'   => 'fax',
                'label' => __( 'Fax', 'teqcidb' ),
            ),
            array(
                'key'   => 'initial_training_date',
                'label' => __( 'Initial Training Date', 'teqcidb' ),
            ),
            array(
                'key'   => 'last_refresher_date',
                'label' => __( 'Last Refresher Date', 'teqcidb' ),
            ),
            array(
                'key'   => 'is_a_representative',
                'label' => __( 'Is this Student also a Representative?', 'teqcidb' ),
            ),
            array(
                'key'   => 'representative_first_name',
                'label' => __( 'Representative First Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'representative_last_name',
                'label' => __( 'Representative Last Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'representative_email',
                'label' => __( 'Representative Email', 'teqcidb' ),
            ),
            array(
                'key'   => 'representative_phone',
                'label' => __( 'Representative Phone', 'teqcidb' ),
            ),
            array(
                'key'   => 'associations',
                'label' => __( 'Associations', 'teqcidb' ),
            ),
            array(
                'key'   => 'expiration_date',
                'label' => __( 'Expiration Date', 'teqcidb' ),
            ),
            array(
                'key'   => 'qcinumber',
                'label' => __( 'QCI Number', 'teqcidb' ),
            ),
            array(
                'key'   => 'comments',
                'label' => __( 'Admin Comments', 'teqcidb' ),
            ),
        );
    }

    private function get_student_dashboard_history_fields() {
        return array(
            array(
                'key'   => 'classname',
                'label' => __( 'Class Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'classdate',
                'label' => __( 'Class Date', 'teqcidb' ),
            ),
            array(
                'key'   => 'classtype',
                'label' => __( 'Class Type', 'teqcidb' ),
            ),
            array(
                'key'   => 'registered',
                'label' => __( 'Registered?', 'teqcidb' ),
            ),
            array(
                'key'   => 'adminapproved',
                'label' => __( 'Admin Approved?', 'teqcidb' ),
            ),
            array(
                'key'   => 'attended',
                'label' => __( 'Attended This Class?', 'teqcidb' ),
            ),
            array(
                'key'   => 'outcome',
                'label' => __( 'Class Outcome', 'teqcidb' ),
            ),
            array(
                'key'   => 'paymentstatus',
                'label' => __( 'Payment Status', 'teqcidb' ),
            ),
            array(
                'key'   => 'amountpaid',
                'label' => __( 'Amount Paid', 'teqcidb' ),
            ),
            array(
                'key'   => 'enrollmentdate',
                'label' => __( 'Enrollment Date', 'teqcidb' ),
            ),
            array(
                'key'   => 'courseinprogress',
                'label' => __( 'Course In Progress?', 'teqcidb' ),
            ),
            array(
                'key'   => 'quizinprogress',
                'label' => __( 'Quiz In Progress?', 'teqcidb' ),
            ),
        );
    }

    public function enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }

        global $post;

        if ( ! $post instanceof WP_Post ) {
            return;
        }

        if ( has_shortcode( $post->post_content, self::SHORTCODE_TAG ) ) {
            wp_enqueue_style(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/student-dashboard.css',
                array(),
                TEQCIDB_VERSION
            );
            wp_enqueue_style( 'dashicons' );
            wp_enqueue_script(
                'teqcidb-jspdf',
                TEQCIDB_PLUGIN_URL . 'assets/js/vendor/jspdf.umd.min.js',
                array(),
                TEQCIDB_VERSION,
                true
            );
            wp_enqueue_script(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/student-dashboard.js',
                array( 'password-strength-meter', 'teqcidb-jspdf' ),
                TEQCIDB_VERSION,
                true
            );
            wp_localize_script(
                'teqcidb-shortcode-student-dashboard',
                'teqcidbStudentDashboard',
                array(
                    'toggleShowLabel' => esc_html_x( 'Show', 'Password field toggle button text', 'teqcidb' ),
                    'toggleHideLabel' => esc_html_x( 'Hide', 'Password field toggle button text', 'teqcidb' ),
                    'toggleShowAria'  => esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ),
                    'toggleHideAria'  => esc_attr_x( 'Hide password', 'Password field toggle button label', 'teqcidb' ),
                    'ajaxUrl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
                    'ajaxNonce'       => wp_create_nonce( 'teqcidb_ajax_nonce' ),
                    'ajaxAction'      => 'teqcidb_save_student',
                    'ajaxLoginAction' => 'teqcidb_login_user',
                    'messageRequired' => esc_html_x( 'Please complete all required fields.', 'Create account form validation message', 'teqcidb' ),
                    'messageEmail'    => esc_html_x( 'The email addresses do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messagePassword' => esc_html_x( 'The passwords do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messageStrength' => esc_html_x( 'Your password must be at least 12 characters long and include uppercase and lowercase letters, a number, and a symbol.', 'Create account form validation message', 'teqcidb' ),
                    'messageUnknown'  => esc_html_x( 'Something went wrong while creating the account. Please try again.', 'Create account form validation message', 'teqcidb' ),
                    'messageLoginRequired' => esc_html_x( 'Please enter your username/email and password.', 'Login form validation message', 'teqcidb' ),
                    'messageLoginFailed' => esc_html_x( 'We could not log you in with those credentials. Please try again.', 'Login form validation message', 'teqcidb' ),
                    'profileEditLabel' => esc_html_x( 'Edit Profile Info', 'Profile form edit button label', 'teqcidb' ),
                    'profileCancelLabel' => esc_html_x( 'Cancel Editing', 'Profile form edit button label', 'teqcidb' ),
                    'profileSaveLabel' => esc_html_x( 'Save Profile Info', 'Profile form save button label', 'teqcidb' ),
                    'profileMessageRequired' => esc_html_x( 'Please complete all required fields.', 'Profile form validation message', 'teqcidb' ),
                    'profileMessageEmailInUse' => esc_html_x( 'Whoops! It looks like that email address is already in use by another user! Please double-check your email address, or use a different one.', 'Profile form validation message', 'teqcidb' ),
                    'profileMessageSaveError' => esc_html_x( 'Unable to save your profile. Please try again.', 'Profile form validation message', 'teqcidb' ),
                    'profileMessageSaved' => esc_html_x( 'Profile saved.', 'Profile form validation message', 'teqcidb' ),
                    'profileUpdateAction' => 'teqcidb_update_profile',
                    'oldCompanyLabel' => esc_html_x( 'Previous Company', 'Profile form old company field label', 'teqcidb' ),
                    'studentSearch' => array(
                        'action' => 'teqcidb_read_student',
                        'perPage' => 50,
                        'summaryFields' => array(
                            'placeholder_1',
                            'placeholder_2',
                            'placeholder_3',
                        ),
                        'detailFields' => $this->get_student_dashboard_fields(),
                        'historyFields' => $this->get_student_dashboard_history_fields(),
                        'assignAction' => 'teqcidb_assign_student_representative',
                        'assignLabel' => esc_html_x( 'Add This Student', 'Student dashboard student search assign button label', 'teqcidb' ),
                        'assignSuccess' => esc_html_x( 'Student added to your roster.', 'Student dashboard student search assign success message', 'teqcidb' ),
                        'assignError' => esc_html_x( 'Unable to add this student right now. Please try again.', 'Student dashboard student search assign error message', 'teqcidb' ),
                        'detailsHeading' => esc_html_x( 'Student Information', 'Student dashboard student search details heading', 'teqcidb' ),
                        'historyHeading' => esc_html_x( 'Student History', 'Student dashboard student search history heading', 'teqcidb' ),
                        /* translators: %s: Student history entry count. */
                        'historyEntryTitle' => esc_html_x( 'History Entry %s', 'Student dashboard student search history entry title', 'teqcidb' ),
                        'historyEmpty' => esc_html_x( 'No student history entries were found.', 'Student dashboard student search history empty state', 'teqcidb' ),
                        'emptyValue' => esc_html_x( 'Not available', 'Student dashboard student search empty value label', 'teqcidb' ),
                        'searchEmpty' => esc_html_x( 'Search for students to view their details.', 'Student dashboard student search empty state', 'teqcidb' ),
                        'searchNoResults' => esc_html_x( 'No matching students were found.', 'Student dashboard student search no results message', 'teqcidb' ),
                        'searchError' => esc_html_x( 'Unable to load students right now. Please try again.', 'Student dashboard student search error message', 'teqcidb' ),
                        'booleanLabels' => array(
                            '1' => esc_html_x( 'Yes', 'Student dashboard student search yes label', 'teqcidb' ),
                            '0' => esc_html_x( 'No', 'Student dashboard student search no label', 'teqcidb' ),
                        ),
                    ),
                    'countdownLabels' => array(
                        'months' => array(
                            'singular' => esc_html_x( 'month', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'months', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                        'days' => array(
                            'singular' => esc_html_x( 'day', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'days', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                        'weeks' => array(
                            'singular' => esc_html_x( 'week', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'weeks', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                        'hours' => array(
                            'singular' => esc_html_x( 'hour', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'hours', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                        'minutes' => array(
                            'singular' => esc_html_x( 'minute', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'minutes', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                        'seconds' => array(
                            'singular' => esc_html_x( 'second', 'Countdown unit singular label', 'teqcidb' ),
                            'plural' => esc_html_x( 'seconds', 'Countdown unit plural label', 'teqcidb' ),
                        ),
                    ),
                    'walletCard' => array(
                        'ademLogoUrl' => esc_url( TEQCIDB_PLUGIN_URL . 'assets/images/te-adem.jpg' ),
                        'thompsonLogoUrl' => esc_url( TEQCIDB_PLUGIN_URL . 'assets/images/te-square-logo.jpg' ),
                        'qualifiedLabel' => esc_html_x( 'Qualified Credentialed Inspector', 'Wallet card qualified label', 'teqcidb' ),
                        'qciNumberLabel' => esc_html_x( 'QCI No.', 'Wallet card QCI number label', 'teqcidb' ),
                        'expirationLabel' => esc_html_x( 'Expiration Date', 'Wallet card expiration label', 'teqcidb' ),
                        'initialTrainingLabel' => esc_html_x( 'Initial Training', 'Wallet card initial training label', 'teqcidb' ),
                        'mostRecentLabel' => esc_html_x( 'Most Recent Annual Update', 'Wallet card most recent update label', 'teqcidb' ),
                        'backTitle' => esc_html_x( 'QCI Important Information', 'Wallet card back title', 'teqcidb' ),
                        'backBullets' => array(
                            esc_html_x(
                                'Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.',
                                'Wallet card back bullet',
                                'teqcidb'
                            ),
                            esc_html_x(
                                'QCIs must recertify if they change employers or if their training provider is no longer certified.',
                                'Wallet card back bullet',
                                'teqcidb'
                            ),
                            esc_html_x(
                                'For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.',
                                'Wallet card back bullet',
                                'teqcidb'
                            ),
                        ),
                        'emptyValue' => esc_html_x( '—', 'Wallet card empty value placeholder', 'teqcidb' ),
                        'downloadFileName' => esc_html_x( 'qci-wallet-card.pdf', 'Wallet card download file name', 'teqcidb' ),
                        'missingPdfMessage' => esc_html_x( 'Unable to generate the wallet card right now. Please try again.', 'Wallet card missing PDF library message', 'teqcidb' ),
                    ),
                )
            );
        }
    }

    private function decode_student_address_field( $value ) {
        $defaults = array(
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
            'zip_code'    => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        if ( is_array( $value ) ) {
            $decoded = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
        }

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        foreach ( array( 'street_1', 'street_2', 'city', 'state', 'postal_code' ) as $key ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        if ( isset( $decoded['zip_code'] ) && is_scalar( $decoded['zip_code'] ) ) {
            $defaults['postal_code'] = sanitize_text_field( (string) $decoded['zip_code'] );
        }

        return $defaults;
    }

    private function decode_representative_field( $value ) {
        $defaults = array(
            'first_name' => '',
            'last_name'  => '',
            'email'      => '',
            'phone'      => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        if ( is_array( $value ) ) {
            $decoded = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
        }

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        foreach ( array( 'first_name', 'last_name' ) as $key ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        if ( isset( $decoded['phone'] ) && is_scalar( $decoded['phone'] ) ) {
            $defaults['phone'] = sanitize_text_field( (string) $decoded['phone'] );
        }

        if ( isset( $decoded['email'] ) ) {
            $email = sanitize_email( $decoded['email'] );
            $defaults['email'] = $email ? $email : '';
        }

        return $defaults;
    }

    private function get_student_history_entries( $user_id ) {
        $user_id = (int) $user_id;
        if ( $user_id <= 0 ) {
            return array();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'teqcidb_studenthistory';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            return array();
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT classname, registered, attended, outcome, paymentstatus, amountpaid, enrollmentdate, registeredby, courseinprogress, quizinprogress, id
                FROM $table_name
                WHERE wpuserid = %d
                ORDER BY enrollmentdate DESC, id DESC",
                $user_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $results ) ) {
            return array();
        }

        $prepared = array();
        foreach ( $results as $entry ) {
            $amount_paid = '';
            if ( isset( $entry['amountpaid'] ) && '' !== $entry['amountpaid'] && null !== $entry['amountpaid'] ) {
                $amount_paid = sprintf(
                    /* translators: %s is the formatted amount. */
                    _x( '$%s', 'Student dashboard amount paid format', 'teqcidb' ),
                    number_format_i18n( (float) $entry['amountpaid'], 2 )
                );
            }

            $enrollment_date = '';
            if ( ! empty( $entry['enrollmentdate'] ) ) {
                $timestamp = strtotime( (string) $entry['enrollmentdate'] );
                if ( false !== $timestamp ) {
                    $enrollment_date = date_i18n( get_option( 'date_format' ), $timestamp );
                }
            }

            $registered_by = $this->format_registered_by( $entry, $user_id );

            $prepared[] = array(
                'classname' => isset( $entry['classname'] ) ? sanitize_text_field( (string) $entry['classname'] ) : '',
                'registered' => isset( $entry['registered'] ) ? sanitize_text_field( (string) $entry['registered'] ) : '',
                'attended' => isset( $entry['attended'] ) ? sanitize_text_field( (string) $entry['attended'] ) : '',
                'outcome' => isset( $entry['outcome'] ) ? sanitize_text_field( (string) $entry['outcome'] ) : '',
                'paymentstatus' => isset( $entry['paymentstatus'] ) ? sanitize_text_field( (string) $entry['paymentstatus'] ) : '',
                'amountpaid' => $amount_paid,
                'enrollmentdate' => $enrollment_date,
                'registeredby' => sanitize_text_field( (string) $registered_by ),
                'courseinprogress' => isset( $entry['courseinprogress'] ) ? sanitize_text_field( (string) $entry['courseinprogress'] ) : '',
                'quizinprogress' => isset( $entry['quizinprogress'] ) ? sanitize_text_field( (string) $entry['quizinprogress'] ) : '',
            );
        }

        return $prepared;
    }

    private function format_registered_by( array $entry, $current_user_id ) {
        $registered_by_id = isset( $entry['registeredby'] ) ? (int) $entry['registeredby'] : 0;
        if ( $registered_by_id <= 0 ) {
            return _x( 'Self-Registered', 'Student dashboard class history registered by label', 'teqcidb' );
        }

        if ( $registered_by_id === (int) $current_user_id ) {
            return _x( 'Self-Registered', 'Student dashboard class history registered by label', 'teqcidb' );
        }

        $registered_user = get_user_by( 'id', $registered_by_id );
        if ( ! $registered_user instanceof WP_User ) {
            return '';
        }

        $first_name = get_user_meta( $registered_by_id, 'first_name', true );
        $last_name  = get_user_meta( $registered_by_id, 'last_name', true );
        $email      = $registered_user->user_email;

        $first_name = is_string( $first_name ) ? $first_name : '';
        $last_name  = is_string( $last_name ) ? $last_name : '';
        $email      = is_string( $email ) ? $email : '';

        $name_parts = array_filter(
            array( $first_name, $last_name ),
            static function ( $value ) {
                return '' !== $value;
            }
        );
        $name = implode( ' ', $name_parts );

        if ( '' === $name && '' === $email ) {
            return '';
        }

        if ( '' === $name ) {
            return sanitize_email( $email );
        }

        if ( '' === $email ) {
            return sanitize_text_field( $name );
        }

        return sprintf(
            /* translators: 1: first and last name, 2: email address. */
            _x( '%1$s (%2$s)', 'Student dashboard class history registered by format', 'teqcidb' ),
            sanitize_text_field( $name ),
            sanitize_email( $email )
        );
    }

    private function format_history_display_value( $value ) {
        if ( '' === $value || null === $value ) {
            return esc_html_x( '—', 'Student dashboard empty field placeholder', 'teqcidb' );
        }

        return esc_html( (string) $value );
    }

    private function decode_list_field( $value ) {
        if ( is_array( $value ) ) {
            $items = $value;
        } else {
            $decoded = json_decode( (string) $value, true );
            $items   = is_array( $decoded ) ? $decoded : array();
        }

        if ( empty( $items ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $normalized = wp_kses_post( (string) $item );

            if ( '' !== $normalized ) {
                $sanitized[] = $normalized;
            }
        }

        return $sanitized;
    }
}
