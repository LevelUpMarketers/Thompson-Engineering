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
            return '';
        }

        ob_start();
        ?>
        <section class="teqcidb-auth-section">
            <div class="teqcidb-auth-grid">
                <article class="teqcidb-auth-card">
                    <h2 class="teqcidb-auth-title">
                        <?php
                        echo esc_html_x(
                            'Already Have an Account? Log In Below!',
                            'Login form headline',
                            'teqcidb'
                        );
                        ?>
                    </h2>
                    <p class="teqcidb-auth-description">
                        <?php
                        echo esc_html_x(
                            'Log in to sign up for events, see upcoming events, and check membership status & perks. Don\'t have a membership? Join here!',
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

                        <a class="teqcidb-auth-link" href="#">
                            <?php
                            echo esc_html_x(
                                'Forgot your password?',
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
                            'Don\'t Have an Account? Create One Below!',
                            'Create account form headline',
                            'teqcidb'
                        );
                        ?>
                    </h2>
                    <p class="teqcidb-auth-description">
                        <?php
                        echo esc_html_x(
                            'Create a free account below. Sign up for one of our memberships to gain access to special perks and discounts.',
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
                                <select id="teqcidb-create-state" name="teqcidb_create_state" autocomplete="address-level1">
                                    <option value="">
                                        <?php
                                        echo esc_html_x(
                                            'Select a state',
                                            'Create account state placeholder option',
                                            'teqcidb'
                                        );
                                        ?>
                                    </option>
                                    <option value="AL"><?php echo esc_html_x( 'Alabama', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="AK"><?php echo esc_html_x( 'Alaska', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="AZ"><?php echo esc_html_x( 'Arizona', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="AR"><?php echo esc_html_x( 'Arkansas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="CA"><?php echo esc_html_x( 'California', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="CO"><?php echo esc_html_x( 'Colorado', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="CT"><?php echo esc_html_x( 'Connecticut', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="DE"><?php echo esc_html_x( 'Delaware', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="FL"><?php echo esc_html_x( 'Florida', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="GA"><?php echo esc_html_x( 'Georgia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="HI"><?php echo esc_html_x( 'Hawaii', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="ID"><?php echo esc_html_x( 'Idaho', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="IL"><?php echo esc_html_x( 'Illinois', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="IN"><?php echo esc_html_x( 'Indiana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="IA"><?php echo esc_html_x( 'Iowa', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="KS"><?php echo esc_html_x( 'Kansas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="KY"><?php echo esc_html_x( 'Kentucky', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="LA"><?php echo esc_html_x( 'Louisiana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="ME"><?php echo esc_html_x( 'Maine', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MD"><?php echo esc_html_x( 'Maryland', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MA"><?php echo esc_html_x( 'Massachusetts', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MI"><?php echo esc_html_x( 'Michigan', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MN"><?php echo esc_html_x( 'Minnesota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MS"><?php echo esc_html_x( 'Mississippi', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MO"><?php echo esc_html_x( 'Missouri', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="MT"><?php echo esc_html_x( 'Montana', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NE"><?php echo esc_html_x( 'Nebraska', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NV"><?php echo esc_html_x( 'Nevada', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NH"><?php echo esc_html_x( 'New Hampshire', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NJ"><?php echo esc_html_x( 'New Jersey', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NM"><?php echo esc_html_x( 'New Mexico', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NY"><?php echo esc_html_x( 'New York', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="NC"><?php echo esc_html_x( 'North Carolina', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="ND"><?php echo esc_html_x( 'North Dakota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="OH"><?php echo esc_html_x( 'Ohio', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="OK"><?php echo esc_html_x( 'Oklahoma', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="OR"><?php echo esc_html_x( 'Oregon', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="PA"><?php echo esc_html_x( 'Pennsylvania', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="RI"><?php echo esc_html_x( 'Rhode Island', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="SC"><?php echo esc_html_x( 'South Carolina', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="SD"><?php echo esc_html_x( 'South Dakota', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="TN"><?php echo esc_html_x( 'Tennessee', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="TX"><?php echo esc_html_x( 'Texas', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="UT"><?php echo esc_html_x( 'Utah', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="VT"><?php echo esc_html_x( 'Vermont', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="VA"><?php echo esc_html_x( 'Virginia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="WA"><?php echo esc_html_x( 'Washington', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="WV"><?php echo esc_html_x( 'West Virginia', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="WI"><?php echo esc_html_x( 'Wisconsin', 'State option label', 'teqcidb' ); ?></option>
                                    <option value="WY"><?php echo esc_html_x( 'Wyoming', 'State option label', 'teqcidb' ); ?></option>
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
        }
    }
}
