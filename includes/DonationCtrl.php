<?php 

class DonationCtrl {

	protected static $instance;

	public static $contribution_options = array(
			1 => '$25 Member',
			2 => '$50 Representative\'s Club',
			3 => '$100 Senator\'s Club',
			4 => '$250 Governor\'s Club',
			5 => '$500 President\'s Club',
			6 => 'Annual Recurring Payroll Deduction'
		);

	public static $donation_amounts = array(
			1 => 25,
			2 => 50,
			3 => 100,
			4 => 250,
			5 => 500,
			6 => 0
		);

	protected $view;

	private function __construct() {
		$this->view = new DonationView();
        $this->mail = new PHPMailer();
	}	

	private function __clone() {}

	public static function getInstance() {
		if( is_null(self::$instance) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function registerMenu() {
		$icon = WP_PLUGIN_URL . "/pd-donate/images/dollar.png";

		add_menu_page('PD Donation', 'PD Donation', 'manage_options', 'pd-donation', array('DonationCtrl', 'donateBack'), $icon);

		add_submenu_page('pd-donation', 'Donation Settings', 'Settings', 'manage_options', 'pd-donation-settings', array('DonationCtrl', 'settings'));
	}
	
	public static function loadAdminScripts() {
		$file = WP_PLUGIN_URL . "/pd-donate/css/admin.css";
		
		wp_register_style('donation-admin', $file);
		wp_enqueue_style('donation-admin');
	
		$file = WP_PLUGIN_URL . "/pd-donate/js/admin.js";
		
		wp_register_script('donation-script', $file);
		wp_enqueue_script('donation-script');
		wp_localize_script('donation-script', 'GEMCPAC', array(
			'donate_index'	=> admin_url('admin.php?page=pd-donation')
		));
	}

	public static function donateBack() {
		$instance = self::getInstance();
		$model = DonationModel::getInstance();

		$is_single_page = isset($_GET['action']) && $_GET['action']=='view' ? true : false;

		if( $is_single_page && $_GET['id'] && is_numeric($_GET['id']) ) {

			$donation_id = absint($_GET['id']);

			$instance->view->donation_amounts = self::$donation_amounts;

			$instance->view->donation = $model->getDonationById($donation_id);

			$instance->view->render('admin/single-donation-view');

		} else {

			$instance->view->year_months_pair = $model->getDonatedYearMonths();
		
			$curr_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
			
			$instance->view->curr_page = $curr_page <= 0 ? 1 : $curr_page;
			
			$instance->view->donations_per_page = 40;
			
			$instance->view->offset = ($instance->view->curr_page - 1) * $instance->view->donations_per_page;
			
			$selectParams = array(
				'limit'		=> $instance->view->donations_per_page,
				'offset'	=> $instance->view->offset
			);
			
			$filters = array();
			
			if( isset($_GET['dyear']) ) {
				$selectParams['year'] = absint($_GET['dyear']);			
				$filters['year'] = $selectParams['year'];
			}		
			
			if( isset($_GET['dmonth']) ) {
				$selectParams['month'] = absint($_GET['dmonth']);
				$filters['month'] = $selectParams['month'];
			}

			$instance->view->delete_nonce = wp_create_nonce(GDONATE_SALT . "delete_donation_entry");
				
			
			$instance->view->donations_total = $model->getTotalDonations( $filters );
			
			$instance->view->donations = $model->getDonations( $selectParams );
			
			$instance->view->export_url = '';
					
			if( count($instance->view->donations) > 0 ) {
				$url_params = array();
			
				$url_params[] = 'page=pd-donation';
					
				$url_params[] = 'action=export';
				
				$url_params[] = 'export_nonce=' . wp_create_nonce('EXPORT_DONATIONS_TO_CSV');
				
				if( isset($_GET['dyear']) ) {
					$url_params[] = 'dyear=' . absint($_GET['dyear']);
				}
				
				if( isset($_GET['dmonth']) ) {
					$url_params[] = 'dmonth=' . absint($_GET['dmonth']);
				}
				
				if( isset($_GET['paged']) ) {
					$url_params[] = 'paged=' . absint($_GET['paged']);
				}
				
				$query_str = implode('&', $url_params);
				$instance->view->export_url = admin_url('admin.php?') . $query_str;

			}
					
			
			$instance->view->render('admin/index');

		}
	}

	public static function handlePostRequest() {
		if( isset($_POST['settings_nonce']) && wp_verify_nonce($_POST['settings_nonce'], GDONATE_SALT . "settings") ) {
			$new_settings = array(
				'donate_page' => absint($_POST['donation_page']),
				'thankyou_page'	=> absint($_POST['thankyou_page']),
				'paypal_email'	=> sanitize_email($_POST['paypal_email']),
				'use_live_pp_env'	=> absint($_POST['paypal_env']),
				'notification_email' => sanitize_email($_POST['notification_email'])
				);

			update_option('donation_options', $new_settings);


			wp_redirect(admin_url('admin.php?page=pd-donation-settings&update=1'));

			exit();
		}
	}

	public static function shortcodeExistsCheck() {
		global $post;


		if( is_singular() && has_shortcode($post->post_content, 'pd_donate') ) {
			add_action('wp_enqueue_scripts', array('DonationCtrl', 'loadFrontScripts'));
		}

	}

	public static function loadFrontScripts() {

		$file = WP_PLUGIN_URL . "/pd-donate/css/shortcode-style.css";

		wp_register_style('donation-style', $file);
		wp_enqueue_style('donation-style');

		$file = WP_PLUGIN_URL . "/pd-donate/js/shortcode-script.js";

		wp_enqueue_script('donation-script', $file, array('jquery'));
		wp_localize_script('donation-script', 'GEMCPAC', array('ajaxurl'=>admin_url('admin-ajax.php')));

	}

	public static function settings() {
		$instance = self::getInstance();

		$instance->view->nonce = GDONATE_SALT . "settings";

		$instance->view->nonce_action = "settings_nonce";

		$instance->view->options = get_option('donation_options');

		$instance->view->pages = get_pages();

		$instance->view->donate_page_set = isset($instance->view->options['donate_page']) && get_post($instance->view->options['donate_page']) ? true : false;

		$instance->view->ipn_url = $instance->view->donate_page_set ? get_permalink($instance->view->options['donate_page']) : null;

		if( ! is_null($instance->view->ipn_url) ) {
			$instance->view->ipn_url = add_query_arg(array('listen'=>'ipn'), $instance->view->ipn_url);
		}
	
		$instance->view->render('admin/settings');
	}

	public static function donateShortcode() {
		global $post;

		$instance = self::getInstance();

		$settings = get_option('donation_options');

		$email = !isset($settings['paypal_email']) ? null : sanitize_email($settings['paypal_email']);

		if( ! $email ) {
		  
			$html = $instance->view->render('front/error', false);
            
		} else {
		    
			$instance->view->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr/';
			
            //$instance->view->paypal_url = 'https://www.paypal.com/cgi-bin/webscr/';
            
			if( isset($settings['use_live_pp_env']) && $settings['use_live_pp_env'] ) {
			 
				$instance->view->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
                
			}
			
			$instance->view->paypal_email = isset($settings['paypal_email']) ? $settings['paypal_email'] : '';
			
			$instance->view->pageurl = get_permalink($post->ID);
			
			$instance->view->thankyou_url =  isset($settings['thankyou_page']) ? get_permalink($settings['thankyou_page']) : $instance->view->pageurl;

			$instance->view->nonce_field = GDONATE_SALT . "Donate_K3y_Please";

			$instance->view->nonce_action = '_donate_form_nonce';

			$instance->view->style_url = WP_PLUGIN_URL . "/pd-donate/css/shortcode-style.css";

			$html = $instance->view->render('front/donate-form', false);
		}

		return $html;
	}

	public static function handleFrontPostRequest() {

		global $post;

		if( isset($_POST['_donate_form_nonce']) && wp_verify_nonce($_POST['_donate_form_nonce'], GDONATE_SALT . "Donate_K3y_Please") ) {
			$year 			= isset($_POST['membership_year']) ? absint($_POST['membership_year']) : '';
			$contribution 	= isset($_POST['contribution']) ? absint($_POST['contribution']) : 1;
			$name 			= isset($_POST['donor_name']) ? sanitize_text_field($_POST['donor_name']) : '';
			$employer 		= isset($_POST['donor_employer']) ? sanitize_text_field($_POST['donor_employer']) : '';
			$occupation 	= isset($_POST['donor_occupation']) ? sanitize_text_field($_POST['donor_occupation']) : '';
			$system_name 	= isset($_POST['donor_system_name']) ? sanitize_text_field($_POST['donor_system_name']) : '';
			$home_address 	= isset($_POST['donor_home_address']) ? sanitize_text_field($_POST['donor_home_address']) : '';
			$city 			= isset($_POST['donor_city']) ? sanitize_text_field($_POST['donor_city']) : '';
			$zipcode 		= isset($_POST['donor_zipcode']) ? sanitize_text_field($_POST['donor_zipcode']) : '';
			$state 			= isset($_POST['donor_state']) ? sanitize_text_field($_POST['donor_state']) : '';
			$email 			= isset($_POST['donor_email']) ? sanitize_text_field($_POST['donor_email']) : '';
			$contribution_amount = 0;

			if( $contribution == 6 ) {
				$contribution_amount = abs(floatval($_POST['deduction-amount']));
			}


			if( empty( $year ) || empty( $contribution ) || empty( $name ) || empty( $employer )  || empty( $occupation ) ||
			 empty( $system_name ) ||  empty( $home_address ) || empty( $city ) || empty( $zipcode ) || empty( $state ) || empty( $email ) ) {
				return;
			}

			if( ! is_email( $email ) ) {
				return;
			}

			if( ! array_key_exists($contribution, self::$contribution_options) ) {
				return;
			}

			if( $contribution == 6 && $contribution_amount <= 0 ) {
				return;
			}	
			
			date_default_timezone_set('EST');
			$now = date('Y-m-d H:i:s');

			$params = array(
				'donor_name'	=> $name,
				'donor_employer'	=> $employer,
				'donor_occupation'	=> $occupation,
				'donor_system_name'	=> $system_name,
				'donor_home_address'	=> $home_address,
				'donor_city'			=> $city,
				'donor_state'			=> $state,
				'donor_zip'				=> $zipcode,
				'donor_email'			=> $email,
				'donor_donation_type'	=> $contribution,
				//'donor_amount_donated'	=> $contribution_amount,
				'donor_membership_year'	=> $year,
				'donor_date_donated'	=> $now
				);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s'
				);

			$model = DonationModel::getInstance();

			$redirect = get_permalink($post->ID);

			if( $model->addDonation($params, $format) ) {

				$redirect = add_query_arg( array('donate'=>'success'), $permalink);

			} else {

				$redirect = add_query_arg( array('donate'=>'fail'), $permalink);
			}

			wp_redirect($redirect);
			exit();
		}

	}

	public static function registerPacDonor() {
	    $instance = self::getInstance();
        
        $options = get_option('donation_options');
        
        $notification_email = trim( $options['notification_email'] );

		if( empty($notification_email) ) {
			$notification_email = get_bloginfo('admin_email');
		}
        
		//var_dump($_POST);
		//die;

		global $post;

		if( isset($_POST['_donate_form_nonce']) && wp_verify_nonce($_POST['_donate_form_nonce'], GDONATE_SALT . "Donate_K3y_Please") ) {
			$year 			= isset($_POST['year']) ? absint($_POST['year']) : '';
			$contribution 	= isset($_POST['contribution']) ? absint($_POST['contribution']) : 1;
			$name 			= isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
			$employer 		= isset($_POST['employer']) ? sanitize_text_field($_POST['employer']) : '';
			$occupation 	= isset($_POST['occupation']) ? sanitize_text_field($_POST['occupation']) : '';
			$system_name 	= isset($_POST['system_name']) ? sanitize_text_field($_POST['system_name']) : '';
			$home_address 	= isset($_POST['home_address']) ? sanitize_text_field($_POST['home_address']) : '';
			$city 			= isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
			$zipcode 		= isset($_POST['zipcode']) ? sanitize_text_field($_POST['zipcode']) : '';
			$state 			= isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
			$email 			= isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
			$contribution_amount = 0;

			$response = array(
				'error'	=> false,
				'message'	=> ''
				);

			
			if( $contribution == 6 ) {
				$contribution_amount = abs(floatval($_POST['deduction_amount']));
			} 


			if( empty( $year ) || empty( $contribution ) || empty( $name ) || empty( $employer )  || empty( $occupation ) ||
			 empty( $system_name ) ||  empty( $home_address ) || empty( $city ) || empty( $zipcode ) || empty( $state ) || empty( $email ) ) {
				$response['error'] = true;
				$response['message'] = 'Please fill up the required fields.';

				echo json_encode($response);
				die;
			}

			if( ! is_email( $email ) ) {
				$response['error'] = true;
				$response['message'] = 'You have entered an invalid email address.';

				echo json_encode($response);
				die;
			}

			if( ! array_key_exists($contribution, self::$contribution_options) ) {
				$response['error'] = true;
				$response['message'] = 'Invalid contribution entered.';

				echo json_encode($response);
				die;
			}

			if( $contribution == 6 && $contribution_amount <= 0 ) {
				$response['error'] = true;
				$response['message'] = 'Invalid contribution amount.';

				echo json_encode($response);
				die;
			}

			$params = array(
				'donor_name'	=> $name,
				'donor_employer'	=> $employer,
				'donor_occupation'	=> $occupation,
				'donor_system_name'	=> $system_name,
				'donor_home_address'	=> $home_address,
				'donor_city'			=> $city,
				'donor_state'			=> $state,
				'donor_zip'				=> $zipcode,
				'donor_email'			=> $email,
				'donor_donation_type'	=> $contribution,
				// 'donor_amount_donated'	=> $contribution_amount,
				'donor_membership_year'	=> $year,
				'donor_date_donated'	=> date('Y-m-d H:i:s'),
				'donate_status'			=> 'unpaid'
				);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s'
				);

			$model = DonationModel::getInstance();

			//$redirect = get_permalink($post->ID);
			

			if( ( $d_id = $model->addDonation($params, $format) ) ) {

				$response['d_id'] = $d_id;
				//$redirect = add_query_arg( array('donate'=>'success'), $permalink);
				//$response['_nonce'] = wp_create_nonce("paypal_donate" . $d_id);
                    
                    $form_alt = 'Name:'.esc_html($name).'
                        		 Employer:'.esc_html($employer).'
                        		 Occupation:'.esc_html($occupation).'
                                 System Name:'.esc_html($system_name).'
                                 Home Address:'.esc_html($home_address).'
                                 City:'.esc_html($city).'
                                 State:'.esc_html($state).'
                                 Zipcode:'.esc_html($zipcode).'
                                 Email Address:'.esc_html($email).'
                                 Donation Type:'.esc_html( self::$contribution_options[$contribution] ).'
                                 Date Donated:'.esc_html( date('Y-m-d H:i:s') ).'
                                 Membership Year:'.esc_html($year).'';
                    
                    $form = '<table class="form-table donation-table">
                    			<tr>
                    				<th>Name: </th>
                    				<td>'.esc_html($name).'</td>
                    				<th>Employer: </th>
                    				<td>'.esc_html($employer).'</td>
                    			</tr>
                    			<tr>
                    				<th>Occupation: </th>
                    				<td>'.esc_html($occupation).'</td>
                    				<th>System Name: </th>
                    				<td>'.esc_html($system_name).'</td>
                    			</tr>
                    			<tr>
                    				<th>Home Address: </th>
                    				<td>'.esc_html($home_address).'</td>
                    				<th>City: </th>
                    				<td>'.esc_html($city).'</td>
                    			</tr>
                    			<tr>
                    				<th>State: </th>
                    				<td>'.esc_html($state).'</td>
                    				<th>Zipcode: </th>
                    				<td>'.esc_html($zipcode).'</td>
                    			</tr>
                    			<tr>
                    				<th>Email Address: </th>
                    				<td>'.esc_html($email).'</td>
                    				<th>Donation Type: </th>
                    				<td>'.esc_html( self::$contribution_options[$contribution] ).'</td>
                    			</tr>
                    			<tr>
                    				<th>Date Donated: </th>
                    				<td>'.esc_html( date('Y-m-d H:i:s') ).'</td>
                    				<th>Membership Year: </th>
                    				<td>'.esc_html($year).'</td>
                    			</tr>
                    		</table>';

                    $instance->mail->SetFrom( $notification_email, 'Paypal Donation Form' );
                    $instance->mail->AddReplyTo( ' noreply@pd-donation.com', 'pd-donation' );
                    
                    $instance->mail->AddAddress( $notification_email, "Email");
                    $instance->mail->AddCC( $notification_email, "Email");
                  
                    $instance->mail->Subject = '['.$name.'] New Donation';
                    $instance->mail->AltBody = $form_alt;
                    
                    $instance->mail->IsHTML(true);        
                    $instance->mail->MsgHTML( $form );
                    
                    if( !$instance->mail->Send() ){
                        $response['send'] = 'email send';
                    } else {
                        $response['error-send'] = $instance->mail->ErrorInfo;
                    }
                
			} else {

				//$redirect = add_query_arg( array('donate'=>'fail'), $permalink);
				$response['error'] = true;
				$response['message'] = 'Failed to process form. Please try again.';
			}

			echo json_encode($response);
			die;
			//wp_redirect($redirect);
			//exit();
		}
	}

	
	public static function handleGetRequest() {
		// var_dump($_GET); die;
		if( is_admin() && isset($_GET['export_nonce']) && wp_verify_nonce($_GET['export_nonce'], 'EXPORT_DONATIONS_TO_CSV') ) {
			// ob_end_clean();
		
			$param = array();
			
			if( isset($_GET['dyear']) ) {
				$param['year'] = absint($_GET['dyear']);
			}
			
			if( isset($_GET['dmonth']) ) {
				$param['month'] = absint($_GET['dmonth']);
			}						
			
			// var_dump($_REQUEST); 
			// var_dump($_GET);
			// die;
			
			$model = DonationModel::getInstance();
			
			$donations = $model->getDonations( $param );
			
			if( count($donations) > 0 ) {
				// $filename = 'GEMC_PAC_DONATIONS.CSV';
				// header( 'Content-Type: text/csv' );
				// header( 'Content-Disposition: attachment;filename='.$filename);
			
				//$out = fopen('php://output', 'w');
				// $file = GDONATE_BASEPATH . "/tmp/". time() .".csv";
				// $out = fopen($file, "w");
				
				$headings = array(
					'Name', 'Employer', 'Occupation', 'System Name', 'Home Address', 'City', 'Zip', 'Email', 'Donation Type', 'Membership Year', 'Amount Donated', 'Payer ID', 'Transaction ID', 'Date Donated'
				);


				
				// fputcsv($out, $headings);
				$lines = '';

				$lines = implode(",", $headings);
				$lines .= "\n";
				
				foreach( $donations as $donation ) {
					$donation_type = absint($donation->donor_donation_type);
					$donation_type = self::$contribution_options[$donation_type];
					$date = date('Y-m-d h:i a', strtotime($donation->donor_date_donated));
					
					$line = array(
						$donation->donor_name, $donation->donor_employer, $donation->donor_occupation, $donation->donor_system_name, $donation->donor_home_address, 
						$donation->donor_city, $donation->donor_zip, $donation->donor_email, $donation_type, $donation->donor_membership_year, $donation->payment_amount,
						$donation->payer_id, $donation->txn_id, $date
					);
					
					$line = implode(",", $line);
					$lines .= $line . "\n";
					// fputcsv($out, $line);
				}
				
				
				
				
				
				// fclose($out);

				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=GEMC_PAC_DONATIONS.csv");
				header("Pragma: no-cache");
				header("Expires: 0");

				echo $lines;

				exit();
				
			}
		}


		if( isset($_GET['_delete_nonce']) && wp_verify_nonce($_GET['_delete_nonce'], GDONATE_SALT . "delete_donation_entry") 
			&& isset($_GET['did']) && is_numeric($_GET['did']) ) {

			$donation_id = absint($_GET['did']);
			$query_param = array();

			if( isset($_GET['dmonth']) ) {
				$query_param["dmonth"] = absint($_GET['dmonth']);
			}

			if( isset($_GET['dyear']) ) {
				$query_param["dyear"] = absint($_GET['dyear']);
			}

			$model = DonationModel::getInstance();

			$redirect = admin_url('admin.php?page=pd-donation');

			

			if( $model->deleteDonationById( $donation_id )) {
				$query_param["delete"] = 'success';
			} else {
				$query_param["delete"] = 'fail';
			}

			$redirect = add_query_arg( $query_param, $redirect);

			wp_redirect($redirect);
			exit();

		}
		
	}


	public static function ipnListener() {
        $instance = self::getInstance();
        
		if( isset($_GET['listen']) && $_GET['listen'] == 'ipn' ) {
			global $wpdb, $post;

			$options = get_option('donation_options');

			$donate_page = isset($options['donate_page']) ? absint($options['donate_page']) : 0;

			if( $post->ID != $donate_page ) {
				return;
			}

			$paypal_url = '';
			$use_live_env = isset($options['use_live_pp_env']) ? absint($options['use_live_pp_env']) : false;

			if( $use_live_env ) {
				$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
			} else {
				$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			}

			$logfile = GDONATE_LOG ."/". date("Y-m-d") . "_ipn.log";

			$raw_post_data = file_get_contents('php://input');

			if( empty( $raw_post_data ) ) {
				error_log(date('[Y-m-d H:i e] '). "Do input found. Terminating script...: " . PHP_EOL, 3, $logfile);
				exit;
			}

			$raw_post_array = explode('&', $raw_post_data);

			$myPost = array();

			foreach ($raw_post_array as $keyval) {
				$keyval = explode ('=', $keyval);
				if (count($keyval) == 2)
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}

			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';
			if(function_exists('get_magic_quotes_gpc')) {
				$get_magic_quotes_exists = true;
			}

			foreach ($myPost as $key => $value) {
				if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
					$value = urlencode(stripslashes($value));
				} else {
					$value = urlencode($value);
				}
				$req .= "&$key=$value";
			}

			// Post IPN data back to PayPal to validate the IPN data is genuine
			// Without this step anyone can fake IPN data

			$ch = curl_init($paypal_url);
			if ($ch == FALSE) {
				return FALSE;
			}

			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

			// Set TCP timeout to 30 seconds
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

			$cert = GDONATE_BASEPATH . "/cacert.pem";
			curl_setopt($ch, CURLOPT_CAINFO, $cert);

			$res = curl_exec($ch);

			if (curl_errno($ch) != 0) { // cURL error 

				error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, $logfile);
				
				curl_close($ch);
				
				exit;

			} 

			curl_close($ch);

			// Inspect IPN validation result and act accordingly

			if (strcmp ($res, "VERIFIED") == 0) {
				// check whether the payment_status is Completed
				// check that txn_id has not been previously processed
				// check that receiver_email is your PayPal email
				// check that payment_amount/payment_currency are correct
				// process payment and mark item as paid.

				// assign posted variables to local variables
				//$item_name = $_POST['item_name'];
				//$item_number = $_POST['item_number'];
				//$payment_status = $_POST['payment_status'];
				//$payment_amount = $_POST['mc_gross'];
				//$payment_currency = $_POST['mc_currency'];
				//$txn_id = $_POST['txn_id'];
				//$receiver_email = $_POST['receiver_email'];
				//$payer_email = $_POST['payer_email'];

				$payment_amount 	= $_POST['mc_gross'];
				$payment_currency 	= $_POST['mc_currency'];
				$payer_id 			= $_POST['payer_id'];
				$payment_status 	= $_POST['payment_status'];
				$txn_id 			= $_POST['txn_id'];
				$receiver_email 	= $_POST['receiver_email'];
				//$payment_date 		= urldecode($_POST['payment_date']);
				//$payer_email 		= $_POST['payer_email'];
				//$id_nonce_pair		= explode("_", $_POST['custom']);
				//$donation_id		= $id_nonce_pair[0];
				//$nonce 				= $id_nonce_pair[1];
				$donation_id 		= absint($_POST['custom']);

				$model = DonationModel::getInstance();

				// validate if receiver_email is correct
				if( $receiver_email != $options['paypal_email'] ) {
					error_log( "Incorrect receiver email." . PHP_EOL, 3, $logfile);
					exit;
				}

				// validate if the nonce is correct
				// if( ! wp_verify_nonce( $nonce, "paypal_donate" . $donation_id ) ) {
				// 	error_log( "nonce: {$nonce}, donation_id: {$donation_id}, txn_id: {$txn_id}." . PHP_EOL, 1, "godz.lopez@gmail.com");
				// 	error_log( "Unauthorized request, nonce is invalid for transaction #[$txn_id]." . PHP_EOL, 3, $logfile);
				// 	exit;
				// }

				// check currency
				if( $payment_currency != "USD" ) {
					error_log( "Incorrect currency [$payment_currency] for transaction #[$txn_id]" . PHP_EOL, 3, $logfile);
					exit;
				}

				// check if the payment status is Completed
				if (strcmp ($payment_status, "Completed") != 0) {
					error_log( "Payment is not complete for transaction #[$txn_id]" . PHP_EOL, 3, $logfile);
					exit;
				}

				$donor_data = $model->getDonationById($donation_id);
				$donation_type = absint($donor_data->donor_donation_type);
				$donation_amount = self::$donation_amounts[$donation_type];

				if( $donation_type != 6 ) {
					//if( $donation_amount == $payment_amount )
					if( bccomp($payment_amount, $donation_amount) != 0 ) {
						// update donation message here
						error_log( "Donor ID #[$donation_id] has donated but the amount is not the exact donation amount. " . PHP_EOL, 3, $logfile);
						exit;
					}
				}

				if( $model->txnIdExists( $txn_id ) ) {
					error_log( "Invalid transaction, a transaction ID #[$txn_id] already exists. " . PHP_EOL, 3, $logfile);
					exit;
				}

				// stopped here
				// update status, currency, amount, payer_id, txn_id
				if( ! $model->update( array(
						'donate_status'		=> 'paid',
						'payment_amount'	=> $payment_amount,
						'payment_currency'	=> $payment_currency,
						'payer_id'			=> $payer_id,
						'txn_id'			=> $txn_id
					), array(
						'id'	=> $donation_id
					), 
					'%s', 
					'%d' ) ) {					
					error_log( "Failed to update donation #[$donation_id] " . PHP_EOL, 1, "charlycapillanes@gmail.com");
					exit;

				} else {
					$donation = $model->getDonationById( $donation_id );
					$donor_single_view = admin_url('admin.php?page=pd-donation&action=view&id=' . $donation->id);
					$notification_email = trim( $options['notification_email'] );

					if( empty($notification_email) ) {
						$notification_email = get_bloginfo('admin_email');
					}

					$subject  = "[{$txn_id}] New Donation";

					$headers  = "From: " . $donation->donor_email . "\r\n";
					$headers .= "Reply-To: noreply@pd-donation.com" . "\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                    
                    $form_alt = 'Name:'.esc_html($name).'
                        		 Employer:'.esc_html($employer).'
                        		 Occupation:'.esc_html($occupation).'
                                 System Name:'.esc_html($system_name).'
                                 Home Address:'.esc_html($home_address).'
                                 City:'.esc_html($city).'
                                 State:'.esc_html($state).'
                                 Zipcode:'.esc_html($zipcode).'
                                 Email Address:'.esc_html($email).'
                                 Donation Type:'.esc_html($contribution).'
                                 Date Donated:'.esc_html( date('Y-m-d H:i:s') ).'
                                 Membership Year:'.esc_html($year).'
                				 Amount Donated:'.esc_html($donation->payment_amount).'
                                 Payment Currency:'.esc_html($donation->payment_currency).'
                                 Payer ID:'.esc_html($donation->payer_id).'
                                 Transaction ID:'.esc_html($donation->txn_id).'';
                    
                    $form = '<table class="form-table donation-table">
                    			<tr>
                    				<th>Name: </th>
                    				<td>'.esc_html($donation->donor_name).'</td>
                    				<th>Employer: </th>
                    				<td>'.esc_html($donation->donor_employer).'</td>
                    			</tr>
                    			<tr>
                    				<th>Occupation: </th>
                    				<td>'.esc_html($donation->donor_occupation).'</td>
                    				<th>System Name: </th>
                    				<td>'.esc_html($donation->donor_system_name).'</td>
                    			</tr>
                    			<tr>
                    				<th>Home Address: </th>
                    				<td>'.esc_html($donation->donor_home_address).'</td>
                    				<th>City: </th>
                    				<td>'.esc_html($donation->donor_city).'</td>
                    			</tr>
                    			<tr>
                    				<th>State: </th>
                    				<td>'.esc_html($donation->donor_state).'</td>
                    				<th>Zipcode: </th>
                    				<td>'.esc_html($donation->donor_zip).'</td>
                    			</tr>
                    			<tr>
                    				<th>Email Address: </th>
                    				<td>'.esc_html($donation->donor_email).'</td>
                    				<th>Donation Type: </th>
                    				<td>'.esc_html($donation->donor_donation_type).'</td>
                    			</tr>
                    			<tr>
                    				<th>Date Donated: </th>
                    				<td>'.esc_html(date('Y-m-d h:i a', strtotime($donation->donor_date_donated))).'</td>
                    				<th>Membership Year: </th>
                    				<td>'.esc_html($donation->donor_membership_year).'</td>
                    			</tr>
                    			<tr>
                    				<th>Amount Donated: </th>
                    				<td>'.esc_html($donation->payment_amount).'</td>
                    				<th>Payment Currency: </th>
                    				<td>'.esc_html($donation->payment_currency).'</td>
                    			</tr>
                    			<tr>
                    				<th>Payer ID: </th>
                    				<td>'.esc_html($donation->payer_id).'</td>
                    				<th>Transaction ID: </th>
                    				<td>'.esc_html($donation->txn_id).'</td>
                    			</tr>
                    		</table>';
                     
					$message  = "A donation has just been made by {$donation->donor_name} with a transaction ID of: {$txn_id}.  <br/>";
					$message .= "Please visit this <a href='{$donor_single_view}'>link</a> to see all the donor details.";
                    $message .= $form;

					wp_mail( $notification_email, $subject, $message, $headers);
                    
                    // php mailer setup
                    
                    $instance->mail->SetFrom( $notification_email, 'Paypal Donation Form' );
                    $instance->mail->AddReplyTo( ' noreply@pd-donation.com', 'pd-donation' );
                    
                    $instance->mail->AddAddress( $notification_email, "Email");
                    $instance->mail->AddCC( $notification_email, "Email");
                  
                    $instance->mail->Subject = "[{$txn_id}] New Donation";
                    $instance->mail->AltBody = $form_alt;
                    
                    $instance->mail->IsHTML(true);        
                    $instance->mail->MsgHTML( $message );
                    
                    if( !$instance->mail->Send() ){
                        $response['send'] = 'email send';
                    } else {
                        $response['error-send'] = $instance->mail->ErrorInfo;
                    }   
				}


			} else if (strcmp ($res, "INVALID") == 0) {

				// log for manual investigation
				// Add business logic here which deals with invalid IPN messages

				error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, $logfile);

			}

			exit;
		}
	}
}