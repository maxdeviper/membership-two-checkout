<?php

class MS_Gateway_Two_Checkout_View_Button extends MS_View {

	public function to_html() {
		$fields = $this->prepare_fields();
		$subscription = $this->data['ms_relationship'];
		$invoice = $subscription->get_current_invoice();
		$member = MS_Model_Member::get_current_member();
		$gateway = $this->data['gateway'];

		//$action_url = MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER );
		$action_url = apply_filters(
			'ms_gateway_two_checkout_view_button_form_action_url',
			$gateway->get_checkout_url() //$action_url
		);
		if ( 0 === $invoice->total ) {
			$action_url = null;
		}

		$row_class = 'gateway_' . $gateway->id;
		if ( ! $gateway->is_live_mode() ) {
			$row_class .= ' sandbox-mode';
		}
		
		ob_start();
		?>

        <!-- <script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"

            <?php
            // foreach ( $two_checkout_data as $key => $value ) {
            // 	printf(
            // 		'data-%s="%s" ',
            // 		esc_attr( $key ),
            // 		esc_attr( $value )
            // 	);
            // }
            ?>
        ></script> -->
		<form id="membership-form" action="<?php echo esc_url( $action_url ); ?>" method="post">



			<?php
				foreach ( $fields as $field ) {
					MS_Helper_Html::html_element( $field );
				}
			?>
		</form>

		<?php
		$payment_form = apply_filters(
			'ms_gateway_form',
			ob_get_clean(),
			$gateway,
			$invoice,
			$this
		);

		ob_start();
		?>
		<tr class="<?php echo esc_attr( $row_class ); ?>">
			<td class="ms-buy-now-column" colspan="2">
				<?php echo $payment_form; ?>
			</td>
		</tr>
		<?php
		$html = ob_get_clean();

		$html = apply_filters(
			'ms_gateway_button-' . $gateway->id,
			$html,
			$this
		);

		$html = apply_filters(
			'ms_gateway_button',
			$html,
			$gateway->id,
			$this
		);

		return $html;
	}

	private function prepare_fields() {
		$gateway = $this->data['gateway'];
		$subscription = $this->data['ms_relationship'];
		$membership = $subscription->get_membership();
        $invoice = $subscription->get_current_invoice();
		$member = $subscription->get_member();

		$fields = array(

            'sid' => array(
                'id' => 'sid',
                'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
                'value' => $gateway->seller_id(),
            ),
			'mode' => array(
				'id' => 'mode',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => '2CO',
			),
			'name' => array(
				'id' => 'li_0_name',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $membership->name,
			),
			
			// 'recurrence' => array(
			// 	'id' => 'li_0_recurrence',
			// 	'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			// 	'value' => '1 Month',
			// ),
			'tangible' => array(
				'id' => 'li_0_tangible',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => 'N',
			),

			'skip_landing' => array(
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'id' => 'skip_landing',
				'value' => '1',
			),
			'user_id' => array(
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'id' => 'user_id',
				'value' => $member->id,
			),
			'merchant_order_id' => array(
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'id' => 'merchant_order_id',
				'value' => $invoice->id,
			),

            'price' => array(
				'id' => 'li_0_price',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $invoice->total,
			),
			'email' => array(
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'id' => 'email',
				'value' => $member->email,
			),
			'ms_relationship_id' => array(
				'id' => 'ms_relationship_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $subscription->id,
			),
			'return_url' => array(
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'id' => 'x_receipt_link_url',
				'value' => esc_url_raw(
					add_query_arg(
						array( 'ms_relationship_id' => $subscription->id ),
						MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REG_COMPLETE, false )
					)
				),
			),
		);
		if ( MS_Model_Membership::PAYMENT_TYPE_RECURRING == $membership->payment_type ) {
				#'li_0_reccurance' = '2 days'   // Can use # Week / # Month / # Year
				#'li_0_duration' = 'Forever'    // Same as _recurrence, with additional "Forever" option
				$period_type = MS_Helper_Period::get_period_value(
					$membership->pay_cycle_period,
					'period_type'
				);
				$period_type = strtoupper( $period_type[0] );
				$period_value = MS_Helper_Period::get_period_value(
					$membership->pay_cycle_period,
					'period_unit'
				);
				die(var_dump($membership->pay_cycle_period));
				// $period_value = MS_Helper_Period::validate_range(
				// 	$period_value,
				// 	$period_type
				// );
				$fields['recurrence'] = array(
					'id' => 'li_0_recurrence',
					'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' =>implode(' ', $membership->pay_cycle_period),
				);
		}
		if ( false !== strpos( $gateway->pay_button_url, '://' ) ) {
			$fields['submit'] = array(
				'id' => 'submit-payment',
				'type' => MS_Helper_Html::INPUT_TYPE_IMAGE,
				'value' => $gateway->pay_button_url,
			);
		} else {
			$fields['button'] = array(
				'id' => 'submit-payment',
				'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
				'value' => $gateway->pay_button_url
					? $gateway->pay_button_url
					: __( 'Signup using 2Checkout', 'membership-two-checkout' ),
			);
		}

		return $fields;
	}
}