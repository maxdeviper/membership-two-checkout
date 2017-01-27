<?php


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
require plugin_dir_path( __FILE__ ).'/class-ms-gateway-two-checkout.php';
$option_name = MS_Gateway_Two_Checkout::TWO_CHECKOUT_OPTION_KEY;
 
delete_option($option_name);