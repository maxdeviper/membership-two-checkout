<?php

/**
 * Plugin name: Membership 2 2Checkout Gateway.
 * 
 * @wordpress-plugin
 * Plugin URI:        www.opushive.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Opus Hive
 * Author URI:        www.opushive.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       membership-two-checkout
 */

require plugin_dir_path( __FILE__ ).'/class-ms-gateway-two-checkout.php';

function two_checkout_register( $api ) {
 	$api->register_payment_gateway( MS_Gateway_Two_Checkout::ID, 'MS_Gateway_two_checkout' );
}

function set_to_naira($default, $invoice){

	return 'NGN';
}
function convert_to_kobo($amount, $invoice)
{
	return ceil(abs( $amount * 100 ));
}

function two_checkout_subscription_code($membership_tab_payment_view, $membership)
{
	// $html = '';
	// $gateways = MS_Model_Gateway::get_gateways();
	// foreach ($gateways as $gateway) {
	// 	if ($gateway->id == MS_Gateway_two_checkout::ID && $gateway->active && $membership->can_use_gateway($gateway->id
	// 		)){
	// 		$html = '<label for="two_checkout_subscription_code">two_checkout Subscription code: </label><input type="text" value="">';
	// 	}
	// }
 	
 	//echo $html;
 	//
 	$two_checkout_options = get_option(MS_Gateway_Two_Checkout::TWO_CHECKOUT_OPTION_KEY, array());
 	$isset_option = isset($two_checkout_options[$membership->id]) && !empty($two_checkout_options[$membership->id]);
	$action = MS_Controller_Membership::AJAX_ACTION_UPDATE_MEMBERSHIP;
	$nonce = wp_create_nonce( $action );
 	$fields = array();
 	$gateways = MS_Model_Gateway::get_gateways();
	foreach ($gateways as $gateway) {
		if ($gateway->id == MS_Gateway_Two_Checkout::ID && $gateway->active && $membership->can_use_gateway($gateway->id
			)){
		 	$fields['two_checkout_subscription_code'] = array(
		 			'id' 		=> 'two_checkout_subscription_code',
		 			'name' 		=> 'two_checkout_subscription_code',
					'title' 	=> __('two_checkout Subscription Code', 'membership-two-checkout' ),
					'type' 		=> MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' 	=> $isset_option ? $two_checkout_options[$membership->id]['code'] : '',
					'class' 	=> 'ms-text-large',
					'ajax_data' => array(
						'field' => 'two_checkout_subscription_code',
						'_wpnonce' 		=> $nonce,
						'action' 		=> $action,
						'membership_id' => $membership->id,
						),
		 		);
			 $fields['two_checkout_subscription_amount'] = array(
			 			'id' 		=> 'two_checkout_subscription_amount',
			 			'name' 		=> 'two_checkout_subscription_amount',
						'title' 	=> __('two_checkout Subscription Amount', 'membership-two-checkout' ),
						'before'	=> 'NGN',
						'type' 		=> MS_Helper_Html::INPUT_TYPE_NUMBER,
						'value' 	=> $isset_option ? $two_checkout_options[$membership->id]['amount'] : '',
						'class' 	=> 'ms-text-medium',
						'ajax_data' => array(
							'field' => 'two_checkout_subscription_amount',
							'_wpnonce' 		=> $nonce,
							'action' 		=> $action,
							'membership_id' => $membership->id,
							),
			 		);
			 foreach ($fields as $field)
			 {
			 	
			 	MS_Helper_Html::html_element( $field);
			 }
		}
	}
}

function add_two_checkout_subscription_code()
{ 
        //options fields for each membership 
	$fields = [
		'two_checkout_subscription_code' => 'code',
		'two_checkout_subscription_amount' => 'amount',
	];
        // check if request contain parameters to be used
	if (
                !isset($_POST['field'])
                &&!isset($_POST['value'])
                &&!isset($_POST['membership_id'])
            ){
		return;
	}
        //check if the field parameter exist in our required parameters
	if (!in_array($_POST['field'], array_keys($fields))){
		return;
	}


        //set default values for fields
	$default = array('code' => null, 'amount' => null );
        
        //retrieve field key names
	$options = array(
		$fields[$_POST['field']] => $_POST['value']
	);
        //retrieve membership this is for
	$membership_id = $_POST['membership_id'];
        
        //get membership options from the database
	$two_checkout_options = get_option(MS_Gateway_Two_Checkout::TWO_CHECKOUT_OPTION_KEY, array($membership_id  => $default));
        
        //set the default to the value from the database
	if (array_key_exists($membership_id, $two_checkout_options)){
		
		$default = array_replace($default, $two_checkout_options[$membership_id]);
	}
        //set field values with new ones
        
	$two_checkout_options[$membership_id] = array_replace($default, $options);
        
        // update options
 	update_option( MS_Gateway_Two_Checkout::TWO_CHECKOUT_OPTION_KEY, $two_checkout_options);
 	// wp_die(); ;
}

/**
 * Adds payment periods for payment
 * 
 * @param array $periods an array of periods for membership payment period
 * @return array
 */
function add_payments_period($periods) {
    $periods['hours'] = __('hours', 'membership-two-checkout');
    return $periods;
}





/**
 * filters
 *
 * ms_gateway_two_checkout_currency_to_use  (args: $default = 'NGN', $invoice)
 *
 * ms_gateway_two_checkout_amount_to_use  (args: $default='$invoice->total, $invoice)
 *
 * ms_gateway_two_checkout_two_checkout_get_transaction_ref
 *
 *
 * 
 */
add_filter( 'ms_gateway_two_checkout_currency_to_use', 'set_to_naira',10, 2 );
add_filter( 'ms_gateway_two_checkout_amount_to_use', 'convert_to_kobo',10, 2 );
add_action( 'ms_init', 'two_checkout_register' );
add_action('ms_view_membership_tab_payment_form', 'two_checkout_subscription_code', 10, 2);
// add_filter('ms_view_membership_tab_payment_fields', 'two_checkout_membership_field',10, 1);
// 
add_action('wp_ajax_update_membership', 'add_two_checkout_subscription_code');

/**
 * filter for adding custom payment periods
 */
//add_filter('ms_helper_period_get_periods', 'add_payments_period');