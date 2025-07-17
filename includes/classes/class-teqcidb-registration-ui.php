<?php
/**
 * MedWestHealthPoints_Registration_UI Class that dispalys the login form or the user dashboard - class-teqcidb-dashboard-ui.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('TEQciDb_Registration_UI', false)) :

  /**
   * MedWestHealthPoints_Admin_Menu Class.
   */
  class TEQciDb_Registration_UI
  {


    /**
     * Class Constructor
     */
    public function __construct()
    {


      // For grabbing an image from media library.
      wp_enqueue_media();

      wp_enqueue_script('password-strength-meter');

      // See if we have a currently logged-in user.
      $loggedin = is_user_logged_in();

      // If user is logged in...
      if ($loggedin) {
        $this->stitch_logged_in_member_html();
      } else {
        $this->stitch_login_or_register_html();
      }
    }



    /**
     * Builds and outputs the final HTML for individuals to sign in or register.
     */
    public function stitch_login_or_register_html()
    {
      $this->display_login_or_register_form();
    }

    /**
     * Builds and outputs the final HTML for individuals who are already registered.
     */
    public function stitch_logged_in_member_html()
    {
      $this->display_registered_user_dashboard();
    }

    /**
     * The Login/Register HTML.
     */
    public function display_login_or_register_form()
    {

      $args = array(
        'echo'           => false,
        'remember'       => true,
        'redirect'       => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'form_id'        => 'teqcidb-dashboard-login-row-wrapper',
        'id_username'    => 'user_login',
        'id_password'    => 'user_pass',
        'id_remember'    => 'rememberme',
        'id_submit'      => 'wp-submit',
        'label_username' => __('Email'),
        'label_password' => __('Password'),
        'label_remember' => __('Remember Me'),
        'label_log_in'   => __('Log In'),
        'value_username' => '',
        'value_remember' => false
      );

      $login_register_html = '<div id="teqcidb-dashboard-container">
										<div id="teqcidb-dashboard-login-wrapper">
											<p class="teqcidb-tab-intro-para">Already a QCI student? Log in below.</p>' . wp_login_form($args) . '
											<a href="' . wp_lostpassword_url() . '">Forgot your password? Reset it here!</a>
										</div>
										<div class="teqcidb-spinner" id="teqcidb-spinner-login-frontend-spinner">
										</div>
									<div id="teqcidb-not-registered-top-container">
										<p>Are you a first-time online registrant?</p>
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
													<label class="teqcidb-form-section-fields-label">Email</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-email" type="text" placeholder="Your Email Address" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Verify Email</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-emailverify" type="text" placeholder="Verify Your Email Address" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Office Phone</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-officephone" type="text" placeholder="Users\'s Office Phone" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Street Address</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-streetaddress" type="text" placeholder="Your Street Address" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">City</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-city" type="text" placeholder="Your City" />
												</div>
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
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Alternate Contact Name</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Alternate Contact Email</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" />
												</div>
												<div class="teqcidb-form-section-fields-indiv-wrapper">
													<label class="teqcidb-form-section-fields-label">Alternate Contact Phone</label>
													<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone" type="text" placeholder="Alternate Contact Phone" />
												</div>
											</div>
											<div class="teqcidb-form-section-fields-wrapper">
												<div class="teqcidb-form-section-fields-indiv-wrapper-checkboxes">
													<label class="teqcidb-form-section-fields-label">Affiliated Associations</label>
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
    public function display_registered_user_dashboard()
    {

      // Get logged in user's info
      $current_user = wp_get_current_user();

      global $wpdb;
      $table_name = $wpdb->prefix . 'teqcidb_students';
      // Check for duplicate email.
      $user_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s", $current_user->user_email));

      $associations = [
        "AAPA" => "120",
        "ARBA" => "35",
        "AGC" => "50",
        "ABC" => "70",
        "AUCA" => "10",
      ];
      $discount_rate = 0;
      $discount_association = "";

      $user_associations = strtoupper($user_info->associations);

      if (strlen($user_associations) > 0) {
        $u_associations = array_flip(array_filter(explode(",", $user_associations)));

        $discount_list = array_intersect_key($associations, $u_associations);

        $association_list_flip = array_flip($associations);

        if (count($discount_list) > 0) {
          $discount_rate = (int) max($discount_list);
          $discount_association = array_search(max($discount_list), $discount_list);
        }
      }

      // Build the state selction drop-down deal.
      switch ($user_info->contactstate) {
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

$temp = '';
$finalhtml = '';
if( false !== stripos( $user_info->associations, ',')  ){
	$temp = explode( ',', $user_info->associations );
	foreach ( $temp as $asskey => $assvalue ) {
		$finalhtml .= $assvalue . ', ';
	}
	$user_info->associations = ltrim($finalhtml, ',');
	$user_info->associations = ltrim($user_info->associations, ' ');
	$user_info->associations = ltrim($user_info->associations, ',');
	$user_info->associations = ltrim($user_info->associations, ' ');
	$user_info->associations = rtrim($user_info->associations, ',');
	$user_info->associations = rtrim($user_info->associations, ' ');
	$user_info->associations = rtrim($user_info->associations, ',');
	$user_info->associations = rtrim($user_info->associations, ' ');
	$user_info->associations = ucwords($user_info->associations);
} elseif( ( null === $user_info->associations ) || ( '' === $user_info->associations ) ) {
	$finalhtml = 'None';
	$user_info->associations = $finalhtml;
}


// Let's get every class the student has history for that has passed, so we can display all the certs for printing.
$class_history_table = $wpdb->prefix . 'teqcidb_studenthistory';
$class_history_db_results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $class_history_table WHERE uniquestudentid = %s AND registered = %s AND attended = %s AND outcome = %s AND paymentstatus = %s",
        $user_info->uniquestudentid,
        'yes',
        'yes',
        'passed',
        'paidinfull'
    )
);
//echo $user_info->uniquestudentid;
//echo var_dump($class_history_db_results);

$certifications_html = ''; // Initialize the variable to store the generated HTML




/*




if (!empty($class_history_db_results)) {
    $all_class_results = []; // Array to hold all results from the teqcidb_classes table

    // Loop through each entry in $class_history_db_results
    foreach ($class_history_db_results as $entry) {
        $classes_for_cert_table = $wpdb->prefix . 'teqcidb_classes';
        $class_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $classes_for_cert_table WHERE uniqueclassid = %s",
                $entry->uniqueclassid
            )
        );

        // Add results to the array if not empty
        if (!empty($class_results)) {
            $all_class_results = array_merge($all_class_results, $class_results);
        }
    }

    // Sort all results by 'classstartdate' descending
    usort($all_class_results, function ($a, $b) {
        return strtotime($b->classstartdate) - strtotime($a->classstartdate);
    });

    // Use the most recent entry for further processing
    if (!empty($all_class_results)) {
        $class_results = $all_class_results[0]; // The most recent entry

        $classtype = '';
        if ($class_results->classformat == 'inperson' && $class_results->classtype == 'initial') {
            $classtype = 'initial in-person';
            $classname = $class_results->classname;
        } elseif ($class_results->classformat == 'online' && $class_results->classtype == 'initial') {
            $classtype = 'initial online';
            $classname = $class_results->classname;
        } elseif ($class_results->classformat == 'inperson' && $class_results->classtype == 'refresher') {
            $classtype = 'refresher in-person';
            $classname = $class_results->classname;
        } elseif ($class_results->classformat == 'online' && $class_results->classtype == 'refresher') {
            $classtype = 'refresher online';
            $classname = $class_results->classname;
        }

        // Generate the specific HTML for the determined class type
        if ($classtype === 'initial in-person') {
            $certifications_html .= '<div style="min-height:135px;margin-right:5px;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-initial" 
                                    id="teqcidbplugin-generate-class-forms-button-certification-initial-' . esc_attr($user_info->ID) . '" 
                                    data-id="' . esc_attr($user_info->ID) . '" 
                                    data-uniquestudentid="' . esc_attr($user_info->uniquestudentid) . '" 
                                    data-firstname="' . esc_attr($user_info->firstname) . '" 
                                    data-lastname="' . esc_attr($user_info->lastname) . '" 
                                    data-company="' . esc_attr($user_info->company) . '" 
                                    data-contactstreetaddress="' . esc_attr($user_info->contactstreetaddress) . '" 
                                    data-contactcity="' . esc_attr($user_info->contactcity) . '" 
                                    data-contactstate="' . esc_attr($user_info->contactstate) . '" 
                                    data-contactzip="' . esc_attr($user_info->contactzip) . '" 
                                    data-phonecell="' . esc_attr($user_info->phonecell) . '" 
                                    data-phoneoffice="' . esc_attr($user_info->phoneoffice) . '" 
                                    data-email="' . esc_attr($user_info->email) . '" 
                                    data-initialtrainingdate="' . esc_attr($user_info->initialtrainingdate) . '" 
                                    data-expirationdate="' . esc_attr($user_info->expirationdate) . '" 
                                    data-qcinumber="' . esc_attr($user_info->qcinumber) . '" 
                                    data-associations="' . esc_attr($user_info->associations) . '" 
                                    data-lastrefresherdate="' . esc_attr($user_info->lastrefresherdate) . '">
                                    <img class="teqcidbplugin-form-section-placeholder-image-small" 
                                         src="https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/plus.png">
                                    <p data-nohistory=""><strong>Generate Completion Certificate</strong><br/><br/>' . $classname . '</p>
                                 </div>';
        } elseif ($classtype === 'refresher in-person') {
            $certifications_html .= '<div style="min-height:135px;margin-right:5px;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-refresher" 
                                    id="teqcidbplugin-generate-class-forms-button-certification-refresher-' . esc_attr($user_info->ID) . '" 
                                    data-id="' . esc_attr($user_info->ID) . '" 
                                    data-uniquestudentid="' . esc_attr($user_info->uniquestudentid) . '" 
                                    data-firstname="' . esc_attr($user_info->firstname) . '" 
                                    data-lastname="' . esc_attr($user_info->lastname) . '" 
                                    data-company="' . esc_attr($user_info->company) . '" 
                                    data-contactstreetaddress="' . esc_attr($user_info->contactstreetaddress) . '" 
                                    data-contactcity="' . esc_attr($user_info->contactcity) . '" 
                                    data-contactstate="' . esc_attr($user_info->contactstate) . '" 
                                    data-contactzip="' . esc_attr($user_info->contactzip) . '" 
                                    data-phonecell="' . esc_attr($user_info->phonecell) . '" 
                                    data-phoneoffice="' . esc_attr($user_info->phoneoffice) . '" 
                                    data-email="' . esc_attr($user_info->email) . '" 
                                    data-initialtrainingdate="' . esc_attr($user_info->initialtrainingdate) . '" 
                                    data-expirationdate="' . esc_attr($user_info->expirationdate) . '" 
                                    data-qcinumber="' . esc_attr($user_info->qcinumber) . '" 
                                    data-associations="' . esc_attr($user_info->associations) . '" 
                                    data-lastrefresherdate="' . esc_attr($user_info->lastrefresherdate) . '">
                                    <img class="teqcidbplugin-form-section-placeholder-image-small" 
                                         src="https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/plus.png">
                                    <p data-nohistory=""><strong>Generate Completion Certificate</strong><br/><br/>' . $classname . '</p>
                                 </div>';
        }

        // Check for the existence of 'initial online' and 'refresher online' classes in $all_class_results
        $has_initial_online = false;
        $has_refresher_online = false;

        foreach ($all_class_results as $class) {
            if ($class->classformat == 'online' && $class->classtype == 'initial') {
                $has_initial_online = true;
            }
            if ($class->classformat == 'online' && $class->classtype == 'refresher') {
                $has_refresher_online = true;
            }
        }

        // Include 'initial online' certificate if it exists
        if ($has_initial_online) {
            $certifications_html .= '<div style="min-height:135px;margin-right:5px;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-initial-online" 
                                    id="teqcidbplugin-generate-class-forms-button-certification-initial-online-' . esc_attr($user_info->ID) . '" 
                                    data-id="' . esc_attr($user_info->ID) . '" 
                                    data-uniquestudentid="' . esc_attr($user_info->uniquestudentid) . '" 
                                    data-firstname="' . esc_attr($user_info->firstname) . '" 
                                    data-lastname="' . esc_attr($user_info->lastname) . '" 
                                    data-company="' . esc_attr($user_info->company) . '" 
                                    data-contactstreetaddress="' . esc_attr($user_info->contactstreetaddress) . '" 
                                    data-contactcity="' . esc_attr($user_info->contactcity) . '" 
                                    data-contactstate="' . esc_attr($user_info->contactstate) . '" 
                                    data-contactzip="' . esc_attr($user_info->contactzip) . '" 
                                    data-phonecell="' . esc_attr($user_info->phonecell) . '" 
                                    data-phoneoffice="' . esc_attr($user_info->phoneoffice) . '" 
                                    data-email="' . esc_attr($user_info->email) . '" 
                                    data-initialtrainingdate="' . esc_attr($user_info->initialtrainingdate) . '" 
                                    data-expirationdate="' . esc_attr($user_info->expirationdate) . '" 
                                    data-qcinumber="' . esc_attr($user_info->qcinumber) . '" 
                                    data-associations="' . esc_attr($user_info->associations) . '" 
                                    data-lastrefresherdate="' . esc_attr($user_info->lastrefresherdate) . '">
                                    <img class="teqcidbplugin-form-section-placeholder-image-small" 
                                         src="https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/plus.png">
                                    <p data-nohistory=""><strong>Generate Completion Certificate</strong><br/><br/>Initial Online Course</p>
                                 </div>';
        }

        // Include 'refresher online' certificate if it exists
        if ($has_refresher_online) {
            $certifications_html .= '<div style="min-height:135px;margin-right:5px;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-refresher-online" 
                                    id="teqcidbplugin-generate-class-forms-button-certification-refresher-online-' . esc_attr($user_info->ID) . '" 
                                    data-id="' . esc_attr($user_info->ID) . '" 
                                    data-uniquestudentid="' . esc_attr($user_info->uniquestudentid) . '" 
                                    data-firstname="' . esc_attr($user_info->firstname) . '" 
                                    data-lastname="' . esc_attr($user_info->lastname) . '" 
                                    data-company="' . esc_attr($user_info->company) . '" 
                                    data-contactstreetaddress="' . esc_attr($user_info->contactstreetaddress) . '" 
                                    data-contactcity="' . esc_attr($user_info->contactcity) . '" 
                                    data-contactstate="' . esc_attr($user_info->contactstate) . '" 
                                    data-contactzip="' . esc_attr($user_info->contactzip) . '" 
                                    data-phonecell="' . esc_attr($user_info->phonecell) . '" 
                                    data-phoneoffice="' . esc_attr($user_info->phoneoffice) . '" 
                                    data-email="' . esc_attr($user_info->email) . '" 
                                    data-initialtrainingdate="' . esc_attr($user_info->initialtrainingdate) . '" 
                                    data-expirationdate="' . esc_attr($user_info->expirationdate) . '" 
                                    data-qcinumber="' . esc_attr($user_info->qcinumber) . '" 
                                    data-associations="' . esc_attr($user_info->associations) . '" 
                                    data-lastrefresherdate="' . esc_attr($user_info->lastrefresherdate) . '">
                                    <img class="teqcidbplugin-form-section-placeholder-image-small" 
                                         src="https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/plus.png">
                                    <p data-nohistory=""><strong>Generate Completion Certificate</strong><br/><br/>Refresher Online Course</p>
                                 </div>';
        }
    }
} else {
    // Optional: Handle case where no results are found
    $certifications_html = '<p>No certifications available for this student.</p>';
}

// Output the generated HTML
//echo $certifications_html;



*/







error_log($user_info->associations);

		// Specify the redirect URL after logout
		$redirect_url = 'https://training.thompsonengineering.com/register-for-a-class-qci/';

		// Generate the logout URL with the redirect parameter
		$logout_url = wp_logout_url($redirect_url);

      $opening_html = '<div id="teqcidb-form-section-wrapper-forms-and-tabs-container" class="teqcidb-form-section-wrapper-forms-and-tabs-container-register">
												<div id="teqcidb-form-section-wrapper-lefthand-tabs-container">
													<div id="teqcidb-form-section-wrapper-lefthand-tabs-inner-wrapper">
														<div class="teqcidb-form-section-wrapper-lefthand-tabs-actual teqcidb-form-section-wrapper-lefthand-tabs-actual-accountinfo teqcidb-form-section-wrapper-lefthand-tabs-actual-active">
															<p>Account&nbsp;Info</p>
														</div>
														<div class="teqcidb-form-section-wrapper-lefthand-tabs-actual teqcidb-form-section-wrapper-lefthand-tabs-actual-classes">
															<p>Register for Classes</p>
														</div>
														<a href="' . esc_url($logout_url) . '">Click Here to Logout</a>
													</div>
												</div>
												<div id="teqcidb-form-section-wrapper-top-container">';

      $logged_in_user_dashboard_accountinfo_html = '
													<div class="teqcidb-form-section-wrapper teqcidb-form-section-wrapper-accountinfo">
														<p class="teqcidb-form-section-wrapper-intro-p">View your account information below.</p>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">New Password</label>
																<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-password" name="password" type="password" placeholder="New Password" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Verify Password</label>
																<input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-passwordverify" name="password_retyped" type="password" placeholder="Verify New Password" />
															</div>
														</div>
														<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
															<div class="teqcidb-form-section-fields-indiv-wrapper">
																<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-see-new-user-password" data-visibility="hidden">View Password</button>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper">
																<p style="opacity:0" id="password-strength">Password Strength is...</p>
															</div>
														</div>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">First Name</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->firstname . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">First Name</label>
																<input value="' . $user_info->firstname . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-firstname" type="text" placeholder="Your First Name" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Last Name</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->lastname . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Last Name</label>
																<input value="' . $user_info->lastname . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-lastname" type="text" placeholder="Your Last Name" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Cell Phone</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->phonecell . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Cell Phone</label>
																<input value="' . $user_info->phonecell . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-cellphone" type="text" placeholder="Your Cell Phone" />
															</div>
														</div>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Office Phone</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->phoneoffice . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Office Phone</label>
																<input value="' . $user_info->phoneoffice . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-officephone" type="text" placeholder="Users\'s Office Phone" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Email</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->email . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Email</label>
																<input value="' . $user_info->email . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-email" type="text" placeholder="Your Email Address" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Street Address</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->contactstreetaddress . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Street Address</label>
																<input value="' . $user_info->contactstreetaddress . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-streetaddress" type="text" placeholder="Your Street Address" />
															</div>
														</div>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">City</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->contactcity . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">City</label>
																<input value="' . $user_info->contactcity . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-city" type="text" placeholder="Your City" />
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">State</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->contactstate . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">State</label>
																<select class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-select" id="teqcidb-user-state" type="text" placeholder="Your State">
																	<option ' . $selected1 . ' value="AL">Alabama</option>
																	<option ' . $selected2 . ' ="AK">Alaska</option>
																	<option ' . $selected3 . ' ="AZ">Arizona</option>
																	<option ' . $selected4 . ' ="AR">Arkansas</option>
																	<option ' . $selected5 . ' ="CA">California</option>
																	<option ' . $selected6 . ' ="CO">Colorado</option>
																	<option ' . $selected7 . ' ="CT">Connecticut</option>
																	<option ' . $selected8 . ' ="DE">Delaware</option>
																	<option ' . $selected9 . ' ="DC">District Of Columbia</option>
																	<option ' . $selected10 . ' ="FL">Florida</option>
																	<option ' . $selected11 . ' ="GA">Georgia</option>
																	<option ' . $selected12 . ' ="HI">Hawaii</option>
																	<option ' . $selected13 . ' ="ID">Idaho</option>
																	<option ' . $selected14 . ' ="IL">Illinois</option>
																	<option ' . $selected15 . ' ="IN">Indiana</option>
																	<option ' . $selected16 . ' ="IA">Iowa</option>
																	<option ' . $selected17 . ' ="KS">Kansas</option>
																	<option ' . $selected18 . ' ="KY">Kentucky</option>
																	<option ' . $selected19 . ' ="LA">Louisiana</option>
																	<option ' . $selected20 . ' ="ME">Maine</option>
																	<option ' . $selected21 . ' ="MD">Maryland</option>
																	<option ' . $selected22 . ' ="MA">Massachusetts</option>
																	<option ' . $selected23 . ' ="MI">Michigan</option>
																	<option ' . $selected24 . ' ="MN">Minnesota</option>
																	<option ' . $selected25 . ' ="MS">Mississippi</option>
																	<option ' . $selected26 . ' ="MO">Missouri</option>
																	<option ' . $selected27 . ' ="MT">Montana</option>
																	<option ' . $selected28 . ' ="NE">Nebraska</option>
																	<option ' . $selected29 . ' ="NV">Nevada</option>
																	<option ' . $selected30 . ' ="NH">New Hampshire</option>
																	<option ' . $selected31 . ' ="NJ">New Jersey</option>
																	<option ' . $selected32 . ' ="NM">New Mexico</option>
																	<option ' . $selected33 . ' ="NY">New York</option>
																	<option ' . $selected34 . ' ="NC">North Carolina</option>
																	<option ' . $selected35 . ' ="ND">North Dakota</option>
																	<option ' . $selected36 . ' ="OH">Ohio</option>
																	<option ' . $selected37 . ' ="OK">Oklahoma</option>
																	<option ' . $selected38 . ' ="OR">Oregon</option>
																	<option ' . $selected39 . ' ="PA">Pennsylvania</option>
																	<option ' . $selected40 . ' ="RI">Rhode Island</option>
																	<option ' . $selected41 . ' ="SC">South Carolina</option>
																	<option ' . $selected42 . ' ="SD">South Dakota</option>
																	<option ' . $selected43 . ' ="TN">Tennessee</option>
																	<option ' . $selected44 . ' ="TX">Texas</option>
																	<option ' . $selected45 . ' ="UT">Utah</option>
																	<option ' . $selected46 . ' ="VT">Vermont</option>
																	<option ' . $selected47 . ' ="VA">Virginia</option>
																	<option ' . $selected48 . ' ="WA">Washington</option>
																	<option ' . $selected49 . ' ="WV">West Virginia</option>
																	<option ' . $selected50 . ' ="WI">Wisconsin</option>
																	<option ' . $selected51 . ' ="WY">Wyoming</option>
																</select>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label class="teqcidb-form-section-fields-label">Zip Code</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->contactzip . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<label class="teqcidb-form-section-fields-label">Zip Code</label>
																<input value="' . $user_info->contactzip . '" class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-zip" type="text" placeholder="Your Zip Code" />
															</div>
														</div>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label style="height:25px;" class="teqcidb-form-section-fields-label teqcidb-jake-special-width-mobile">Alternate Contact Name</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->altcontactname . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label style="height:25px;" class="teqcidb-form-section-fields-label teqcidb-jake-special-width-mobile">Alternate Contact Phone</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->altcontactphone . '</p>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
																<label style="height:25px;" class="teqcidb-form-section-fields-label teqcidb-jake-special-width-mobile">Alternate Contact Email</label>
																<p class="teqcidb-form-section-fields-label-actualvalue">' . $user_info->altcontactemail . '</p>
															</div>
														</div>
													<div class="teqcidb-form-section-fields-wrapper">
														<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
															<label style="height:25px;" class="teqcidb-form-section-fields-label">Associations</label>
															<p class="teqcidb-form-section-fields-label-actualvalue">' . ucfirst($user_info->associations) . '</p>
														</div>
													</div>

													<div class="teqcidb-form-section-fields-wrapper">
														<div style="width:100%;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
															<label style="height:25px;" class="teqcidb-form-section-fields-label">Your Wallet Card</label>
															' . $certifications_html . '
															<div style="margin-right:5px;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-walletcard" 
															     id="teqcidbplugin-generate-class-forms-button-walletcard-'. $class->uniqueclassid .'"
															     data-id="'. $class->uniqueclassid .'"
															     data-uniquestudentid="'. $user_info->uniquestudentid .'"
															     data-firstname="'. $user_info->firstname .'"
															     data-lastname="'. $user_info->lastname .'"
															     data-company="'. $user_info->company .'"
															     data-contactstreetaddress="'. $user_info->contactstreetaddress .'"
															     data-contactcity="'. $user_info->contactcity .'"
															     data-contactstate="'. $user_info->contactstate .'"
															     data-contactzip="'. $user_info->contactzip .'"
															     data-phonecell="'. $user_info->phonecell .'"
															     data-phoneoffice="'. $user_info->phoneoffice .'"
															     data-email="'. $user_info->email .'"
															     data-initialtrainingdate="'. $user_info->initialtrainingdate .'"
															     data-expirationdate="'. $user_info->expirationdate .'"
															     data-qcinumber="'. $user_info->qcinumber .'"
															     data-associations="'. $user_info->associations .'"
															     data-lastrefresherdate="'. $user_info->lastrefresherdate .'">
															     
															    <img class="teqcidbplugin-form-section-placeholder-image-small" 
															         src="'. TEQCIDB_ROOT_IMG_URL . 'plus.png">
															    <p data-nohistory=""><strong>Generate Wallet Card</strong></p>
															</div>
														</div>
													</div>



														<div style="display:none;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-edit-account-button">
															<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-edit-user-account-info" data-visibility="hidden">Edit Account Info</button>
														</div>
														<div class="teqcidb-form-section-fields-wrapper">
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-edit-existing-user-button-from-dashboard">Save Changes</button>
																<div class="teqcidb-spinner"></div>
											 					<div class="teqcidb-response-div-actual-container">
											 						<p class="teqcidb-response-div-p"></p>
											 					</div>
															</div>
															<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-actualinput">
																<button class="teqcidb-form-section-submit-button" id="teqcidb-form-section-cancel-edit-existing-user-button-from-dashboard">Cancel Editing</button>
																<div class="teqcidb-spinner"></div>
											 					<div class="teqcidb-response-div-actual-container">
											 						<p class="teqcidb-response-div-p"></p>
											 					</div>
															</div>
														</div>
													</div>';


      // Now we need to get all the classes that currently exist in the database for a user to potentially register for.
      $class_table = $wpdb->prefix . 'teqcidb_classes';
      $class_db_results = $wpdb->get_results("SELECT * FROM $class_table");
      $class_html = '';
      foreach ($class_db_results as $key => $class) {

        // If the class isn't full AND it's not hidden from the front-end...
				if (  ( $class->classsize > $class->classregistrantnumber ) && ( 'Yes' !== $class->classhide  ) ) {

          // Let's first format a few DB things...
          if ('inperson' === $class->classformat) {
            $class->classformat = 'In-Person';
          }

          if ('online' === $class->classformat) {
            $class->classstreetaddress = 'N/A - Class held online';
            $class->classcity = 'N/A - Class held online';
            $class->classstate = 'N/A - Class held online';
            $class->classzip = 'N/A - Class held online';
          }

          // Format the dates...
          $startdate_array = explode('-', $class->classstartdate);
          $class->classstartdate = $startdate_array[1] . '-' . $startdate_array[2] . '-' . $startdate_array[0];
          $enddate_array = explode('-', $class->classenddate);
          $class->classenddate = $enddate_array[1] . '-' . $enddate_array[2] . '-' . $enddate_array[0];

          // Format Times.
          $date = '19:24:15 06/13/2013';
          $class->classstarttime = date('g:i a', strtotime($class->classstarttime));
          $class->classendtime = date('g:i a', strtotime($class->classendtime));

          $clean_cost = (float) str_replace(["$", ",", " "], "", $class->classcost);
          $discounted_cost = (float) ($clean_cost - $discount_rate);
          $discounted_cost = ($discounted_cost <= 0) ? 0 : $discounted_cost;
          $value_input = "";

          if ( ( 'initial' === $class->classtype ) && (  ( 'none' !== $user_info->associations ) && ( 'None' !== $user_info->associations ) && ( '' !== $user_info->associations ) && ( null !== $user_info->associations )   )    ){
          	$clean_cost = $clean_cost - 50;
          	$class->classcost = '$' . $clean_cost;
          }

          if ($discount_rate) {
            $value_input .= '<div class="teqcidb-students-association-discount-wrapper" >';
            $value_input .= '<div class="teqcidb-students-association-discount teqcidb-clean-cost" > Class Cost: ' . $clean_cost . '</div>';
            $value_input .= '<div class="teqcidb-students-association-discount teqcidb-discount-cost" >' . $discount_association . ": -" . $discount_rate . '</div>';
            $value_input .= '</div>';
          }
          $value_input .= '<label for="amount">$';
          $value_input .= '<input type="txt" id="amount" name="amount" value="' . $discounted_cost . '">';
          $value_input .= '</label>';

         	// Determine if the logged-in student is expired or not.
					$expiredcssvariable = '';
					$expiredmessaging = 'View all upcoming training opportunities below.';
					$today = new DateTime();
					$provided_date = DateTime::createFromFormat('Y-m-d', $user_info->expirationdate);


					if (
					    empty($user_info->expirationdate) ||  // Check if expiration date is empty or invalid
					    empty($user_info->qcinumber) ||      // Check if QCI number is empty or invalid
					    ($provided_date->format('Y-m-d') >= $today->format('Y-m-d'))            // Check if expiration date is in the past (but not today)
					) {
					    // The student is not expired and can register, even if today is their expiration date.
					} else {
					    // The student is not expired and can register, even if today is their expiration date.
							$expiredmessaging = '<span style="font-size:20px; font-weight:bold;">It looks like your QCI certification may be expired, or you may need to sign up for an Initial Training Course instead of a Refresher Course.<br/><br/>Ensure that you are logged in with the correct account. The person currently logged in is:<br/><br/>' . $user_info->firstname . ' ' . $user_info->lastname . '<br/>' . $user_info->email . '<br/><br/>Please contact the Stormwater Training administrators by phone at <a href="tel:251-666-2443">(251) 666-2443</a>, or by email at <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a></span>';
					    $expiredcssvariable = ' pointer-events:none!important; ';
					}


			    if ( ( 'refresher' === $class->classtype ) && ( 'View all upcoming training opportunities below.' !== $expiredmessaging ) ){
			    	$class_html = $class_html . '
						<div class="teqcidb-classes-view-container teqcidb-all-classes">
							<button class="accordion teqcidb-classes-view-container-accordion-heading" >' . $class->classname . '</button>
							<div class="teqcidb-students-indiv-class-info-container" data-open="false" style="height: 0px; opacity:0;">
								<div class="teqcidbplugin-form-wrapper">
									<p style="padding: 10px;color: red;text-align: center;" class="teqcidb-form-section-wrapper-intro-p">' . $expiredmessaging . '</p>
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
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classcost) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Type</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classtype) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Format</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classformat) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Date</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstartdate) . '</p>
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-form-wrapper">
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Start Time</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper($class->classstarttime) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class End Time</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper($class->classendtime) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Street Address</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstreetaddress) . '</p>
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
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstate) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Zip Code</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classzip) . '</p>
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-form-wrapper">
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
											<button style="' . $expiredcssvariable . '" class="teqcidb-form-section-fields-wrapper-class-registration-options-onlinebutton" data-class-payment-open="'.$key.'" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">Register & Pay Online Now</button>
											<button class="teqcidb-form-section-fields-wrapper-class-registration-options-pdfformbutton" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">Print & Email Your Registration Form</button>
										</div>
									</div>
									<div class="teqcidbplugin-form-variables">
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-discountassociation_'. $key .'" type="hidden" value="' . $discount_association . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-wpuserid_'. $key .'" type="hidden" value="' . $user_info->wpuserid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniquestudentid_'. $key .'" type="hidden" value="' . $user_info->uniquestudentid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniqueclassid_'. $key .'" type="hidden" value="' . $class->uniqueclassid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-discountrate_'. $key .'" type="hidden" value="' . $discount_rate . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-cleancost_'. $key .'" type="hidden" value="' . $clean_cost . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingstreetaddress_'. $key .'" type="hidden" value="' . ucwords($user_info->contactstreetaddress) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingcity_'. $key .'" type="hidden" value="' . ucwords($user_info->contactcity) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingstate_'. $key .'" type="hidden" value="' . ucwords($user_info->contactstate) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingzip_'. $key .'" type="hidden" value="' . ucwords($user_info->contactzip) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-classname_'. $key .'" type="hidden" value="' . ucwords($class->classname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-paymentamount_'. $key .'" type="hidden" value="$' . $discounted_cost . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfirstname_'. $key .'" type="hidden" value="' . ucwords($user_info->firstname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillinglastname_'. $key .'" type="hidden" value="' . ucwords($user_info->lastname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingcompany_'. $key .'" type="hidden" value="' . ucwords($user_info->company) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingphonecell_'. $key .'" type="hidden" value="' . ucwords($user_info->phonecell) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfax_'. $key .'" type="hidden" value="' . ucwords($user_info->fax) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingemail_'. $key .'" type="hidden" value="' . ucwords($user_info->email) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingtableid_'. $key .'" type="hidden" value="' . ucwords($user_info->ID) . '" />
	                </div>
									<div class="teqcidbplugin-form-wrapper teqcidbplugin-form-wrapper-print-and-email-actual" data-open="false" style="height: 0px; opacity:0;">
										<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-flex-with-header">
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header teqcidb-tab-intro-para-flex-header-red">Please read this information before submitting your completed registration form or payment!</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header"><span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Cancellation & Payment Policy:</span> Registration fees for in-person classes and online courses are non-refundable. Payment is requested prior to or on the date of the training. In certain situations, we may issue credits that are good for one year from the original (initial) training date. These credits may be transferable to another employee of the same company/organization. We do not issue credits for online refresher training fees.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">Certificates of completion and QCI numbers issued upon completion of training and receipt of payment.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">For more information or clarification, please call <a href="tel:251-666-2443">(251) 666-2443</a>.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">
	                      Once you\'ve downloaded the registration form, you can email the completed form to <a href="mailto:QCI@thompsonengineering.com">QCI@thompsonengineering.com</a>, or mail to the address below. Payments can be mailed to the address below as well.
	                    </p>
	                  </div>
	                  <div class="teqcidb-form-section-fields-wrapper">
	                    <div style="margin:0px;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
	                      <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Thompson Engineering</span><br/>
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">ATTN: QCI Program</span><br/>
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">2970 Cottage Hill Road, Suite 190</span>
	                        <br/><span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Mobile, AL 36606</span>
	                      </p>
	                      <a class="teqcidb-registration-form-link" target="_blank" href="https://training.thompsonengineering.com/wp-content/uploads/2024/07/1-QCI-FORM-2024-NEW.pdf">
	                        <button class="teqcidb-form-section-fields-wrapper-class-registration-options-pdfformbutton" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">
	                          Click Here for Registration Form
	                        </button>
	                      </a>
	                    </div>
	                  </div>
	                </div>
	              </div>
	            </div>
	          </div>';

			    } else {
			    	$class_html = $class_html . '
						<div class="teqcidb-classes-view-container teqcidb-all-classes">
							<button class="accordion teqcidb-classes-view-container-accordion-heading" >' . $class->classname . '</button>
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
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classcost) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Type</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classtype) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Format</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classformat) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Date</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstartdate) . '</p>
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-form-wrapper">
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class Start Time</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper($class->classstarttime) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Class End Time</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . strtoupper($class->classendtime) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Street Address</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstreetaddress) . '</p>
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
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classstate) . '</p>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
											<label class="teqcidb-form-section-fields-label">Zip Code</label>
											<p class="teqcidb-form-section-fields-label-actualvalue">' . ucwords($class->classzip) . '</p>
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-form-wrapper">
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
											<button class="teqcidb-form-section-fields-wrapper-class-registration-options-onlinebutton" data-class-payment-open="'.$key.'" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">Register & Pay Online Now</button>
											<button class="teqcidb-form-section-fields-wrapper-class-registration-options-pdfformbutton" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">Print & Email Your Registration Form</button>
										</div>
									</div>
									<div class="teqcidbplugin-form-variables">
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-discountassociation_'. $key .'" type="hidden" value="' . $discount_association . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-wpuserid_'. $key .'" type="hidden" value="' . $user_info->wpuserid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniquestudentid_'. $key .'" type="hidden" value="' . $user_info->uniquestudentid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniqueclassid_'. $key .'" type="hidden" value="' . $class->uniqueclassid . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-discountrate_'. $key .'" type="hidden" value="' . $discount_rate . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-cleancost_'. $key .'" type="hidden" value="' . $clean_cost . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingstreetaddress_'. $key .'" type="hidden" value="' . ucwords($user_info->contactstreetaddress) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingcity_'. $key .'" type="hidden" value="' . ucwords($user_info->contactcity) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingstate_'. $key .'" type="hidden" value="' . ucwords($user_info->contactstate) . '" />
	                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingzip_'. $key .'" type="hidden" value="' . ucwords($user_info->contactzip) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-classname_'. $key .'" type="hidden" value="' . ucwords($class->classname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-paymentamount_'. $key .'" type="hidden" value="$' . $discounted_cost . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfirstname_'. $key .'" type="hidden" value="' . ucwords($user_info->firstname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillinglastname_'. $key .'" type="hidden" value="' . ucwords($user_info->lastname) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingcompany_'. $key .'" type="hidden" value="' . ucwords($user_info->company) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingphonecell_'. $key .'" type="hidden" value="' . ucwords($user_info->phonecell) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfax_'. $key .'" type="hidden" value="' . ucwords($user_info->fax) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingemail_'. $key .'" type="hidden" value="' . ucwords($user_info->email) . '" />
	                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingtableid_'. $key .'" type="hidden" value="' . ucwords($user_info->ID) . '" />
	                </div>
									<div class="teqcidbplugin-form-wrapper teqcidbplugin-form-wrapper-print-and-email-actual" data-open="false" style="height: 0px; opacity:0;">
										<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-flex-with-header">
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header teqcidb-tab-intro-para-flex-header-red">Please read this information before submitting your completed registration form or payment!</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header"><span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Cancellation & Payment Policy:</span> Registration fees for in-person classes and online courses are non-refundable. Payment is requested prior to or on the date of the training. In certain situations, we may issue credits that are good for one year from the original (initial) training date. These credits may be transferable to another employee of the same company/organization. We do not issue credits for online refresher training fees.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">Certificates of completion and QCI numbers issued upon completion of training and receipt of payment.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">For more information or clarification, please call <a href="tel:251-666-2443">(251) 666-2443</a>.</p>
	                    <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">
	                      Once you\'ve downloaded the registration form, you can email the completed form to <a href="mailto:QCI@thompsonengineering.com">QCI@thompsonengineering.com</a>, or mail to the address below. Payments can be mailed to the address below as well.
	                    </p>
	                  </div>
	                  <div class="teqcidb-form-section-fields-wrapper">
	                    <div style="margin:0px;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
	                      <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Thompson Engineering</span><br/>
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">ATTN: QCI Program</span><br/>
	                        <span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">2970 Cottage Hill Road, Suite 190</span>
	                        <br/><span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Mobile, AL 36606</span>
	                      </p>
	                      <a class="teqcidb-registration-form-link" target="_blank" href="https://training.thompsonengineering.com/wp-content/uploads/2024/07/1-QCI-FORM-2024-NEW.pdf">
	                        <button class="teqcidb-form-section-fields-wrapper-class-registration-options-pdfformbutton" data-uniqueclassid="' . $class->uniqueclassid . '" data-uniquestudentid="' . $user_info->uniquestudentid . '" data-wpuserid="' . $user_info->wpuserid . '">
	                          Click Here for Registration Form
	                        </button>
	                      </a>
	                    </div>
	                  </div>
	                </div>
	              </div>
	            </div>
	          </div>';
			    }


          
        }
      }

      $logged_in_user_dashboard_classes_html = '
													<div style="display: none;" class="teqcidb-form-section-wrapper teqcidb-form-section-wrapper-class-registration">
														' . $class_html . '
													</div>
												</div>
												<div style="display: none;"> 
                          <div class="teqcidbplugin-form-wrapper teqcidbplugin-form-wrapper-registration-and-payment-actual" id="secure_payment_class_form">
                            <div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-flex-with-header">
                              <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header teqcidb-tab-intro-para-flex-header-red">Please read this information before completing your registration and payment below!</p>
                              <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header"><span class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header-bold">Cancellation & Payment Policy:</span> Registration fees for in-person classes and online courses are non-refundable. Payment is requested prior to or on the date of the training. In certain situations, we may issue credits that are good for one year from the original (initial) training date. These credits may be transferable to another employee of the same company/organization. We do not issue credits for online refresher training fees.</p>
                              <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">Certificates of completion and QCI numbers issued upon completion of training and receipt of payment.</p>
                              <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">For more information or clarification, please call <a href="tel:251-666-2443">(251) 666-2443</a>.</p>
                              <p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-flex-header">Provide your Payment information below.</p>
                            </div>
                            <div class="teqcidb-form-section-fields-wrapper">
                              <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options teqcidb-form-section-fields-wrapper-class-registration-options-first-row">
                              	<div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxccnumber">Credit Card Number</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxccnumber" type="text" value="" />
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxexpiremonth">Expiration Month</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxexpiremonth" type="number" value="" min="1" max="12"/>
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxexpireyear">Expiration Year</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxexpireyear" type="number" value="" min="2023" max="2099"/>
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxcvv">CVV</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxcvv" type="number" value="" min="0" max="9999"/>
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxnameoncard">First Name on Card</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxfirstnameoncard" type="text" value="" />
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label" for="teqcidb-user-xboxnameoncard">Last Name on Card</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-xboxlastnameoncard" type="text" value="" />
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label">Billing Street Address</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingstreetaddress" type="text" value="" />
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label">Billing City</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-billingcity" type="text" value="" />
	                                </div>
                                </div>
                                <div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label">Billing State</label>
	                                  <select class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-select" id="teqcidb-user-billingstate">
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
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label class="teqcidb-form-section-fields-label">Billing Zip Code</label>
	                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" maxlength="5" id="teqcidb-user-billingzip" type="text" value="" />
	                                </div>
	                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-flexed">
	                                  <label style="display:inline-block;" class="teqcidb-form-section-fields-label">If different from student\'s email address, enter an email address for credit card receipts below:</label>
	                                  <input placeholder="exampleemail@gmail.com" style="margin-top:20px!important;"  class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-receipts" type="text" value="" />
	                                </div>
                                </div>
                              </div>
                            </div>
                            <div class="teqcidb-form-section-fields-wrapper">
                              <div style="margin:0px;" class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-wrapper-class-registration-options">
                                <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext">
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-classname" type="hidden" value="" />
                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-wpuserid" type="hidden" value="" />
                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniquestudentid" type="hidden" value="" />
                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-uniqueclassid" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-paymentamount" type="hidden" value="" />
                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfirstname" type="hidden" value="" />
                                  <input class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillinglastname" type="hidden" value="" />  
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingcompany" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingphonecell" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingfax" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingemail" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-userbillingtableid" type="hidden" value="" />
                                  <input disabled class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-text" id="teqcidb-user-classname" type="hidden" value="" />
                                </div>
                              </div>
                            </div>
                            <div class="teqcidb-form-section-fields-wrapper">
                              <div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-form-section-fields-indiv-wrapper-justtext teqcidb-form-section-fields-indiv-wrapper-protectform">
                                  <div id="pt_hpf_form">
                                  </div>
                                  <input type="txt" id="HPF_Token" name="HPF_Token" hidden>
                                  <input type="txt" id="enc_key" name="enc_key" hidden>
                                  <div id="teqcidb_class_cost_and_discount_description"></div>
                                  <input style="display:none;" type="txt" id="amount" name="amount" value="0">
                                  <input type="submit" value="Submit" id="SubmitButton" />
                              </div>
                              <div class="teqcidb-form-section-fields-wrapper">
                              	<div id="teqcidb-spinner-paymentsubmit" class="teqcidb-spinner"></div>
                            	</div>
                              <div class="teqcidb-form-section-fields-valiation-descriptions" id="teqcidb-form-valiation-description"> </div>
                            </div>
                            <div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-account-for-height">
                              <div class="teqcidb-response-div-actual-container">
                                <p class="teqcidb-response-div-p"></p>
                              </div>
                            </div>
                          </div>
                        </div>';
      $closing_html = '</div>';
      echo $opening_html . $logged_in_user_dashboard_accountinfo_html . $logged_in_user_dashboard_classes_html . $closing_html;
    }
  }
endif;