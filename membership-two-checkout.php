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

/**
 * filters
 *
 *
 * ms_gateway_two_checkout_two_checkout_get_transaction_ref
 *
 *
 * 
 */

add_action( 'ms_init', 'two_checkout_register' );
 
// add_action('wp_ajax_update_membership', 'add_two_checkout_plan_code');

/**
 * filter for adding custom payment periods
 */
//add_filter('ms_helper_period_get_periods', 'add_payments_period');