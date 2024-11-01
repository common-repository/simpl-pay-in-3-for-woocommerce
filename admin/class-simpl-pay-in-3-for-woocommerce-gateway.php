<?php
/**
 * The paymentgateway-specific functionality of the plugin.
 *
 * @link       http://getsimpl.com/
 * @since      1.0.0
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/admin
 * @author     Simpl <merchant-support@getsimpl.com>
 */
class Simpl_Pay_In_3_For_Woocommerce_Gateway extends WC_Payment_Gateway
{

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        $this->id = SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG; // payment gateway plugin ID.

        // URL of the icon that will be displayed on checkout page near your gateway name.
        if ($this->get_option('checkout_page_payment_method_logo_image')) {
            $this->icon = $this->get_option('checkout_page_payment_method_logo_image');
        } else {
            $this->icon = plugin_dir_url(SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_FILE) . 'public/images/brand.svg';
        }


        $this->has_fields = true; // in case you need a custom credit card form.
        $this->method_title = esc_html__('Simpl', 'simpl-pay-in-3-for-woocommerce');
        $this->method_description = esc_html__('Enable your users to pay for their orders in 3 simple payments by Simpl. Easy and convenient.', 'simpl-pay-in-3-for-woocommerce');
        $this->log = new WC_Logger();

        $this->supports = array(
            'products',
            'refunds',
        );

        $this->init_form_fields();
        $this->init_settings();

        $this->title = 'Simpl';
        $this->description = 'Interest Free. Always.';
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->merchant_client_id = $this->get_option('merchant_client_id');
        $this->merchant_client_secret = $this->get_option('merchant_client_secret');

        $this->checkAirBreak = 'yes';
        $this->airBreakProjectId = '331901';
        $this->airBreakKey = '7369135ff3bb9c93250b4f90f47b3050';

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_api_' . strtolower('Simpl_Pay_In_3_For_Woocommerce_Gateway'), array($this, 'payment_callback'));

        add_action('woocommerce_api_' . strtolower('Simpl_Pay_In_3_For_Woocommerce_Gateway_Webhook'), array($this, 'webhook_callback'));
    }

    /**
     * Plugin options, we deal with it in Step 3 too
     *
     * @since    1.0.0
     */
    public function init_form_fields()
    {

	    $promotionalPosition = ['after'=>'After Simpl Widget','before'=>'Before Simpl Widget'];

        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_html__('Enable/Disable', 'simpl-pay-in-3-for-woocommerce'),
                'label' => esc_html__('Enable Simpl', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'testmode' => array(
                'title' => esc_html__('Test mode', 'simpl-pay-in-3-for-woocommerce'),
                'label' => esc_html__('Enable Test Mode', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'checkbox',
                'description' => esc_html__('Place the payment gateway in test mode using test API keys.', 'simpl-pay-in-3-for-woocommerce'),
                'default' => 'yes',
                'desc_tip' => true,
            ),
            'merchant_client_id' => array(
                'title' => esc_html__('Merchant Client ID', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),
            'merchant_client_secret' => array(
                'title' => esc_html__('Merchant Client Secret', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'password',
            ),

            'merchant_popup_image' => array(
                'title' => esc_html__('Price Breakdown Popup Image Path', 'simpl-pay-in-3-for-woocommerce'),
                'placeholder' => __('Optional', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),

            'pay_in_3_note_simpl_logo_image' => array(
                'title' => esc_html__('Price Breakdown Description Simpl Logo Image Path', 'simpl-pay-in-3-for-woocommerce'),
                'placeholder' => __('Optional', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),

            'checkout_page_payment_method_title' => array(
                'title' => esc_html__('Payment Method Title', 'simpl-pay-in-3-for-woocommerce'),
                'placeholder' => __('Optional', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),

            'checkout_page_payment_method_description' => array(
                'title' => esc_html__('Payment Method Description', 'simpl-pay-in-3-for-woocommerce'),
                'placeholder' => __('Optional', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),

            'checkout_page_payment_method_logo_image' => array(
                'title' => esc_html__('Payment Method Logo Image Path', 'simpl-pay-in-3-for-woocommerce'),
                'placeholder' => __('Optional', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
            ),

            'logging' => array(
                'title' => __('Enable Logging', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Logging', 'simpl-pay-in-3-for-woocommerce'),
                'default' => 'yes',
            ),
            'min_price_limit' => array(
                'title' => __('Minimum Price Limit (Integer Numbers only)', 'simpl-pay-in-3-for-woocommerce'),
                'type' => 'text',
                'description' => esc_html__('Minimum Price Limit To Display SplitPay Widget And Payment Method On Checkout , NOTE : Max Price Limit is 25000 INR', 'simpl-pay-in-3-for-woocommerce'),
            ),
            'promotional_text' => array(
	            'title' => __('Promotional (Offer) Text', 'simpl-pay-in-3-for-woocommerce'),
	            'type' => 'text',
	            'description' => esc_html__('This field will be used as Promotional Text, NOTE : But for Simpl plugin and its offer only.', 'simpl-pay-in-3-for-woocommerce'),
            ),
            'promotional_text_position' => array(
	            'title'    => __( 'Promotional Text Position', 'simpl-pay-in-3-for-woocommerce' ),
	            'description'     => __( 'You can show the promotional text before or after Simpl Widget', 'promotional_text_position' ),
	            'id'       => 'promotional_text_position',
	            'default'  => 'after',
	            'type'     => 'select',
	            'class'    => 'wc-enhanced-select',
	            'options'  => $promotionalPosition,
            ),
            'promotional_offer_date' => array(
	            'title'    => __( 'Promotional Offer Date (Should be future Date only)', 'simpl-pay-in-3-for-woocommerce' ),
	            'description'     => __( 'When you use Promotional Text then please se this date which will show the promotional <br> text till that date else it will show until you remove the text', 'promotional_text_position' ),
	            'id'       => 'promotional_offer_date',
	            'default'  => 'after',
	            'type'     => 'date',
            ),
        );
    }

    /**
     * Process Payment.
     *
     * @param int $order_id The Order ID.
     * @return  string $url Redirect URL.
     * @since    1.0.0
     */
    public function process_payment($order_id)
    {
        try {
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
            } else {
                $order = new WC_Order($order_id);
            }
            return $this->get_redirect_url($order);
        } catch (Exception $e) {
            $this->notify_airbrake($e, $order_id);
        }
    }

    /**
     * Returns Initite URL.
     *
     * @return  string $url Initiate URL.
     * @since    1.0.0
     */
    public function get_initiate_url()
    {
        if ($this->testmode) {
            return 'https://sandbox-splitpay-api.getsimpl.com/api/v1/transaction/initiate';   // sandbox url.
        } else {
            return 'https://splitpay-api.getsimpl.com/api/v1/transaction/initiate';  // live url.
        }
    }

    /**
     * Notifies error to airbrake.
     *
     * @param Object $exception Exception to be notified.
     * @param string $order_id Order ID.
     * @since    1.0.0
     */
    public function notify_airbrake($exception, $order_id)
    {
        $error_class = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTrace();

        $body = array();

        $body['context'] = array(
            'environment' => $this->testmode ? 'sandbox' : 'production',
            'context' => 'error'
        );

        $body['params'] = array(
            'merchant_client_id' => $this->merchant_client_id,
            'order_id' => $order_id
        );

        $body['errors'] = array();

        $code = array();
        foreach ($trace as $index => $trace_line) {
            $code[$index] = json_encode($trace_line);
        }

        $backtrace_data = array(
            'file' => $file,
            'line' => $line,
            'code' => (object)$code
        );

        $error_data = array(
            'type' => $error_class,
            'message' => $message,
            'backtrace' => array()
        );
        array_push($error_data['backtrace'], $backtrace_data);
        array_push($body['errors'], $error_data);

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 80,
            'redirection' => 35,
        );

        if($this->checkAirBreak =='yes' && !empty($this->airBreakProjectId) && !empty($this->airBreakKey)) {
            $url                  = 'https://api.airbrake.io/api/v3/projects/' . $this->airBreakProjectId . '/notices?key=' . $this->airBreakKey;
            $response             = wp_remote_post( $url, $args );
            $encode_response_body = wp_remote_retrieve_body( $response );
            $response_code        = wp_remote_retrieve_response_code( $response );

            if ( 200 === $response_code ) {
                $this->log( 'Notified error to airbrake: ' . $message );
            } else {
                $this->log( 'Airbrake notification failed with response code: ' . $response_code );
            }
        }   else{
            $this->log->add('simpl', json_encode($body));
        }
    }

    public function airBreakTrack($message = '', $moreTraceData = '', $order_id = null)
    {
        $prepareRequest = array();

        $urlparts = parse_url(home_url());
        $prepareRequest['context'] = array(
            'environment' => $this->testmode ? 'sandbox' : 'production',
            'context' => 'error',
            'hostname' => $urlparts['host']
        );

        $prepareRequest['params'] = array(
            'merchant_client_id' => $this->merchant_client_id,
            'order_id' => $order_id
        );

        $multipleErrorGroups = [];
        $errorRequest = [];

        $bt = debug_backtrace();
        $caller = array_shift($bt);
        if (is_array($moreTraceData)) {
            $counter = 1;
            foreach ($moreTraceData as $key => $values) {
                $multipleErrorGroups[$counter] = (string)$values;
                $counter++;
            }
            $errorRequest[] = array(
                "type" => "error1",
                "message" => (string)$message,
                "backtrace" => [
                    [
                        'file' => $caller['file'],
                        'line' => $caller['line'],
                        "function" => "backtrace function",
                        "code" => $multipleErrorGroups
                    ]
                ]
            );

        } else {
            $errorRequest[] = array(
                "type" => "error1",
                "message" => (string)$message,
                "backtrace" => [
                    [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        "function" => "backtrace function",
                        "code" => (string)$moreTraceData
                    ]
                ]
            );
        }
        $prepareRequest['errors'] = $errorRequest;

        if($this->checkAirBreak =='yes' && !empty($this->airBreakProjectId) && !empty($this->airBreakKey)) {
            $url = "https://api.airbrake.io/api/v3/projects/".$this->airBreakProjectId."/notices?key=".$this->airBreakKey;
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($prepareRequest),
                'timeout' => 80,
                'redirection' => 35,
            );
            $response = wp_remote_post($url, $args);
            $response_code = wp_remote_retrieve_response_code($response);
            if (201 === $response_code) {
                $this->log('Notified error to airbrake: ' . $message);
            } else {
                $this->log('Airbrake notification failed with response code: ' . $response_code);
            }
        } else{
            $this->log->add('simpl', json_encode($prepareRequest));
        }
    }

    /**
     * Returns Transaction status URL.
     *
     * @param int $order_id The Order Id.
     * @return  string $url Transation status URL.
     * @since    1.0.0
     */
    public function get_transaction_status_url($order_id)
    {
        if ($this->testmode) {
            $url = 'https://sandbox-splitpay-api.getsimpl.com/api/v1/transaction_by_order_id/{order_id}/status';
        } else {
            $url = 'https://splitpay-api.getsimpl.com/api/v1/transaction_by_order_id/{order_id}/status';
        }

        $url = str_replace('{order_id}', $order_id, $url);
        return $url;
    }

    /**
     * Returns refund URL.
     *
     * @return  string $url refund URL.
     * @since    1.0.0
     */
    public function get_refund_url()
    {
        if ($this->testmode) {
            return 'https://sandbox-splitpay-api.getsimpl.com/api/v1/transaction/refund';
        } else {
            return 'https://splitpay-api.getsimpl.com/api/v1/transaction/refund';
        }
    }

    /**
     * Returns Redirect URL.
     *
     * @param Object $order Order.
     * @return  array $url redirect URL.
     * @since    1.0.0
     */
    public function get_redirect_url($order)
    {
        $uniq_order_id = $this->get_unique_order_id($order->get_id());
        update_post_meta($order->get_id(), '_simpl_order_id', $uniq_order_id);
        $body = array(
            'merchant_client_id' => $this->merchant_client_id,
            'transaction_status_redirection_url' => get_site_url() . '/?wc-api=Simpl_Pay_In_3_For_Woocommerce_Gateway&key=' . $order->get_order_key(),
            'transaction_status_webhook_url' => get_site_url() . '/?wc-api=Simpl_Pay_In_3_For_Woocommerce_Gateway_Webhook&key=' . $order->get_order_key(),
            'order_id' => (string)$uniq_order_id,
            'amount_in_paise' => (int)($order->get_total() * 100),
            'journey_id' => WC()->session->get('simpl_journey_id'),
        );

        $body['user'] = array(
            'phone_number' => $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
        );

        $body['billing_address'] = array(
            'line1' => $order->get_billing_address_1(),
            'line2' => $order->get_billing_address_2(),
            'city' => $order->get_billing_city(),
            'pincode' => $order->get_billing_postcode(),
        );

        if (!empty($order->get_billing_address_2())) {
            $body['billing_address']['line1'] = $order->get_billing_address_2();
            $body['billing_address']['line2'] = $order->get_billing_address_1();
        }

        $body['shipping_address'] = array(
            'line1' => $order->get_shipping_address_1(),
            'line2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'pincode' => $order->get_shipping_postcode(),
        );

        $body['items'] = array();
        if (count($order->get_items())) {
            foreach ($order->get_items() as $item) {
                if ($item['variation_id']) {
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($item['variation_id']);
                    } else {
                        $product = new WC_Product($item['variation_id']);
                    }
                } else {
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($item['product_id']);
                    } else {
                        $product = new WC_Product($item['product_id']);
                    }
                }
                $item_data = array(
                    'sku' => $item['name'],
                    'quantity' => $item['qty'],
                    'rate_per_item' => (int)($product->get_price_including_tax() * 100),
                );
                array_push($body['items'], $item_data);
            }
        }

        if ($this->testmode) {
            $body['mock_eligibility_response'] = 'eligibility_success';
            $body['mock_eligibility_amount_in_paise'] = 500000;
        }

        $this->log('Simpl redirecting');

        $args = array(
            'headers' => array(
                'Authorization' => $this->merchant_client_secret,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 80,
            'redirection' => 35,
        );
        $initiate_url = $this->get_initiate_url();
        $response = wp_remote_post($initiate_url, $args);
        $encode_response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $this->dump_api_actions($initiate_url, $args, $encode_response_body, $response_code);
        if (200 === $response_code) {
            $response_body = json_decode($encode_response_body);
            update_post_meta($order->get_id(), '_simpl_redirect_url', $response_body->data->redirection_url);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        } else {

            $response_body = json_decode($encode_response_body);
            // Tracking to AirBreak
            $messageParse = 'Sorry, there was a problem preparing your payment.';
            $moreTraceData = array('response_code' => $response_code, 'responsedata' => json_encode($response_body));
            $this->airBreakTrack($messageParse,$moreTraceData, $order->get_id());
            // Tracking to AirBreak
            $order->add_order_note(esc_html__('Unable to generate the transaction ID. Payment couldn\'t proceed.', 'simpl-pay-in-3-for-woocommerce'));
            wc_add_notice(esc_html__('Sorry, there was a problem preparing your payment.', 'simpl-pay-in-3-for-woocommerce'), 'error');
            return array(
                'result' => 'failure',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        }

    }

    /**
     * Generates unique Simpl order id.
     *
     * @param string $order_id Order ID.
     * @return  string $uniq_order_id Unique Order ID.
     * @since    1.0.0
     */
    public function get_unique_order_id($order_id)
    {
        $random_bytes = random_bytes(13);
        return $order_id . '-' . bin2hex($random_bytes);
    }

    /**
     * Log Messages.
     *
     * @param string $message Log Message.
     * @since    1.0.0
     */
    public function log($message)
    {
        if ($this->get_option('logging') === 'no') {
            return;
        }
        if (empty($this->log)) {
            $this->log = new WC_Logger();
        }
        $this->log->add('simpl', $message);
    }

    /**
     * Dump API Actions.
     *
     * @param string $url URL.
     * @param Array $request Request.
     * @param Array $response Response.
     * @param Int $status_code Status Code.
     * @since    1.0.0
     */
    public function dump_api_actions($url, $request = null, $response = null, $status_code = null)
    {
        if ($this->get_option('testmode') === 'no') {
            return;
        }
        ob_start();
        echo esc_url($url);
        echo '<br>';
        echo 'Request Body : ';
        echo '<br>';
        print_r($request);
        echo '<br>';
        echo 'Response Body : ';
        echo '<br>';
        print_r($response);
        echo '<br>';
        echo 'Status Code : ';
        echo esc_html($status_code);
        $data = ob_get_clean();
        $this->log($data);
    }


    /**
     * Receipt Page.
     *
     * @param int $order_id Order Id.
     * @since    1.0.0
     */
    public function receipt_page($order_id)
    {
        echo '<p>' . esc_html__('Thank you for your order, please wait as you will be automatically redirected to Simpl.', 'simpl-pay-in-3-for-woocommerce') . '</p>';

        $redirect_url = get_post_meta($order_id, '_simpl_redirect_url', true);
        ?>
        <script>
            var redirect_url = <?php echo json_encode($redirect_url); ?>;
            window.location.replace(redirect_url);
        </script>
        <?php
    }

    /**
     * Payment Callback check.
     *
     * @since    1.0.0
     */
    public function payment_callback()
    {
        $_GET = stripslashes_deep(wc_clean($_GET));
        $this->dump_api_actions('paymenturl', '', $_GET);

        $order_key = (isset($_GET['key'])) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

        $order_id = wc_get_order_id_by_order_key($order_key);

        try {
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
            } else {
                $order = new WC_Order($order_id);
            }

            if (isset($_GET['status']) && 'SUCCESS' === sanitize_text_field(wp_unslash($_GET['status']))) {

                $_order_id = (isset($_GET['order_id'])) ? sanitize_text_field(wp_unslash($_GET['order_id'])) : '';
                $status = (isset($_GET['status'])) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
                $signature = (isset($_GET['signature'])) ? sanitize_text_field(wp_unslash($_GET['signature'])) : '';
                $signature_algorithm = (isset($_GET['signature_algorithm'])) ? sanitize_text_field(wp_unslash($_GET['signature_algorithm'])) : '';
                $nonce = (isset($_GET['nonce'])) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';
                $transaction_id = (isset($_GET['transaction_id'])) ? sanitize_text_field(wp_unslash($_GET['transaction_id'])) : '';

                $data = array();
                if (!empty($nonce)) {
                    $data['nonce'] = $nonce;
                }
                if (!empty($_order_id)) {
                    $data['order_id'] = $_order_id;
                }
                if (!empty($status)) {
                    $data['status'] = $status;
                }
                if (!empty($transaction_id)) {
                    $data['transaction_id'] = $transaction_id;
                    $order->set_transaction_id($transaction_id);
                    $order->save();
                }

                $data = build_query($data);

                $setAlgo = explode('-', strtolower($signature_algorithm));
                $encrypt = !empty($setAlgo[1]) ? $setAlgo[1] : 'sha1';

                $_signature = hash_hmac($encrypt, $data, $this->merchant_client_secret);

                if ($signature === $_signature) {
                    $url = $this->get_transaction_status_url($_order_id);
                    $this->log('Simpl Transaction Status Check');
                    $args = array(
                        'headers' => array(
                            'Authorization' => $this->merchant_client_secret,
                            'Content-Type' => 'application/json',
                        ),
                        'timeout' => 80,
                        'redirection' => 35,
                    );
                    $response = wp_remote_get($url, $args);
                    $encode_response_body = wp_remote_retrieve_body($response);
                    $response_code = wp_remote_retrieve_response_code($response);
                    $this->dump_api_actions($$url, $args, $encode_response_body, $response_code);
                    if (200 === $response_code) {
                        $response_body = json_decode($encode_response_body);
                        $data = $response_body->data;
                        $transaction_id = $order->get_transaction_id();
                        if (empty($transaction_id) && !empty($data->id)) {
                            $order->set_transaction_id($data->id);
                        }
                        if (true === $response_body->success) {
                            if ('SUCCESS' === $data->status) {
                                $order->add_order_note(esc_html__('Payment approved by Simpl successfully.', 'simpl-pay-in-3-for-woocommerce'));
                                $order->payment_complete($data->id);
                                WC()->cart->empty_cart();
                                $redirect_url = $this->get_return_url($order);
                            } else {

                                $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                                // Triggering AirBreak Alert -- Start
                                $moreTraceData = array('response_code' => $response_code, 'responsedata' => json_encode($response_body));
                                $this->airBreakTrack($message,$moreTraceData, $order->get_id());
                                // Triggering AirBreak Alert -- End

                                $order->update_status('failed', $message);
                                $this->add_order_notice($message);
                                $redirect_url = wc_get_checkout_url();
                            }
                        } else {
                            $data = $response_body->error;
                            $error_code = $data->code;
                            $error_message = $data->message;
                            $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'pcss-woo-order-notifications');

                            // Triggering AirBreak Alert -- Start
                            $moreTraceData = array('responsedata' => json_encode($response_body));
                            $this->airBreakTrack($message,$moreTraceData, $order->get_id());
                            // Triggering AirBreak Alert -- End

                            $order->update_status('failed', $message);
                            $this->add_order_notice($message);
                            $redirect_url = wc_get_checkout_url();
                        }
                    } else {
                        $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                        $response_body = json_decode($encode_response_body);

                        // Triggering AirBreak Alert -- Start
                        $moreTraceData = array('responsedata' => json_encode($response_body));
                        $this->airBreakTrack($message,$moreTraceData, $order->get_id());
                        // Triggering AirBreak Alert -- End

                        $order->update_status('failed', $message);
                        $this->add_order_notice($message);
                        $redirect_url = wc_get_checkout_url();
                    }
                } else {
                    $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');

                    // Triggering AirBreak Alert -- Start
                    $moreTraceData = array('debugerror' => 'Order failed because the encryption signature dosnt match');
                    $this->airBreakTrack($message,$moreTraceData, $order->get_id());
                    // Triggering AirBreak Alert -- End

                    $order->update_status('failed', $message);
                    $this->add_order_notice($message);
                    $redirect_url = wc_get_checkout_url();
                }
            } else {
                $error = isset($_GET['error_code']) ? sanitize_text_field(wp_unslash($_GET['error_code'])) : '';
                $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                // Triggering AirBreak Alert -- Start
                $moreTraceData = array('errorText' => $error, 'errorScenario' => 'In return there is no Status field in return url');
                $this->airBreakTrack($message,$moreTraceData, $order->get_id());
                // Triggering AirBreak Alert -- End

                $order->update_status('failed', $message);
                $this->add_order_notice($message);
                $redirect_url = wc_get_checkout_url();
            }
            wp_redirect($redirect_url);
            die();
        } catch (Exception $e) {
            $this->notify_airbrake($e, $order_id);
        }
    }

    /**
     * Webhook Callback check.
     *
     * @since    1.0.0
     */
    public function webhook_callback()
    {
        $_GET = stripslashes_deep(wc_clean($_GET));
        $this->dump_api_actions('webhook', '', $_GET);

        $order_key = (isset($_GET['key'])) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

        $order_id = wc_get_order_id_by_order_key($order_key);

        try {
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
            } else {
                $order = new WC_Order($order_id);
            }

            if ('pending' != $order->get_status()) return;

            if (isset($_GET['status']) && 'SUCCESS' === sanitize_text_field(wp_unslash($_GET['status']))) {
                $_order_id = (isset($_GET['order_id'])) ? sanitize_text_field(wp_unslash($_GET['order_id'])) : '';
                $status = (isset($_GET['status'])) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
                $signature = (isset($_GET['signature'])) ? sanitize_text_field(wp_unslash($_GET['signature'])) : '';
                $signature_algorithm = (isset($_GET['signature_algorithm'])) ? sanitize_text_field(wp_unslash($_GET['signature_algorithm'])) : '';
                $nonce = (isset($_GET['nonce'])) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';
                $transaction_id = (isset($_GET['transaction_id'])) ? sanitize_text_field(wp_unslash($_GET['transaction_id'])) : '';

                $data = array();
                if (!empty($nonce)) {
                    $data['nonce'] = $nonce;
                }
                if (!empty($_order_id)) {
                    $data['order_id'] = $_order_id;
                }
                if (!empty($status)) {
                    $data['status'] = $status;
                }
                if (!empty($transaction_id)) {
                    $data['transaction_id'] = $transaction_id;
                    $order->set_transaction_id($transaction_id);
                    $order->save();
                }

                $data = build_query($data);

                $setAlgo = explode('-', strtolower($signature_algorithm));
                $encrypt = !empty($setAlgo[1]) ? $setAlgo[1] : 'sha1';

                $_signature = hash_hmac($encrypt, $data, $this->merchant_client_secret);

                if ($signature === $_signature) {
                    $url = $this->get_transaction_status_url($_order_id);
                    $this->log('Simpl Transaction Status Check');
                    $args = array(
                        'headers' => array(
                            'Authorization' => $this->merchant_client_secret,
                            'Content-Type' => 'application/json',
                        ),
                        'timeout' => 80,
                        'redirection' => 35,
                    );
                    $response = wp_remote_get($url, $args);
                    $encode_response_body = wp_remote_retrieve_body($response);
                    $response_code = wp_remote_retrieve_response_code($response);
                    $this->dump_api_actions($$url, $args, $encode_response_body, $response_code);
                    if (200 === $response_code) {
                        $response_body = json_decode($encode_response_body);
                        $data = $response_body->data;
                        $transaction_id = $order->get_transaction_id();
                        if (empty($transaction_id) && !empty($data->id)) {
                            $order->set_transaction_id($data->id);
                        }
                        if (true === $response_body->success) {
                            if ('SUCCESS' === $data->status) {
                                $order->add_order_note(esc_html__('Payment approved by Simpl successfully.', 'simpl-pay-in-3-for-woocommerce'));
                                $order->payment_complete($data->id);
                            } else {
                                $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                                $order->update_status('failed', $message);
                                $this->add_order_notice($message);
                            }
                        } else {
                            $data = $response_body->error;
                            $error_code = $data->code;
                            $error_message = $data->message;
                            $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'pcss-woo-order-notifications');
                            $order->update_status('failed', $message);
                            $this->add_order_notice($message);
                        }
                    } else {
                        $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                        $order->update_status('failed', $message);
                        $this->add_order_notice($message);
                    }
                } else {
                    $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                    $order->update_status('failed', $message);
                    $this->add_order_notice($message);
                }
            } else {
                $error = isset($_GET['error_code']) ? sanitize_text_field(wp_unslash($_GET['error_code'])) : '';
                $message = esc_html__('Your payment via Simpl was unsuccessful. Please try again.', 'simpl-pay-in-3-for-woocommerce');
                $order->update_status('failed', $message);
                $this->add_order_notice($message);
            }

        } catch (Exception $e) {
            $this->notify_airbrake($e, $order_id);
        }
    }

    /**
     * Add notie to order.
     *
     * @param string $message Message.
     * @since    1.0.0
     */
    public function add_order_notice($message)
    {
        wc_add_notice($message, 'error');
    }

    /**
     * Process Refund.
     *
     * @param Int $order_id Order Id.
     * @param float $amount Amount.
     * @param String $reason Refund Reason.
     * @return  bool true|false Return Refund Status.
     * @since    1.0.0
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
            } else {
                $order = new WC_Order($order_id);
            }
            $transaction_id = $order->get_transaction_id();
            $url = $this->get_refund_url();

            $uniq_order_id = $this->get_unique_order_id($order->get_id());
            update_post_meta($order->get_id(), '_simpl_order_refund_id', $uniq_order_id);

            $body = array(
                'merchant_client_id' => $this->merchant_client_id,
                'amount_in_paise' => (int)(round($amount, 2) * 100),
                'transaction_id' => $transaction_id,
                'reason' => $reason,
                'order_id' => (string)$uniq_order_id,
            );
            $args = array(
                'headers' => array(
                    'Authorization' => $this->merchant_client_secret,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($body),
                'timeout' => 80,
                'redirection' => 35,
            );
            $response = wp_remote_post($url, $args);
            $encode_response_body = wp_remote_retrieve_body($response);
            $response_code = wp_remote_retrieve_response_code($response);
            $this->dump_api_actions($url, $args, $encode_response_body, $response_code);
            $status = '';
            $code = '';
            $message = '';
            if (200 === $response_code) {
                $response_body = json_decode($encode_response_body);
                if (true === $response_body->success) {
                    $data = $response_body->data;
                    $status = true;
                    /* translators: %1$s Amount, %2$s Refund ID */
                    $message = sprintf(__('Refund of %1$s successfully sent to Simpl. Refund Transaction Id : %2$s', 'simpl-pay-in-3-for-woocommerce'), $amount, $data->refunded_transaction_id);
                } else {
                    $status = false;
                    /* translators: %1$s Error Code, %2$s Error Message */
                    $message = sprintf(__('There was an error submitting the refund to Simpl. Error Code %1$s, Error Message : %2$s', 'simpl-pay-in-3-for-woocommerce'), $response_body->error->code, $response_body->error->message);
                }
            } else {
                $status = false;
                $message = sprintf(__('There was an error submitting the refund to Simpl.', 'simpl-pay-in-3-for-woocommerce'));
            }

            if (true === $status) {
                $order->add_order_note($message);
                return true;
            } else {
                $order->add_order_note($message);
                return false;
            }
        } catch (Exception $e) {
            $this->notify_airbrake($e, $order_id);
            return false;
        }
    }
}
