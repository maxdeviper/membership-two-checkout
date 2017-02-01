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
     * Processes purchase action.
     *
     * This function is called when a payment was made: We check if the
     * transaction was successful. If it was we call `$invoice->changed()` which
     * will update the membership status accordingly.
     *
     *
     * @throws Exception
     * @since  1.0.0
     * @param MS_Model_Relationship $subscription The related membership relationship.
     * @return mixed|MS_Model_Invoice|void
     */
    public function process_purchase( $subscription ) {
        do_action(
            'ms_gateway_process_purchase_before',
            $subscription,
            $this
        );
        $invoice = $subscription->get_current_invoice();
        $invoice->gateway_id = $this->id;
        $invoice->save();

        // The default handler only processes free subscriptions.
        if ( 0 == $invoice->total ) {
            $invoice->changed();
        } else {
            
            if (empty($_POST['transaction_ref'])){
                //throw new Exception('Transaction not verified');
            }
            $transaction_ref = filter_input(INPUT_POST,'transaction_ref', FILTER_SANITIZE_STRING);
            // use 2Checkout inputed reference if set
            if(isset($_POST['two_checkout-reference']) && !empty($_POST['two_checkout-reference'])){
                $transaction_ref = filter_input(INPUT_POST,'two_checkout-reference', FILTER_SANITIZE_STRING);
            }
            //@todo : Refactor to seperate class
            $verification_url = 'https://api.two_checkout.co/transaction/verify/' . $transaction_ref;
            $headers = array(
                'Authorization' => 'Bearer ' . $this->private_key(),
            );
            $args = array(
                'headers'   => $headers,
                'timeout'   => 60
            );
            $request = wp_remote_get($verification_url, $args);
            if(is_wp_error( $request )) {
                MS_Helper_Debug::log('error_in_billing');
                throw new Exception('error_in_billing');
            }
            $two_checkout_response = json_decode( wp_remote_retrieve_body( $request ) );
            if ($two_checkout_response->status == true){
                error_log(json_encode($two_checkout_response));
                // setting up customer update request
                $update_customer_url = 'https://api.two_checkout.co/customer/'.$two_checkout_response->data->customer->customer_code;
                $args['method'] = 'PUT';
                $args['body'] = json_encode(array(
                    'metadata' => array(
                        'subscription' => array(
                            'id' => $subscription->id,
                        ),
                    )
                ));
                $args['headers']['Content-Type'] = 'application/json';
                $request = wp_remote_request($update_customer_url, $args);
                error_log(PHP_EOL.'Url for customer update ='.$update_customer_url.PHP_EOL);
                error_log(PHP_EOL.'args ='.print_r($args, true).PHP_EOL);
                error_log(PHP_EOL.'response::'.print_r($request, true).PHP_EOL);
                if(is_wp_error( $request )) {
                    MS_Helper_Debug::log('Failed to update customer 2Checkout data');
                    throw new Exception('Failed to update customer 2Checkout data');
                }

                $invoice->pay_it( self::ID, $transaction_ref );
            }
            return $invoice;
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
    public function handle_return( $log = false )
    {
        $success = false;
        $notes = '';
        $external_id = '';
        $amount = 0;
        $subscription_id = 0;
        $invoice_id = 0;
        $ignore = false;
        
        // only a post with 2Checkout signature header gets our attention
        $params = array();
        foreach ($_REQUEST as $k => $v) {
            $params[$k] = $v;
        }

        $passback = Twocheckout_Return::check($params, $this->secret_word());
        if(strcasecmp($passback['response_code'], self::HASH_RESPONSE_CODE) != 0
            &&
            strcasecmp($passback['response_message'], self::HASH_RESPONSE_MESSAGE) != 0
        ){
            http_response_code(404);
            die('Hash Incorrect');
        }
        http_response_code(200);
        error_log(print_r($_REQUEST, true));
        die();
        // parse event (which is json string) as object
        // Do something - that will not take long - with $event
        $response = json_decode($input);
        $event = $response->event;
        $data = $response->data;
        error_log(json_encode($input));
        $meta_data = $data->customer->metadata;
        if (!$meta_data){
            error_log('Metadata is empty');
            return;
        }
        /** @var MS_Model_Relationship $subscription */
        $subscription = MS_Factory::load(
                'MS_Model_Relationship',
                $meta_data->subscription->id
            );
        
        if (!$subscription){
            error_log('No membership 2 subscription found in event');
            return;
        }
        $invoice = $subscription->get_current_invoice();
         switch ($event) {
             case 'subscription.create':
                 $subscription->set_custom_data('two_checkout_subscription_code', $data->subscription_code);
                 $notes = __('Customer 2Checkout Subscription created', 'membership-two-checkout');
                 $subscription->save();

                 $success = true;
                 break;
            case 'subscription.disable':
                $notes = __('Customer 2Checkout Subscription disabled', 'membership-two-checkout');
                $subscription->status = MS_Model_Relationship::STATUS_CANCELED;
                $subscription->save();

                $success = true;
                break;
            case 'subscription.enable':
                $subscription->status = MS_Model_Relationship::STATUS_ACTIVE;
                $subscription->save();
                $notes = __('Customer 2Checkout Subscription enabled', 'membership-two-checkout');

                $success = true;
                break;
            case 'invoice.create':
            case 'invoice.update':
                $external_id = $data->transaction->reference;
                $invoice->pay_it(self::ID, $external_id );
                $notes = __('Subscription successfully paid for', 'membership-two-checkout');
                $success = true;
                break;

        }
        if($log){
            $log->invoice_id = $invoice_id;
            $log->subscription_id = $subscription_id;
            $log->amount = $amount;
            $log->description = $notes;
            $log->external_id = $external_id;
            if ( $success ) {
                    $log->manual_state( 'ok' );
            } elseif ( $ignore ) {
                    $log->manual_state( 'ignore' );
            }
            $log->save();
        }else{
            
        }
        do_action(
                    'ms_gateway_transaction_log',
                    self::ID, // gateway ID
                    'handle', // request|process|handle
                    $success, // success flag
                    $subscription->id, // subscription ID
                    $invoice->id, // invoice ID
                    $invoice->total, // charged amount
                    $notes, // Descriptive text
                    $external_id // External ID
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
