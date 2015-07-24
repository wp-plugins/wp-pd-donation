<?php
/*
Plugin Name: WP PD
Description: Wordpress Paypal Donate Plugin
Version: 1.0
Author: Charly Capillanes
Author URI: http://plontacreative.com/wp-plugins
*/


require 'includes/constants.php';

if( !class_exists('PHPMailer') ) {
	require_once ABSPATH . WPINC . '/class-phpmailer.php';
    //require GDONATE_INCLUDES . '/class.phpmailer.php';
}

require GDONATE_INCLUDES . '/DonationCtrl.php';

require GDONATE_INCLUDES . '/DonationModel.php';

require GDONATE_INCLUDES . '/DonationView.php';

class GDonate {
	
	public function __construct() {
		add_action( 'admin_menu',  array('DonationCtrl', 'registerMenu'));		

		add_action( 'admin_init', array('DonationCtrl', 'handlePostRequest') );
		
		add_action( 'admin_init', array('DonationCtrl', 'handleGetRequest') );
		
		add_action('admin_enqueue_scripts', array('DonationCtrl', 'loadAdminScripts'));

		//add_action('wp', array('DonationCtrl', 'handleFrontPostRequest'));

		add_action('wp', array('DonationCtrl', 'shortcodeExistsCheck'));
		

		add_action('wp_ajax_register_pac_donor', array('DonationCtrl', 'registerPacDonor'));
		add_action('wp_ajax_nopriv_register_pac_donor', array('DonationCtrl', 'registerPacDonor'));
        
        add_action('wp_ajax_register_pac_donor_email', array('DonationCtrl', 'register_pac_donor_email'));
		add_action('wp_ajax_nopriv_register_pac_donor_email', array('DonationCtrl', 'register_pac_donor_email'));

		add_action('wp', array('DonationCtrl', 'ipnListener'));

		add_shortcode( 'pd_donate', array('DonationCtrl', 'donateShortcode') );
	}

	public static function install() {
		global $wpdb;

		$table = $wpdb->prefix . "donors";

		$sql = "CREATE TABLE $table (
			id int(9) NOT NULL AUTO_INCREMENT,
			donor_name varchar(150) NOT NULL,
			donor_employer varchar(150) NOT NULL,
			donor_occupation varchar(200) NOT NULL,
			donor_system_name varchar(200) NOT NULL,
			donor_home_address varchar(200) NOT NULL,
			donor_city varchar(100) NOT NULL,
			donor_state varchar(100) NOT NULL,
			donor_zip varchar(20) NOT NULL,
			donor_email varchar(150) NOT NULL,
			donor_donation_type smallint(2) NOT NULL,
			donor_membership_year YEAR(4) NOT NULL,
			donor_date_donated datetime NOT NULL,
			donate_status enum('paid', 'unpaid') DEFAULT 'unpaid',
			donate_message text NOT NULL,
			payment_amount decimal(7,2) NOT NULL,
			payment_currency VARCHAR(15) NOT NULL,
			payer_id varchar(100) NOT NULL,
			txn_id varchar(100) NOT NULL,
			PRIMARY KEY  (id)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dBDelta( $sql );

		add_option( 'donation_options', array(
				'donate_page'		=> 0,
				'thankyou_page'		=> 0,
				'paypal_email'		=> '',
				'use_live_pp_env'	=> false,
				'notification_email' => ''
			) );
	}

	public static function deactivate() {

	}
}

register_activation_hook( __FILE__, array('GDonate', 'install') );

register_deactivation_hook( __FILE__, array('GDonate', 'deactivate') );

$gemc_donate = new GDonate();

