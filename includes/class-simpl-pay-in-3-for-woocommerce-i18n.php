<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://getsimpl.com/
 * @since      1.0.0
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/includes
 * @author     Simpl <merchant-support@getsimpl.com>
 */
class Simpl_Pay_In_3_For_Woocommerce_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'simpl-pay-in-3-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
