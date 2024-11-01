<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// encrypt user and orders function in WP GDPR
function woogdprAddon_wpEncryptUser( $user_id ) {

	global $wpdb;
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

	$key = WPEnc_get_key();

	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user_id" );

	foreach ( $usermetas as $usermeta ) 
	{
		$WPEnc_userMetaKeys = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_phone', 'billing_email', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state');
		if ( in_array($usermeta->meta_key, $WPEnc_userMetaKeys)) {
        		$wpdb->update($usermeta_table, array('meta_value' => WPEnc_safeEncrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
		}
	}

	$postmetas = $wpdb->get_results( "SELECT post_id FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user_id" );

	foreach($postmetas as $postmeta) {
		$order_id = $postmeta->post_id;
		$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
		foreach ( $postorders as $postorder ) 
		{
			$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
			if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
        			$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeEncrypt($postorder->meta_value, $key)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
			}
		}
	}

}

// decrypt user and orders function in WP GDPR
function woogdprAddon_wpDecryptUser( $user_id ) {

	global $wpdb;
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

	$key = WPEnc_get_key();

	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user_id" );

	foreach ( $usermetas as $usermeta ) 
	{
		$WPEnc_userMetaKeys = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_phone', 'billing_email', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state');
		if ( in_array($usermeta->meta_key, $WPEnc_userMetaKeys)) {
        	$wpdb->update($usermeta_table, array('meta_value' => WPEnc_safeDecrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
		}
	}

	$postmetas = $wpdb->get_results( "SELECT post_id FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user_id" );

	foreach($postmetas as $postmeta) {
		$order_id = $postmeta->post_id;
		$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
		foreach ( $postorders as $postorder ) 
		{
			$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
			if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
    			$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeDecrypt($postorder->meta_value, $key)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
			}
		}
	}

}

// decrypt order data after successfully placed
function woogdprAddon_afterOrderRegister($order_id) {

	global $wpdb;
	$postmeta_table = $wpdb->prefix . 'postmeta';

	$isUserOrder = get_post_meta( $order_id, '_customer_user', true );

	if (!$isUserOrder) {
		$key = WPEnc_get_key();

		$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
		foreach ( $postorders as $postorder ) 
		{
			$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
			if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
    			$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeEncrypt($postorder->meta_value, $key)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
			}
		}
	}
}

function woogdprAddon_registrationUserOrder( $user_id ) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

	$isUserOrder = $wpdb->get_var( "SELECT COUNT(*) FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user_id" );

	if($isUserOrder) {

		$key = WPEnc_get_key();

		$user = $wpdb->get_row( "SELECT * FROM $user_table WHERE ID = $user_id" );

		$user_login 	=  WPEnc_safeDecrypt($user->user_login, $key);
		$user_pass 		=  WPEnc_safeDecrypt($user->user_pass, $key);
		$user_nicename 	=  WPEnc_safeDecrypt($user->user_nicename, $key);
		$user_email 	=  WPEnc_safeDecrypt($user->user_email, $key);
		$user_url 		=  WPEnc_safeDecrypt($user->user_url, $key);
		$display_name 	=  WPEnc_safeDecrypt($user->display_name, $key);

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
			array('id' => $user_id)
		);

		add_user_meta( $user_id, 'user_logged_in_successfully', 1);

		$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user_id" );

		foreach ( $usermetas as $usermeta ) 
		{
			$WPEnc_userMetaKeys = array('nickname', 'description');
			if ( in_array($usermeta->meta_key, $WPEnc_userMetaKeys)) {
        		$wpdb->update($usermeta_table, array('meta_value' => WPEnc_safeDecrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
			}
		}
	}
}

// hook for WPEnc_userLoginFailedHookFunction in WP GDPR
add_action( 'WPEnc_wpUserEncryptLoginFailed', 'woogdprAddon_wpEncryptUser', 10, 1 );

// hook for WPEnc_userLogoutHookFunction in WP GDPR
add_action( 'WPEnc_wpUserEncryptLogout', 'woogdprAddon_wpEncryptUser', 10, 1 );

// hook for wp_authenticate in WP GDPR
add_action( 'WPEnc_wpUserDecrypt', 'woogdprAddon_wpDecryptUser', 10, 1 );

// hook for WPEnc_decryptUserData in WP GDPR
add_action( 'WPEnc_wpAllUsersDecrypt', 'woogdprAddon_wpDecryptUser', 10, 1 );

// hook After New Woocommerce Order Registrtion
add_action( 'woocommerce_thankyou', 'woogdprAddon_afterOrderRegister',  10, 1  );

// New User Registration Hook
add_action( 'user_register', 'woogdprAddon_registrationUserOrder', 12, 1 );