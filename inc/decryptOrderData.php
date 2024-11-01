<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* Decrypt All Userdata Menu Function 
*/
function woogdprAddon_decryptOrderDataMenu() {

	add_users_page('Decrypt All Order Data', 'Decrypt All Order Data', 'manage_options', 'wpenc-decrypt-orderdata', 'woogdprAddon_decryptOrderData');
}
add_action( 'admin_menu', 'woogdprAddon_decryptOrderDataMenu', 2 );

/**
* Decrypt All Userdata to Original
*/
function woogdprAddon_decryptOrderData() {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

   	if ( !current_user_can( 'manage_options' ) )
   	{
      		wp_die( 'You are not allowed to be on this page.' );
   	} 

   	$notice = $secretKey = '';

   	if ( isset($_POST['secretKey']) && isset($_POST['mode']) )
   	{
      		// Check that nonce field
   	  	wp_verify_nonce( $_POST['secret_key_for_decrypt_nonce_verify'], 'secret_key_for_decrypt_nonce_verify' );

		$secretKey= $_POST['secretKey'];

		$fHash = WPEnc_get_key();

		$key = base64_encode($fHash);

		if($key != $secretKey) {
			$notice=1;
		} else {
			if($_POST['mode'] == 'decrypt') {
				$directorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = 0" );

				foreach($directorders as $postmeta) {
					$order_id = $postmeta->post_id;
					$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
					foreach ( $postorders as $postorder ) 
					{
						$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
						if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
        					$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeDecrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
						}
					}
				}

				$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

				foreach ( $users as $user ) 
				{
					$ID  =  $user->ID;
					$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user->ID" );

					foreach($postmetas as $postmeta) {
						$order_id = $postmeta->post_id;
						$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
						foreach ( $postorders as $postorder ) 
						{
							$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
							if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
        						$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeDecrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
							}
						}
					}
				}
				add_option( 'wpEncryptionOrderEncryptStatus', 'true', '', 'yes' );
				$notice=2;
			} else {

				$directorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = 0" );

				foreach($directorders as $postmeta) {
					$order_id = $postmeta->post_id;
					$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
					foreach ( $postorders as $postorder ) 
					{
						$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
						if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
        					$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeEncrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
						}
					}
				}

				$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

				foreach ( $users as $user ) 
				{
					$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user->ID" );

					foreach($postmetas as $postmeta) {
						$order_id = $postmeta->post_id;
						$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
						foreach ( $postorders as $postorder ) 
						{
							$WPEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
							if ( in_array($postorder->meta_key, $WPEnc_postMetaKeys)) {
        						$wpdb->update($postmeta_table, array('meta_value' => WPEnc_safeEncrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
							}
						}
					}
				}
				delete_option( 'wpEncryptionOrderEncryptStatus');
				$notice=3;
			}

		}

   	}
	?>

	<div class="wrap">
		<h1>Decrypt All Order Data</h1>
		<p>This page will decrypt your all orders data to normal.</p>
		<?php if($notice==1) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong>Your secret key is not matched. Please try again.</strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
		<?php } else if($notice==2) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong>All order data is successfully decrypted.</strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
		<?php } else if($notice==3) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong>All order data is successfully encrypted.</strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
		<?php } ?>
		<?php if(get_option('wpEncryptionOrderEncryptStatus')) { ?>
			<h3 style="color: #f00;">Note: Data is already decrypted.</h3>
		<?php } ?>
		<form method="post" name="decrypt_data_after_key_verify" action="<?php echo admin_url( 'users.php' ); ?>?page=wpenc-decrypt-orderdata">
			<?php $nonce = wp_create_nonce( 'secret_key_for_decrypt_nonce_verify' ); ?>
			<input type="hidden" name="secret_key_for_decrypt_nonce_verify" value="<?php echo($nonce); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="email">Mode <span class="description">(required)</span></label>
						</th>
						<td>
							<label><input name="mode" type="radio" value="decrypt" checked> Decrypt Order Data</label><br>
							<label><input name="mode" type="radio" value="encrypt" > Encrypt Order Data</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="email">Your Secret Key <span class="description">(required)</span></label>
						</th>
						<td>
							<input name="secretKey" type="password" id="secretKey" value="<?php echo $secretKey; ?>" class="regular-text" aria-required="true" required="required">
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Decrypt All User Data"></p>
		</form>
	</div>
<?php
}