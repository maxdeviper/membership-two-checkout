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
			'https://www.2checkout.com/checkout/purchase' //$action_url
		);

		$row_class = 'gateway_' . $gateway->id;
		if ( ! $gateway->is_live_mode() ) {
			$row_class .= ' sandbox-mode';
		}
		/**
		 * Users can change details (like the title or description) of the
		 * 2Checkout checkout popup.
		 *
		 * @since  1.0.0
		 * @var array
		 */
		$two_checkout_data = array();
		$two_checkout_data = apply_filters(
			'ms_gateway_two_checkout_form_details',
			$two_checkout_data,
			$invoice
		);

		$two_checkout_data['email'] = $member->email;
                // add subscription data to two_checkout. To be used for accessing user's membership2 subscription
                $metadata = array(
                  'custom_fields' => array(
                      array(                          
                        "display_name" => "Subscription_ID",
                        "variable_name" => "subscription_id",
                        "value" => $subscription->id,
                      ),
                      array(                          
                        "display_name" => "Member_ID",
                        "variable_name" => "member_id",
                        "value" => $member->id,
                      )
                  )  
                );
		$two_checkout_data['metadata'] = json_encode($metadata);
		$two_checkout_data['plan'] = get_option( MS_Gateway_Two_Checkout::TWO_CHECKOUT_OPTION_KEY)[$subscription->get_membership()->id]['code'] ;
		$two_checkout_data['key'] = $gateway->publishable_key();
		$two_checkout_data['currency'] = apply_filters( 'ms_gateway_two_checkout_currency_to_use', 'NGN', $invoice);
		$two_checkout_data['ref'] = apply_filters( 'ms_gateway_two_checkout_get_transaction_ref', mt_rand(1000,9999));
		$two_checkout_data['amount'] = apply_filters( 'ms_gateway_two_checkout_amount_to_use', $invoice->total, $invoice); // Amount in kobo.

                
        $two_checkout_data = apply_filters(
			'ms_gateway_two_checkout_form_details_after',
			$two_checkout_data,
			$invoice
		);
		ob_start();
		?>
		<form id="membership-form" action="<?php echo esc_url( $action_url ); ?>" method="post">
			<?php
				foreach ( $fields as $field ) {
					MS_Helper_Html::html_element( $field );
				}
			?>
            <form action='' method='post'>
                <input type='hidden' name='sid' value='1303908' />
                <input type='hidden' name='mode' value='2CO' />
                <input type='hidden' name='li_0_type' value='product' />
                <input type='hidden' name='li_0_name' value='invoice123' />
                <input type='hidden' name='li_0_price' value='25.99' />
                <input type='hidden' name='li_0_tangible' value='Y' />
                <input type='hidden' name='li_1_type' value='shipping' />
                <input type='hidden' name='li_1_name' value='Express Shipping' />
                <input type='hidden' name='li_1_price' value='13.99' />
                <input type='hidden' name='card_holder_name' value='Checkout Shopper' />
                <input type='hidden' name='street_address' value='123 Test Address' />
                <input type='hidden' name='street_address2' value='Suite 200' />
                <input type='hidden' name='city' value='Columbus' />
                <input type='hidden' name='state' value='OH' />
                <input type='hidden' name='zip' value='43228' />
                <input type='hidden' name='country' value='USA' />
                <input type='hidden' name='ship_name' value='Checkout Shopper' />
                <input type='hidden' name='ship_street_address' value='123 Test Address' />
                <input type='hidden' name='ship_street_address2' value='Suite 200' />
                <input type='hidden' name='ship_city' value='Columbus' />
                <input type='hidden' name='ship_state' value='OH' />
                <input type='hidden' name='ship_zip' value='43228' />
                <input type='hidden' name='ship_country' value='USA' />
                <input type='hidden' name='email' value='example@2co.com' />
                <input type='hidden' name='phone' value='614-921-2450' />
                <input name='submit' type='submit' value='Checkout' />
            </form>
        <script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"

			<?php
				 // foreach ( $two_checkout_data as $key => $value ) {
				 // 	printf(
				 // 		'data-%s="%s" ',
				 // 		esc_attr( $key ),
				 // 		esc_attr( $value )
				 // 	);
				 // }
			?>
		></script>
		</form>
			 
		<script>
		  function payWithTwoCheckout(){
		    var handler = two_checkoutPop.setup({
		      key: '<?php echo $two_checkout_data['key']; ?>',
		      email: '<?php echo $two_checkout_data['email']; ?>',
		      plan: '<?php echo $two_checkout_data['plan']; ?>',
		      currency: '<?php echo $two_checkout_data['currency']; ?>',
		      amount: <?php echo $two_checkout_data['amount']; ?>,
		      ref: '<?php echo $two_checkout_data['ref']; ?>',
		      metadata: <?php echo $two_checkout_data['metadata']; ?>,
		      callback: function(response){
		          var form = document.getElementById('membership-form');
		          var input = document.createElement("input");
		          input.setAttribute('type', 'hidden');
		          input.setAttribute('name', 'transaction_ref');
		          input.setAttribute('value', response.reference);
		          form.appendChild(input);
		          form.submit();
		      },
		      onClose: function(){
		         
		      }
		    });
		    handler.openIframe();
		  }
		  document.getElementById('submit-payment').addEventListener("click", payWithTwoCheckout);
		</script>
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

		$fields = array(
			'_wpnonce' => array(
				'id' => '_wpnonce',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => wp_create_nonce( "{$gateway->id}_{$subscription->id}" ),
			),
			'gateway' => array(
				'id' => 'gateway',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $gateway->id,
			),
			'ms_relationship_id' => array(
				'id' => 'ms_relationship_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $subscription->id,
			),
			'step' => array(
				'id' => 'step',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['step'],
			),
		);

		if ( false !== strpos( $gateway->pay_button_url, '://' ) ) {
			$fields['submit'] = array(
				'id' => 'submit-payment',
				'type' => MS_Helper_Html::INPUT_TYPE_IMAGE,
				'value' => $gateway->pay_button_url,
			);
		} else {
			$fields['button'] = array(
				'id' => 'submit-payment',
				'type' => MS_Helper_Html::INPUT_TYPE_BUTTON,
				'value' => $gateway->pay_button_url
					? $gateway->pay_button_url
					: __( 'Signup', 'membership-two-checkout' ),
				'onclick' => 'payWithTwoCheckout()',
			);
		}

		return $fields;
	}
}