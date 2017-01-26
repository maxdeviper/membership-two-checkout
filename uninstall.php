<?php


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$option_name = 'paystack_membership_options';
 
delete_option($option_name);