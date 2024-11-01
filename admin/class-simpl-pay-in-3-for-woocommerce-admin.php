<?php
/**
 * The admin-specific functionality of the plugin.
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
class Simpl_Pay_In_3_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_new_payment_gateway' ) );

	}

	/**
	 * Initialize Stripe external paymentgateway.
	 *
	 * @since    1.0.0
	 * @param      array $gateways       Payment Gateways.
	 * @return      array $gateways    Payment gateways.
	 */
	public function add_new_payment_gateway( $gateways ) {
		$gateways[] = 'Simpl_Pay_In_3_For_Woocommerce_Gateway';
		return $gateways;
	}

}
