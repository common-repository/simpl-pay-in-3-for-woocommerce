<?php
/**
 * The plugin bootstrap file
 *
 * @link              http://getsimpl.com/
 * @since             1.0.0
 * @package           Simpl_Pay_In_3_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Simpl Pay-in-3 for WooCommerce
 * Plugin URI:        http://getsimpl.com/
 * Description:       Enable your users to pay for their orders in 3 simple payments by Simpl. Easy and convenient.
 * Version:           1.2.5
 * Author:            Simpl
 * Text Domain:       simpl-pay-in-3-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.6
 * Tested up to: 5.9.3
 * WC requires at least: 4.0.0
 * WC tested up to:   5.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_VERSION', '1.2.5' );

/**
 * Currently plugin slug.
 */
define( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG', 'simpl-pay-in-3' );

/**
 * Currently plugin file.
 */
define( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_FILE', __FILE__ );

/**
 * Currently plugin basename.
 */
define( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_BASENAME', plugin_basename( SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_FILE ) );

/**
 * Currently plugin dir.
 */
define( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-simpl-pay-in-3-for-woocommerce-activator.php
 */
function activate_simpl_pay_in_3_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpl-pay-in-3-for-woocommerce-activator.php';
	Simpl_Pay_In_3_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-simpl-pay-in-3-for-woocommerce-deactivator.php
 */
function deactivate_simpl_pay_in_3_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpl-pay-in-3-for-woocommerce-deactivator.php';
	Simpl_Pay_In_3_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simpl_pay_in_3_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_simpl_pay_in_3_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simpl-pay-in-3-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_simpl_pay_in_3_for_woocommerce() {
	$plugin = new Simpl_Pay_In_3_For_Woocommerce();
	$plugin->run();

}

/**
* Check if WooCommerce is active
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	run_simpl_pay_in_3_for_woocommerce();
} else {
	add_action( 'admin_notices', 'simpl_pay_in_3_for_woocommerce_installed_notice' );
}

/**
 * Display Woocommerce Activation notice.
 */
function simpl_pay_in_3_for_woocommerce_installed_notice() {     ?>
	<div class="error">
	  <p><?php echo esc_html__( 'Simpl Pay-in-3 for WooCommerce requires WooCommerce Plugin. Please install or activate WooCommerce', 'simpl-pay-in-3-for-woocommerce' ); ?></p>
	</div>
	<?php
}
