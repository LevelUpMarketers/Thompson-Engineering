

<?php
/**
 * MedWestHealthPoints_Registration_UI Class that dispalys the login form or the user dashboard - class-teqcidb-dashboard-ui.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQciDb_All_Classes_UI', false ) ) :

	/**
	 * MedWestHealthPoints_Admin_Menu Class.
	 */
	class TEQciDb_All_Classes_UI {


		/**
		 * Class Constructor
		 */
		public function __construct() {

			// For grabbing an image from media library.
			wp_enqueue_media();

			wp_enqueue_script( 'password-strength-meter' );


/*
			// See if we have a currently logged-in user.
			$loggedin = is_user_logged_in();

			// If user is logged in...
			if ( $loggedin ) {
				$this->stitch_logged_in_member_html();
			} else {
				$this->stitch_login_or_register_html();
			}
*/

			$this->stitch_logged_in_member_html();

		}

		/**
		 * Builds and outputs the final HTML for individuals to sign in or register.
		 */
		public function stitch_login_or_register_html() {
			$this->display_login_or_register_form();
		}

		/**
		 * Builds and outputs the final HTML for individuals who are already registered.
		 */
		public function stitch_logged_in_member_html() {
			$this->display_registered_user_dashboard();
		}

		/**
		 * The Login/Register HTML.
		 */
		public function display_login_or_register_form() {

			$args = array(
				'echo'           => false,
				'remember'       => true,
				'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
				'form_id'        => 'teqcidb-dashboard-login-row-wrapper',
				'id_username'    => 'user_login',
				'id_password'    => 'user_pass',
				'id_remember'    => 'rememberme',
				'id_submit'      => 'wp-submit',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in'   => __( 'Log In' ),
				'value_username' => '',
				'value_remember' => false
			);

			$login_register_html = '<div id="teqcidb-dashboard-container">
										<div id="teqcidb-dashboard-login-wrapper">
											<p class="teqcidb-tab-intro-para">' . $this->loginform_text . '</p>' . wp_login_form( $args ) . '
										</div>
										<div class="teqcidb-spinner" id="teqcidb-spinner-login-frontend-spinner">
										</div>
									<div id="teqcidb-not-registered-top-container">
										<p>Not Registered? Sign up below!</p>
										<div class="teqcidb-form-section-wrapper">
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Create a Password</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-password" name="password" type="password" placeholder="Your Password" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Verify Password</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-passwordverify" name="password_retyped" type="password" placeholder="Verify Your Password" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-see-new-user-password" data-visibility="hidden">View Password</button>
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<p style="opacity:0" id="password-strength">Password Strength is...</p>
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">First Name</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-firstname" type="text" placeholder="Your First Name" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Last Name</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-lastname" type="text" placeholder="Your Last Name" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Company</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-company" type="text" placeholder="Your Company" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Cell Phone</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-cellphone" type="text" placeholder="Your Cell Phone" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Office Phone</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-officephone" type="text" placeholder="Users\'s Office Phone" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Email</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-email" type="text" placeholder="Your Email Address" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Street Address</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-streetaddress" type="text" placeholder="Your Street Address" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">City</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-city" type="text" placeholder="Your City" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">State</label>
													<select class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-select" id="teqcidb-user-state" type="text" placeholder="Your State">
														<option value="AL">Alabama</option>
														<option value="AK">Alaska</option>
														<option value="AZ">Arizona</option>
														<option value="AR">Arkansas</option>
														<option value="CA">California</option>
														<option value="CO">Colorado</option>
														<option value="CT">Connecticut</option>
														<option value="DE">Delaware</option>
														<option value="DC">District Of Columbia</option>
														<option value="FL">Florida</option>
														<option value="GA">Georgia</option>
														<option value="HI">Hawaii</option>
														<option value="ID">Idaho</option>
														<option value="IL">Illinois</option>
														<option value="IN">Indiana</option>
														<option value="IA">Iowa</option>
														<option value="KS">Kansas</option>
														<option value="KY">Kentucky</option>
														<option value="LA">Louisiana</option>
														<option value="ME">Maine</option>
														<option value="MD">Maryland</option>
														<option value="MA">Massachusetts</option>
														<option value="MI">Michigan</option>
														<option value="MN">Minnesota</option>
														<option value="MS">Mississippi</option>
														<option value="MO">Missouri</option>
														<option value="MT">Montana</option>
														<option value="NE">Nebraska</option>
														<option value="NV">Nevada</option>
														<option value="NH">New Hampshire</option>
														<option value="NJ">New Jersey</option>
														<option value="NM">New Mexico</option>
														<option value="NY">New York</option>
														<option value="NC">North Carolina</option>
														<option value="ND">North Dakota</option>
														<option value="OH">Ohio</option>
														<option value="OK">Oklahoma</option>
														<option value="OR">Oregon</option>
														<option value="PA">Pennsylvania</option>
														<option value="RI">Rhode Island</option>
														<option value="SC">South Carolina</option>
														<option value="SD">South Dakota</option>
														<option value="TN">Tennessee</option>
														<option value="TX">Texas</option>
														<option value="UT">Utah</option>
														<option value="VT">Vermont</option>
														<option value="VA">Virginia</option>
														<option value="WA">Washington</option>
														<option value="WV">West Virginia</option>
														<option value="WI">Wisconsin</option>
														<option value="WY">Wyoming</option>
													</select>
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Zip Code</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-zip" type="text" placeholder="Your Zip Code" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper-checkboxes">
													<label class="teqcidb-form-section-fields-label">Associations</label>
													<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-aapa" type="checkbox" ' . $aapa . ' data-association="aapa" />
															<label>AAPA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-arba" type="checkbox" ' . $arba . ' data-association="arba" />
															<label>ARBA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-agc" type="checkbox" ' . $agc . ' data-association="agc" />
															<label>AGC</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-abc" type="checkbox" ' . $abc . ' data-association="abc" />
															<label>ABC</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-auca" type="checkbox" ' . $auca . ' data-association="auca" />
															<label>AUCA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-none" type="checkbox" ' . $none . ' data-association="none" />
															<label>None</label>
														</div>
													</div>
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div style="width:100%;" class="teqcidb-form-section-fields-indiv-wrapper" id="teqcidb-form-section-add-new-user-button-for-width">
													<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-add-new-user-button">Register</button>
													<div class="teqcidb-spinner" id="teqcidb-spinner-add-new-frontend-spinner"></div>
								 					<div class="teqcidb-response-div-actual-container">
								 						<p class="teqcidb-response-div-p"></p>
								 					</div>
												</div>
											</div>
										</div>
									</div>
									</div>';




			echo $login_register_html;


		}

		/**
		 * The Registration HTML for those already registered.
		 */
		public function display_registered_user_dashboard() {

			// Get logged in user's info
			$current_user = wp_get_current_user();

			global $wpdb;
			$table_name = $wpdb->prefix . 'teqcidb_students';
			// Check for duplicate email.
			$user_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s", $current_user->user_email ) );

			// Build the state selction drop-down deal.
				switch ( $user_info->contactstate ) {
					case 'AL':
						$selected1 = 'selected';
						break;
					case 'AK':
						$selected2 = 'selected';
						break;
					case 'AZ':
						$selected3 = 'selected';
						break;
					case 'AR':
						$selected4 = 'selected';
						break;
					case 'CA':
						$selected5 = 'selected';
						break;
					case 'CO':
						$selected6 = 'selected';
						break;
					case 'CT':
						$selected7 = 'selected';
						break;
					case 'DE':
						$selected8 = 'selected';
						break;
					case 'DC':
						$selected9 = 'selected';
						break;
					case 'FL':
						$selected10 = 'selected';
						break;
					case 'GA':
						$selected11 = 'selected';
						break;
					case 'HI':
						$selected12 = 'selected';
						break;
					case 'ID':
						$selected13 = 'selected';
						break;
					case 'IL':
						$selected14 = 'selected';
						break;
					case 'IN':
						$selected15 = 'selected';
						break;
					case 'IA':
						$selected16 = 'selected';
						break;
					case 'KS':
						$selected17 = 'selected';
						break;
					case 'KY':
						$selected18 = 'selected';
						break;
					case 'LA':
						$selected19 = 'selected';
						break;
					case 'ME':
						$selected20 = 'selected';
						break;
					case 'MD':
						$selected21 = 'selected';
						break;
					case 'MA':
						$selected22 = 'selected';
						break;
					case 'MI':
						$selected23 = 'selected';
						break;
					case 'MN':
						$selected24 = 'selected';
						break;
					case 'MS':
						$selected25 = 'selected';
						break;
					case 'MO':
						$selected26 = 'selected';
						break;
					case 'MT':
						$selected27 = 'selected';
						break;
					case 'NE':
						$selected28 = 'selected';
						break;
					case 'NV':
						$selected29 = 'selected';
						break;
					case 'NH':
						$selected30 = 'selected';
						break;
					case 'NJ':
						$selected31 = 'selected';
						break;
					case 'NM':
						$selected32 = 'selected';
						break;
					case 'NY':
						$selected33 = 'selected';
						break;
					case 'NC':
						$selected34 = 'selected';
						break;
					case 'ND':
						$selected35 = 'selected';
						break;
					case 'OH':
						$selected36 = 'selected';
						break;
					case 'OK':
						$selected37 = 'selected';
						break;
					case 'OR':
						$selected38 = 'selected';
						break;
					case 'PA':
						$selected39 = 'selected';
						break;
					case 'RI':
						$selected40 = 'selected';
						break;
					case 'SC':
						$selected41 = 'selected';
						break;
					case 'SD':
						$selected42 = 'selected';
						break;
					case 'TN':
						$selected43 = 'selected';
						break;
					case 'TX':
						$selected44 = 'selected';
						break;
					case 'UT':
						$selected45 = 'selected';
						break;
					case 'VT':
						$selected46 = 'selected';
						break;
					case 'VA':
						$selected47 = 'selected';
						break;
					case 'WA':
						$selected48 = 'selected';
						break;
					case 'WV':
						$selected49 = 'selected';
						break;
					case 'WI':
						$selected50 = 'selected';
						break;
					case 'WY':
						$selected51 = 'selected';
						break;
					
					default:
						// code...
						break;
				}

			$opening_html = '<div id="teqcidb-form-section-wrapper-forms-and-tabs-container" class="teqcidb-form-section-wrapper-forms-and-tabs-container-all-classes-ui">
												<div id="teqcidb-form-section-wrapper-top-container">';

			$logged_in_user_dashboard_accountinfo_html = '';


			// Now we need to get all the classes that currently exist in the database for a user to potentially register for.
			$class_table = $wpdb->prefix . 'teqcidb_classes';
			$class_db_results = $wpdb->get_results( "SELECT * FROM $class_table" );
			$class_html = '';
			foreach ( $class_db_results as $key => $class ) {

				// If the class isn't full AND it's not hidden from the front-end...
				if (  ( $class->classsize > $class->classregistrantnumber ) && ( 'Yes' !== $class->classhide  ) ) {

					// Let's first format a few DB things...
					if ( 'inperson' === $class->classformat ) {
						$class->classformat = 'In-Person';
					}

					// Format the dates...
					$startdate_array = explode('-', $class->classstartdate );
					$class->classstartdate = $startdate_array[1] . '-' . $startdate_array[2] . '-' . $startdate_array[0];
					$enddate_array = explode('-', $class->classenddate );
					$class->classenddate = $enddate_array[1] . '-' . $enddate_array[2] . '-' . $enddate_array[0];

					// Format Times.
					$date = '19:24:15 06/13/2013'; 
					$class->classstarttime = date('g:i a', strtotime( $class->classstarttime ));
					$class->classendtime = date('g:i a', strtotime( $class->classendtime ));

					if ( 'online' === $class->classformat ) {
						$class->classstartdate = 'N/A - Class held online';
						$class->classstreetaddress = 'N/A - Class held online';
						$class->classcity = 'N/A - Class held online';
						$class->classstate = 'N/A - Class held online';
						$class->classzip = 'N/A - Class held online';
					}


					$class_html = $class_html . '
					<div class="teqcidb-classes-view-container teqcidb-all-classes">
						<button id="class' . $class->ID . '" class="accordion teqcidb-classes-view-container-accordion-heading">' . $class->classname . '</button>
						<div class="teqcidb-students-indiv-class-info-container" data-open="false" style="height: 0px; opacity:0;">
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidb-form-section-fields-wrapper">
									<div style="width: initial;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Description</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . $class->classdescription . '</p>
									</div>
								</div>
							</div>
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidb-form-section-fields-wrapper">
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Cost</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classcost ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Type</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classtype ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Format</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classformat ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Date</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classstartdate ) . '</p>
									</div>
								</div>
							</div>
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidb-form-section-fields-wrapper">
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class Start Time</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper( $class->classstarttime ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Class End Time</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper( $class->classendtime ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Street Address</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classstreetaddress ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">City</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . $class->classcity . '</p>
									</div>
								</div>
							</div>
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidb-form-section-fields-wrapper">
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">State</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classstate ) . '</p>
									</div>
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
										<label class="teqcidb-form-section-fields-label">Zip Code</label>
										<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords( $class->classzip ) . '</p>
									</div>
								</div>
							</div>
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidb-form-section-fields-wrapper">
									<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
										<button class="teqcidb-form-section-fields-wrapper-class-registration-options-onlinebutton-link" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">Register for this Class</button>
									</div>
								</div>
							</div>
						</div>';
				}
			}

			$logged_in_user_dashboard_classes_html = '
													<div class="teqcidb-form-section-wrapper teqcidb-form-section-wrapper-class-registration">
														<p class="teqcidb-form-section-wrapper-intro-p">View all upcoming training opportunities below.</p>
														' . $class_html . '
													</div>
												</div>';
			$closing_html = '</div>';
			echo $opening_html . $logged_in_user_dashboard_accountinfo_html . $logged_in_user_dashboard_classes_html . $closing_html;
		}

	

	}
endif;