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
            $association_options = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );

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

                                            <fieldset class="teqcidb-form-fieldset">
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
                                            </div>
                                        </form>
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
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/student-dashboard.js',
                array( 'password-strength-meter' ),
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
