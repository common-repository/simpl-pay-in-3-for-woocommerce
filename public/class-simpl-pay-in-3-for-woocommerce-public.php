<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://getsimpl.com/
 * @since      1.0.0
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Simpl_Pay_In_3_For_Woocommerce
 * @subpackage Simpl_Pay_In_3_For_Woocommerce/public
 * @author     Simpl <merchant-support@getsimpl.com>
 */
class Simpl_Pay_In_3_For_Woocommerce_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The settings of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array $settings Settings of this plugin.
     */
    private $settings;

    /**
     * The strings of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array $strings Strings of this plugin.
     */
    private $strings;

    /**
     * The supported countries of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array $supported_countries Supported countries of this plugin.
     */
    private $supported_countries;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->supported_countries = array('IN');

        $this->strings = array(
            'price_string' => 'Or 3 interest free payments of {{ amount }} with {{ logo }} {{ info_icon }}',
            'payment_method_title' => 'Simpl',
            'payment_method_description' => 'Interest Free. Always.',
            'varying_product_payment_description' => 'Or 3 interest free payments starting with {{ amount }} on {{ logo }} {{ info_icon }}'
        );

        add_action('init', array($this, 'init'));
        add_filter('woocommerce_available_payment_gateways', array($this, 'simpl_pay_inthree_payments_methods'), 10, 1);
        add_shortcode('simpl_pdp_payin3_widget', array($this, 'simplPayinPdpWidget'));
    }

    /**
     * Initialize the functions.
     *
     * @since    1.0.0
     */
    public function init()
    {
        global $woocommerce;

        $payWithSimpl = 'simpl-pay-in-3';
        $checkIfPayInThreeEnabled = WC()->payment_gateways->payment_gateways();
        $available_payment_methods = WC()->payment_gateways->get_available_payment_gateways();

        if (!empty($checkIfPayInThreeEnabled[$payWithSimpl]) && $checkIfPayInThreeEnabled[$payWithSimpl]->enabled == 'yes') {
            $price = !empty($woocommerce->cart->total) ? (float)$woocommerce->cart->total : 0;
            $showWidget = $this->showPayInThree($price);
            if($showWidget) {
                $available_payment_methods[$payWithSimpl] = $checkIfPayInThreeEnabled[$payWithSimpl];
            }
        }

        if (isset($available_payment_methods[$payWithSimpl])) {
            $this->settings = $available_payment_methods[$payWithSimpl]->settings;

            add_filter('woocommerce_available_payment_gateways', array($this, 'remove_gateway_based_on_billing_country'), 10, 2);

            add_action('woocommerce_single_product_summary', array($this, 'simpl_price_text'), 12);
            add_action('woocommerce_before_add_to_cart_button', array($this, 'variation_price_text'), 10);

            add_filter('woocommerce_available_variation', array($this, 'simpl_price_text_variation'), 10, 3);

            add_action('woocommerce_cart_totals_after_order_total', array($this, 'after_cart_totals'), 99999);

            add_action('woocommerce_review_order_after_order_total', array($this, 'simpl_price_text_checkout'), 1);

            add_filter('woocommerce_gateway_title', array($this, 'checkout_gateway_title'), 10, 2);
            add_filter('woocommerce_gateway_description', array($this, 'checkout_gateway_description'), 10, 2);
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/simpl-pay-in-3-for-woocommerce-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('featherlight', plugin_dir_url(__FILE__) . 'js/featherlight.js', array('jquery'), $this->version, false);
    }

    /**
     * Remove payment gateway based on country.
     *
     * @param array $available_gateways Available Payment Gateways.
     * @return     array $available_gateways       Available Payment Gateways.
     * @since    1.0.0
     */
    public function remove_gateway_based_on_billing_country($available_gateways)
    {
        global $woocommerce;

        if (is_admin()) {
            return $available_gateways;
        }
        if (!WC()->customer) {
            return $available_gateways;
        }
        $country_code = WC()->customer->get_billing_country();
        if ($country_code) {
            if (!in_array($country_code, $this->supported_countries, true)) {
                if (isset($available_gateways['simpl-pay-in-3'])) {
                    unset($available_gateways['simpl-pay-in-3']);
                }
            }
        }
        $price = (float)$woocommerce->cart->total;
        $showWidget = $this->showPayInThree($price);
        if (!$showWidget) {
            unset($available_gateways['simpl-pay-in-3']);
        }
        return $available_gateways;
    }

    /**
     * Display Simpl text on singlr product.
     *
     * @since    1.0.0
     */
    public function simpl_price_text()
    {
        global $product;
        if ('simple' === $product->get_type()) {
            $price = $product->get_price();
            echo wp_kses_post($this->get_simpl_price_text($price, 'product'));
        }
    }

    /**
     * @param $productId
     * @return void
     */
    public function simpl_price_text_by_id($productId)
    {
        $product = wc_get_product($productId);
        if ('simple' === $product->get_type()) {
            $price = $product->get_price();
            return $this->get_simpl_price_text($price, 'product');
        }
    }

    /**
     * @return void
     */
    public function variation_price_text()
    {
        global $product;
        $price = $product->get_price();
        $showWidget = $this->showPayInThree($price);
        if(!$showWidget)
        {
            return '';
        }
        if ('variable' === $product->get_type()) {
            $price = $product->get_price();
            $prices = $product->get_variation_prices();
            if (!empty($prices)) {
                $min_price = current($prices['price']);
                $max_price = end($prices['price']);
                $min_reg_price = current($prices['regular_price']);
                $max_reg_price = end($prices['regular_price']);

                if ($min_price != $max_price || $min_reg_price != $max_reg_price) {
                    echo '<div class="simpl-price-variation-default-text">';
                    echo wp_kses_post($this->get_simpl_price_text($price, 'product', 'variation'));
                    echo '</div>';
                } else {
                    echo '<div class="simpl-price-variation-default-text">';
                    echo wp_kses_post($this->get_simpl_price_text($price, 'product'));
                    echo '</div>';
                }
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        if ($(".single_variation_wrap").length > 0) {
                            var $text = '';
                            if ($('.simpl-price-variation-default-text').length > 0) {
                                var $text = $('.simpl-price-variation-default-text').html();
                            }
                            $(".single_variation_wrap").on("show_variation", function (event, variation) {
                                if ($('.simpl-price-variation-default-text').length > 0) {
                                    $('.simpl-price-variation-default-text').html(variation.simpl_price_text);
                                }
                            });
                            $(".single_variation_wrap").on("hide_variation", function (event, variation) {
                                if ($('.simpl-price-variation-default-text').length > 0) {
                                    $('.simpl-price-variation-default-text').html($text);
                                }
                            });
                        }
                    });
                </script>
                <?php
            }
        }
    }

    /**
     * Add Simpl text to Variation.
     *
     * @param array $value Value.
     * @param Object $product Product.
     * @param Object $variation Variation.
     * @reurn array $value Value.
     * @since    1.0.0
     */
    public function simpl_price_text_variation($value, $product = null, $variation = null)
    {
        if (null != $variation) {
            $price = $variation->get_price();
            $value['simpl_price_text'] = $this->get_simpl_price_text($price, 'product');
        }
        return $value;
    }

    /**
     * Display Simpl text on Checkout Page.
     *
     * @since    1.0.0
     */
    public function simpl_price_text_checkout()
    {
        $total = WC()->cart->get_total('edit');
        echo '<tr class="order-simpl">';
        echo '<td colspan="2">';
        echo wp_kses_post($this->get_simpl_price_text($total, 'checkout'));
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Display Simpl text on Cart Page.
     *
     * @since    1.0.0
     */
    public function after_cart_totals()
    {
        $total = WC()->cart->get_total('edit');
        ?>
        <tr class="simpl-cart-text">
            <td colspan="2">
                <?php
                echo wp_kses_post($this->get_simpl_price_text($total, 'cart'));
                ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Ger Simpl text.
     *
     * @param float $price Price.
     * @param string $page Page Name.
     * @since    1.0.0
     */
    public function get_simpl_price_text($price, $page, $type = 'simple')
    {
        $showWidget = $this->showPayInThree($price);
        if (!$showWidget) {
            return '';
        }

        $popup_image = $this->settings['merchant_popup_image'];
        $featherlight = '';
        $amount_in_paise = (int)(round($price, 2) * 100);
        $div = (int)floor($amount_in_paise / 3);
        $rem = $amount_in_paise - ($div * 3);
        $part = (int)$div + (int)$rem;
        $amount_in_rs = (float)(round($part / 100, 2));
        $part = wc_price($amount_in_rs);
        if ($rem > 0) {
            $args = array(
                'decimals' => 2,
            );
            $part = wc_price($amount_in_rs, $args);
        }
        if ($this->settings['pay_in_3_note_simpl_logo_image']) {
            $image = '<img class="simpl-brand-logo" src="' . esc_html($this->settings['pay_in_3_note_simpl_logo_image']) . '"/>';
        } else {
            $image = '<img class="simpl-brand-logo" src="' . esc_html(plugin_dir_url(__FILE__) . 'images/brand.svg') . '"/>';
        }

        $info_icon = '<img src="' . esc_html(plugin_dir_url(__FILE__) . 'images/info.svg') . '"/>';
        if (empty($popup_image)) {
            $popup_image = 'https://cdn.getsimpl.com/images/pay_in_3/interstitial_1.jpg';
        }

        $featherlight = 'data-featherlight="iframe"';
        $interstitial_options = ' data-page="' . $page . '" ';
        if (is_singular('product')) {
            $object = get_queried_object();
            $product_id = $object->ID;
            $interstitial_options .= ' data-product-id="' . $product_id . '"';
        } else {
            $interstitial_options .= ' data-product-id=""';
        }
	    $interstitial_options .= ' data-featherlight-iframe-allowfullscreen="true" data-featherlight-iframe-width="350" data-featherlight-iframe-height="662"';
        ob_start();
        if ($type == 'simple') {
            $string = $this->strings['price_string'];
        } else {
            $string = $this->strings['varying_product_payment_description'];
        }

	    $linkURL = 'https://d19ud5ez64hf3q.cloudfront.net/popup/index.html?price='.$price;
        $placeholders = array(
            '{{ amount }}' => $part,
            '{{ logo }}' => sprintf('<a class="simpl-popup-link" href="%s" %s %s><span class="product-simpl-logo-text">%s</span></a>', $linkURL, $interstitial_options, $featherlight, $image),
            '{{ info_icon }}' => sprintf('<a class="simpl-popup-link" href="%s" %s %s>%s</a>', $linkURL,$interstitial_options, $featherlight, $info_icon),
        );
        $string = str_replace(array_keys($placeholders), $placeholders, $string);

	    $promotionalText = !empty($this->settings['promotional_text']) ? trim($this->settings['promotional_text']) : '';
	    $promotionalTextPosition = !empty($this->settings['promotional_text_position']) ? $this->settings['promotional_text_position'] : 'after';
        $promotionalOfferDate = !empty($this->settings['promotional_offer_date']) ? strtotime($this->settings['promotional_offer_date']) : '';
        $currentDate = strtotime(date("Y-m-d"));


	    if(!empty($promotionalText) && $promotionalTextPosition =='before' && (empty($promotionalOfferDate) || $promotionalOfferDate >=$currentDate ))
        {  ?>
        <p class="product-simpl-text-promotional"><?php echo $promotionalText; ?></p>
        <?php  } ?>
        <p class="product-simpl-text-note"><?php echo wp_kses_post($string); ?></p>
        <?php
        if(!empty($promotionalText) && $promotionalTextPosition  =='after' && (empty($promotionalOfferDate) || $promotionalOfferDate >=$currentDate ))
        {?>
            <p class="product-simpl-text-promotional"><?php echo $promotionalText; ?></p>
        <?php }
        return ob_get_clean();
    }

    /**
     * Return payment gateway title.
     *
     * @param string $title Title.
     * @param string $id Gateway Id.
     * @return string $title Title.
     * @since    1.0.0
     */
    public function checkout_gateway_title($title, $id)
    {
        if (is_admin()) {
            return $title;
        }
        if (SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG === $id) {

            if ($this->settings['checkout_page_payment_method_title']) {
                $title = $this->settings['checkout_page_payment_method_title'];
            } else {
                $title = $this->strings['payment_method_title'];
            }
        }
        return $title;
    }

    /**
     * Return payment gateway description.
     *
     * @param string $description description.
     * @param string $id Gateway Id.
     * @return string $description description.
     * @since    1.0.0
     */
    public function checkout_gateway_description($description, $id)
    {
        if (is_admin()) {
            return $description;
        }
        if (SIMPL_PAY_IN_3_FOR_WOOCOMMERCE_SLUG === $id) {
            if ($this->settings['checkout_page_payment_method_description']) {
                $description = $this->settings['checkout_page_payment_method_description'];
            } else {
                $description = $this->strings['payment_method_description'];
            }
        }
        return $description;
    }

    /**
     * If woocommerce unable to fetch activated Pay in 3 payment method it will bring back
     * @param $payment_methods
     * @return mixed
     */
    public function simpl_pay_inthree_payments_methods($payment_methods)
    {
        global $woocommerce;

        $price = !empty($woocommerce->cart->total) ? (float)$woocommerce->cart->total : 0;
        $showWidget = $this->showPayInThree($price);

        $payWithSimpl = 'simpl-pay-in-3';
        $checkIfPayInThreeEnabled = WC()->payment_gateways->payment_gateways();
        if (!empty($checkIfPayInThreeEnabled[$payWithSimpl]) &&
            $checkIfPayInThreeEnabled[$payWithSimpl]->enabled == 'yes') {
            if ($showWidget) {
                $payment_methods[$payWithSimpl] = $checkIfPayInThreeEnabled[$payWithSimpl];
            }
        }
        return $payment_methods;
    }

    /**
     * @return void
     */
    public function simplPayinPdpWidget($attr = [])
    {
        if (!empty($attr) && !empty($attr['sku'])) {
            $productId = wc_get_product_id_by_sku(trim($attr['sku']));
            if (empty($productId)) {
                return '';
            }
            return $this->simpl_price_text_by_id($productId);
        } elseif (is_product()) {
            return $this->simpl_price_text();
        }
    }

	/**
	 * @param $price
	 *
	 * @return bool
	 */
	public function showPayInThree($price)
    {
        $minPrice = !empty($this->settings['min_price_limit']) ? (int)$this->settings['min_price_limit'] : '';
        $maxPrice = 25000;
        if (empty($minPrice) || (!empty($minPrice) && $price >= $minPrice && $price <= $maxPrice)) {
            return true;
        }
        return false;
    }
}
