<?php
/**
 * 
 * Process manual payments (Eg. check, bank transfer)
 *
 * Persisted by parent class MS_Model_Option. Singleton.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */

require_once plugin_dir_path( __FILE__ ).'../membership/membership.php';

$loader = new MS_Loader();

require_once plugin_dir_path( __FILE__ ).'view/class-ms-gateway-two-checkout-view-button.php';
require_once plugin_dir_path( __FILE__ ).'view/class-ms-gateway-two-checkout-view-settings.php';
require_once plugin_dir_path( __FILE__ ).'lib/2checkout/lib/Twocheckout.php';

class MS_Gateway_Two_Checkout extends MS_Gateway
{

    const ID = 'two_checkout';
    const SANDBOX_CHECKOUT_URL = 'https://sandbox.2checkout.com/checkout/purchase';
    const LIVE_CHECKOUT_URL = 'https://www.2checkout.com/checkout/purchase';
    const HASH_RESPONSE_CODE = 'Success';
    const HASH_RESPONSE_MESSAGE = 'Hash Matched';

    /**
     * Gateway singleton instance.
     *
     * @since  1.0.0
     * @var string $instance
     */
    public static $instance;

    /**
     * Payment information for customer.
     *
     * The payment procedures like bank account, agency, etc.
     *
     * @since  1.0.0
     * @var string $payment_info
     */
    protected $payment_info;

    /**
     * 2Checkout Secret word (Live).
     *
     *
     * @since  1.0.0
     * @var string $secret_word
     */
    protected $secret_word;

    /**
     * 2Checkout Secret word (Sandbox).
     *
     *
     * @since  1.0.0
     * @var string $test_secret_word
     */
    protected $test_secret_word;

    /**
     * 2Checkout Secret key (sandbox).
     *
     * @since  1.0.0
     * @var string $test_private_key
     */
    protected $test_private_key = '';

    /**
     * 2Checkout public key (sandbox).
     *
     * @since  1.0.0
     * @var string $test_publishable_key
     */
    protected $test_publishable_key = '';

    /**
     * 2Checkout Secret key (live).
     *
     * @since  1.0.0
     * @var string $private_key
     */
    protected $private_key = '';

    /**
     * 2Checkout public key (live).
     *
     * @since  1.0.0
     * @var string $publishable_key
     */
    protected $publishable_key = '';

    /**
     * 2Checkout seller id (sandbox).
     *
     * @var string $test_seller_id
     *
     */
    protected $test_seller_id = '';

    /**
     * 2Checkout seller id (live).
     *
     * @var string $seller_id
     *
     */
    protected $seller_id = '';

    /**
     * Option key used for saving 2Checkout subscription data
     * 
     */
    const TWO_CHECKOUT_OPTION_KEY = 'two_checkout_membership_options';

    /**
     * Hook to show payment info.
     * This is called by the MS_Factory
     *
     * @since  1.0.0
     */
    public function after_load() {
        parent::after_load();

        $this->id = self::ID;
        $this->name = __( '2Checkout Payment Gateway', 'membership-two-checkout' );
        $this->description = __( 'Using 2Checkout payments', 'membership-two-checkout' );
        $this->group = __( '2Checkout Payment', 'membership-two-checkout' );
        $this->manual_payment = false;// Recurring charged automatically
        $this->pro_rate = false;

        if ( $this->active ) {
            $this->add_action(
                'ms_controller_gateway_purchase_info_content',
                'purchase_info_content'
            );
        }
    }


    /**
     * Processes gateway IPN return.
     *
     * Overridden in child gateway classes.
     *
     * @since  1.0.0
     * @param bool|false|MS_Model_Transactionlog $log Optional. A transaction log item
     *         that will be updated instead of creating a new log entry.
     */
    public function handle_return() {
        $success = false;
        $exit = false;
        $redirect = false;
        $notes = '';
        $status = null;
        $external_id = null;
        $invoice_id = 0;
        $subscription_id = 0;
        $amount = 0;
        error_log(print_r($_REQUEST, true));
        if ( ! empty( $_POST['vendor_order_id'] ) && ! empty( $_POST['md5_hash'] ) ) {
            $invoice_id = intval( $_POST['vendor_order_id'] );
            $invoice = MS_Factory::load( 'MS_Model_Invoice', $invoice_id );

            $raw_hash = $_POST['sale_id'] . $this->seller_id() . $_POST['invoice_id'] . $this->secret_word();
            $md5_hash = strtoupper( md5( $raw_hash ) );

            if ( $md5_hash == $_POST['md5_hash']
                && ! empty( $_POST['message_type'] )
                && $invoice->id = $invoice_id
            ) {
                $subscription = $invoice->get_subscription();
                $membership = $subscription->get_membership();
                $member = $subscription->get_member();
                $subscription_id = $subscription->id;
                $external_id = $_POST['invoice_id'];
                $amount = (float) $_POST['invoice_list_amount'];

                switch ( $_POST['message_type'] ) {
                    case 'RECURRING_INSTALLMENT_SUCCESS':
                        $notes = 'Payment received';

                        // Not sure if the invoice was already paid via the
                        // INVOICE_STATUS_CHANGED message
                        if ( ! $invoice->is_paid() ) {
                            $success = true;
                        }
                        break;

                    case 'INVOICE_STATUS_CHANGED':
                        $notes = sprintf(
                            'Invoice was %s',
                            $_POST['invoice_status']
                        );

                        switch ( $_POST['invoice_status'] ) {
                            case 'deposited':
                                // Not sure if invoice was already paid via the
                                // RECURRING_INSTALLMENT_SUCCESS message.
                                if ( ! $invoice->is_paid() ) {
                                    $success = true;
                                }
                                break;

                            case 'declied':
                                $status = MS_Model_Invoice::STATUS_DENIED;
                                break;
                        }
                        break;

                    case 'RECURRING_STOPPED':
                        $notes = 'Recurring payments stopped manually';
                        $member->cancel_membership( $membership->id );
                        $member->save();
                        break;

                    case 'FRAUD_STATUS_CHANGED':
                        $notes = 'Ignored: Users Fraud-status was checked';
                        $success = null;
                        break;

                    case 'ORDER_CREATED':
                        $notes = 'Ignored: 2Checkout created a new order';
                        $success = null;
                        break;

                    case 'RECURRING_RESTARTED':
                        $notes = 'Ignored: Recurring payments started';
                        $success = null;
                        break;

                    case 'RECURRING_COMPLETE':
                        $notes = 'Ignored: Recurring complete';
                        $success = null;
                        break;

                    case 'RECURRING_INSTALLMENT_FAILED':
                        $notes = 'Ignored: Recurring payment failed';
                        $success = null;
                        $status = MS_Model_Invoice::STATUS_PENDING;
                        break;

                    default:
                        $notes = sprintf(
                            'Warning: Unclear command "%s"',
                            $_POST['message_type']
                        );
                        break;
                }

                $invoice->add_notes( $notes );
                $invoice->save();

                if ( $success ) {
                    $invoice->pay_it( $this->id, $external_id );
                } elseif ( ! empty( $status ) ) {
                    $invoice->status = $status;
                    $invoice->save();
                    $invoice->changed();
                }

                do_action(
                    'ms_gateway_2checkout_payment_processed_' . $status,
                    $invoice,
                    $subscription
                );
            } else {
                $reason = 'Unexpected transaction response';

                switch ( true ) {
                    case $md5_hash != $_POST['md5_hash']:
                        $reason = 'MD5 Hash invalid';
                        break;

                    case empty( $_POST['message_type'] ):
                        $reason = 'Message type is empty';
                        break;

                    case $invoice->id != $invoice_id:
                        $reason = sprintf(
                            'Expected invoice_id "%s" but got "%s"',
                            $invoice->id,
                            $invoice_id
                        );
                        break;
                }

                $notes = 'Response Error: ' . $reason;
                MS_Helper_Debug::log( $notes );
                MS_Helper_Debug::log( $response );
                $exit = true;
            }
        } else {
            // Did not find expected POST variables. Possible access attempt from a non PayPal site.

            $notes = 'Error: Missing POST variables. Identification is not possible.';
            MS_Helper_Debug::log( $notes );
            $redirect = home_url();
            $exit = true;
        }

        do_action(
            'ms_gateway_transaction_log',
            self::ID, // gateway ID
            'handle', // request|process|handle
            $success, // success flag
            $subscription_id, // subscription ID
            $invoice_id, // invoice ID
            $amount, // charged amount
            $notes // Descriptive text
        );

        if ( $redirect ) {
            wp_safe_redirect( $redirect );
            exit;
        }
        if ( $exit ) {
            exit;
        }

        do_action(
            'ms_gateway_2checkout_handle_return_after',
            $this
        );
    }

    public function get_checkout_url()
    {
        $url = null;

        if ( $this->is_live_mode() ) {
            $url = self::LIVE_CHECKOUT_URL;
        } else {
            $url = self::SANDBOX_CHECKOUT_URL;
        }

        return apply_filters(
            'ms_gateway_two_checkout_get_url',
            $url
        );
    }
    /**
     * Verify required fields.
     *
     * @since  1.0.0
     * @api
     *
     * @return boolean True if configured.
     */
    public function is_configured() {
        $key_pub = $this->publishable_key();
        $key_sec = $this->private_key();
        $seller_id = $this->seller_id();
        $secret_word = $this->secret_word();

        $is_configured = ! ( empty( $key_pub ) || empty( $key_sec ) || empty($seller_id ) || empty($secret_word));

        return apply_filters(
            'ms_gateway_public_is_configured',
            $is_configured
        );
    }

    public function seller_id()
    {
        $seller_id = null;

        if ( $this->is_live_mode() ) {
            $seller_id = $this->seller_id;
        } else {
            $seller_id = $this->test_seller_id;
        }

        return apply_filters(
            'ms_gateway_two_checkout_get_seller_id',
            $seller_id
        );
    }

    /**
     * Propagate membership cancelation to the gateway.
     *
     * Overridden in child classes.
     *
     * @since  1.0.0
     * @param MS_Model_Relationship $subscription The membership relationship.
     */
    public function cancel_membership( $subscription ) {
        do_action(
            'ms_gateway_cancel_membership',
            $subscription,
            $this
        );
    }

    public function secret_word()
    {
        $secret_word = null;

        if ( $this->is_live_mode() ) {
            $secret_word = $this->secret_word;
        } else {
            $secret_word = $this->test_secret_word;
        }

        return apply_filters(
            'ms_gateway_two_checkout_get_seller_id',
            $secret_word
        );
    }

    /**
     * Get two_checkout private key.
     *
     * @since  1.0.0
     * @internal The private key should not be used outside this object!
     *
     * @return string The two_checkout API secret key.
     */
    public function private_key() {
        $private_key = null;

        if ( $this->is_live_mode() ) {
            $private_key = $this->private_key;
        } else {
            $private_key = $this->test_private_key;
        }

        return apply_filters(
            'ms_gateway_two_checkout_get_private_key',
            $private_key
        );
    }

    /**
     * Get two_checkout public key.
     *
     * @since  1.0.0
     * @api
     *
     * @return string The two_checkout API publishable key.
     */
    public function publishable_key() {
        $publishable_key = null;

        if ( $this->is_live_mode() ) {
            $publishable_key = $this->publishable_key;
        } else {
            $publishable_key = $this->test_publishable_key;
        }

        return apply_filters(
            'ms_gateway_two_checkout_get_publishable_key',
            $publishable_key
        );
    }

    /**
     * Validate specific property before set.
     *
     * @since  1.0.0
     *
     * @access public
     * @param string $property The name of a property to associate.
     * @param mixed $value The value of a property.
     */
    public function __set( $property, $value ) {
        if ( property_exists( $this, $property ) ) {
            switch ( $property ) {
                case 'payment_info':
                    $this->$property = wp_kses_post( $value );
                    break;

                default:
                    parent::__set( $property, $value );
                    break;
            }
        }

        do_action(
            'ms_gateway_two_checkout_set_after',
            $property,
            $value,
            $this
        );
    }

}
