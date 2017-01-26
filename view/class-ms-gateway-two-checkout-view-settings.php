<?php

class MS_Gateway_Two_Checkout_View_Settings extends MS_View {

	public function to_html() {
		$fields = $this->prepare_fields();
		$gateway = $this->data['model'];

		$msg = '<em>'.__(
			"@Checkout Forms API KEYS Settings!\n Your webhook URL is: http://www.ibiene.com/ms-payment-return/".MS_Gateway_Two_Checkout::ID, 'membership-two-checkout'
		) .
		'</em>';

		ob_start();
		// Render tabbed interface.
		?>
		<form class="ms-gateway-settings-form ms-form">
			<?php
			MS_Helper_Html::settings_box_header( '', $msg );
			foreach ( $fields as $field ) {
				MS_Helper_Html::html_element( $field );
			}
			MS_Helper_Html::settings_box_footer();
			?>
		</form>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	protected function prepare_fields() {
		$gateway = $this->data['model'];
		$action = MS_Controller_Gateway::AJAX_ACTION_UPDATE_GATEWAY;
		$nonce = wp_create_nonce( $action );

		$fields = array(
			'mode' => array(
				'id' => 'mode',
				'title' => __( 'Mode', 'membership-two-checkout' ),
				'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
				'value' => $gateway->mode,
				'field_options' => $gateway->get_mode_types(),
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),

			'test_secret_key' => array(
				'id' => 'test_secret_key',
				'title' => __( 'API Test Secret Key', 'membership-two-checkout' ),
				'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
				'value' => $gateway->test_secret_key,
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),

			'test_public_key' => array(
				'id' => 'test_public_key',
				'title' => __( 'API Test Public Key', 'membership-two-checkout' ),
				'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
				'value' => $gateway->test_public_key,
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),

			'live_secret_key' => array(
				'id' => 'live_secret_key',
				'title' => __( 'API Live Secret Key', 'membership-two-checkout' ),
				'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
				'value' => $gateway->live_secret_key,
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),

			'live_public_key' => array(
				'id' => 'live_public_key',
				'title' => __( 'API Live Public Key', 'membership-two-checkout' ),
				'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
				'value' => $gateway->live_public_key,
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),

			'pay_button_url' => array(
				'id' => 'pay_button_url',
				'title' => apply_filters(
					'ms_translation_flag',
					__( 'Payment button label', 'membership-two-checkout' ),
					'gateway-button' . $gateway->id
				),
				'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
				'value' => $gateway->pay_button_url,
				'class' => 'ms-text-large',
				'ajax_data' => array( 1 ),
			),
		);

		// Process the fields and add missing default attributes.
		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['ajax_data'] ) ) {
				$fields[ $key ]['ajax_data']['field'] = $fields[ $key ]['id'];
				$fields[ $key ]['ajax_data']['_wpnonce'] = $nonce;
				$fields[ $key ]['ajax_data']['action'] = $action;
				$fields[ $key ]['ajax_data']['gateway_id'] = $gateway->id;
			}
		}

		return apply_filters(
			'ms_gateway_two_checkout_view_settings_prepare_fields',
			$fields
		);
	}
}