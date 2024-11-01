<?php
/*
Plugin Name: Woocommerce Addon for WP GDPR Data Protection
Plugin URI: http://www.codemenschen.at
Description: Woocommerce Add-on for WP GDPR Data Protection
 
Author: codemenschen
Author URI: http://www.codemenschen.at/
Version: 1.0.0
License: GPL2
*/

/*  Copyright 2018 Telberia

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

// plugin variable: woogdprAddon

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

// check to make sure WP GDPR Data Protection is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'wp-gdpr-data-protection/wp-gdpr-data-protection.php' ) ) {
	register_activation_hook( __FILE__, 'woogdprAddon_activate' );
	register_deactivation_hook( __FILE__, "woogdprAddon_deactivate" );
	register_uninstall_hook( __FILE__, "woogdprAddon_uninstall" );

	function woogdprAddon_activate() {

		global $wpdb;

		$fHash = WPEnc_get_key();
	
		$user_table = $wpdb->prefix . 'users';
		$usermeta_table = $wpdb->prefix . 'usermeta';
		$postmeta_table = $wpdb->prefix . 'postmeta';

		$currentuser = wp_get_current_user();

		$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

		foreach ( $users as $user ) 
		{
			$ID 		=  $user->ID;
			$user_login 	=  WPEnc_safeDecrypt($user->user_login, $fHash);
			$user_pass 	=  WPEnc_safeDecrypt($user->user_pass, $fHash);
			$user_nicename 	=  WPEnc_safeDecrypt($user->user_nicename, $fHash);
			$user_email 	=  WPEnc_safeDecrypt($user->user_email, $fHash);
			$user_url 	=  WPEnc_safeDecrypt($user->user_url, $fHash);
			$display_name 	=  WPEnc_safeDecrypt($user->display_name, $fHash);

			$wpdb->update(
				$user_table,
				array( 
					'user_login' 	=> $user_login,
					'user_pass'	=> $user_pass,
					'user_nicename' => $user_nicename,
					'user_email' 	=> $user_email,
					'user_url' 	=> $user_url,
					'display_name'	=> $display_name,
				),
				array('id' => $ID)
			);

			$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $ID" );

			foreach ( $usermetas as $usermeta ) 
			{
				$WPEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
				if ( in_array($usermeta->meta_key, $WPEnc_userMetaKeys)) {
        			$wpdb->update($usermeta_table, array('meta_value' => WPEnc_safeDecrypt($usermeta->meta_value, $fHash)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
				}
			}
		}

		$users = $wpdb->get_results( "SELECT * FROM $user_table" );

		
		$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table WHERE meta_key IN ('nickname', 'first_name', 'last_name', 'description', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_phone', 'billing_email', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state')" );

		$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table WHERE meta_key IN ('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index')" );

		foreach ( $users as $user ) 
		{
			$ID 		=  $user->ID;
			$user_login 	=  WPEnc_safeEncrypt($user->user_login, $fHash);
			$user_pass 	=  WPEnc_safeEncrypt($user->user_pass, $fHash);
			$user_nicename 	=  WPEnc_safeEncrypt($user->user_nicename, $fHash);
			$user_email 	=  WPEnc_safeEncrypt($user->user_email, $fHash);
			$user_url 	=  WPEnc_safeEncrypt($user->user_url, $fHash);
			$display_name 	=  WPEnc_safeEncrypt($user->display_name, $fHash);

			$wpdb->update(
				$user_table,
				array( 
					'user_login' 	=> $user_login,
					'user_pass'	=> $user_pass,
					'user_nicename' => $user_nicename,
					'user_email' 	=> $user_email,
					'user_url' 	=> $user_url,
					'display_name'	=> $display_name,
				),
				array('id' => $ID)
			);
			update_user_meta( $ID, 'user_logged_in_successfully', 0);
		}

		foreach ( $usermetas as $usermeta ) 
		{
			$umeta_id 	=  $usermeta->umeta_id;
			$meta_value 	=  WPEnc_safeEncrypt($usermeta->meta_value, $fHash);

			$wpdb->update(
				$usermeta_table,
				array( 
					'meta_value' => $meta_value,
				),
				array('umeta_id' => $umeta_id, 'meta_key' => $usermeta->meta_key)
			);
		}

		foreach ( $postmetas as $postmeta ) 
		{
			$meta_id 	=  $postmeta->meta_id;
			$meta_value 	=  WPEnc_safeEncrypt($postmeta->meta_value, $fHash);

			$wpdb->update(
				$postmeta_table,
				array( 
					'meta_value' => $meta_value,
				),
				array('meta_id' => $meta_id, 'meta_key' => $postmeta->meta_key)
			);
		}
	}

	function woogdprAddon_deactivate() {
		delete_option("wpEncryptionOrderEncryptStatus");
	}

	function woogdprAddon_uninstall() {
	}

	include_once('inc/wpenc-action.php');

	// admin includes
	if (is_admin()) {
		include_once('inc/decryptOrderData.php');
	}
} else {
	// give warning if WP GDPR Data Protection is not active
	function woogdprAddon_admin_notice() {
		?>
		<div class="error">
			<p><?php _e( '<b>WP GDPR Data Protection - Woocommrce Addon:</b> WP GDPR Data Protection is not installed and / or active! Please install <a target="_blank" href="https://wordpress.org/plugins/wp-gdpr-data-protection/">WP GDPR Data Protection</a>.', 'woogdprAddon' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'woogdprAddon_admin_notice' );
}