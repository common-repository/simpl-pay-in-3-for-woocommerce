<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://getsimpl.com/
 * @since      1.0.0
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/includes
 * @author     Simpl <merchant-support@getsimpl.com>
 */
class Simpl_Pay_In_3_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Simpl_Pay_In_3_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		if ( defined( 'SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG' ) ) {
			$this->plugin_name = SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG;
		} else {
			$this->plugin_name = 'simpl-pay-in-3-for-woocommerce';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action( 'plugins_loaded', array( $this, 'init_gateway_class' ) );
		add_filter( 'plugin_action_links_' . SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_BASENAME, array( $this, 'plugin_page_settings_link' ), 10, 1 );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simpl_Pay_In_3_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Simpl_Pay_In_3_For_Woocommerce_i18n. Defines internationalization functionality.
	 * - Simpl_Pay_In_3_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Simpl_Pay_In_3_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simpl-pay-in-3-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simpl-pay-in-3-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simpl-pay-in-3-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-simpl-pay-in-3-for-woocommerce-public.php';

		$this->loader = new Simpl_Pay_In_3_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simpl_Pay_In_3_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Simpl_Pay_In_3_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Simpl_Pay_In_3_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Simpl_Pay_In_3_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Simpl_Pay_In_3_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Include plugin gateway class file
	 *
	 * @since    1.0.0
	 */
	public function init_gateway_class() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simpl-pay-in-3-for-woocommerce-gateway.php';
	}

	/**
	 * Plugin page settings.
	 *
	 * @since   1.0.0
	 * @param       Array $links  Plugin Settings page link.
	 * @return      Array $links       Plugin Settings page link.
	 */
	public function plugin_page_settings_link( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=simpl-pay-in-3' ) . '" aria-label="' . esc_attr__( 'View settings', 'simpl-pay-in-3-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'simpl-pay-in-3-for-woocommerce' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

}
