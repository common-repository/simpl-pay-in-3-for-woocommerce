=== Simpl Pay-in-3 for WooCommerce ===
Contributors: getsimpl
Tags: simpl, payments, credit, pay later, pay in 3, woocommerce
Requires at least: 4.6
Tested up to: 6.0
Stable tag: 1.2.5
Requires PHP: 7.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is Simpl Pay-in-3 Checkout Payment Gateway for WooCommerce

== Description ==

Give your customers the option to purchase any product in 3 simple payments by using Simpl. The “Simpl Pay-in-3 Gateway for WooCommerce'' plugin provides the option to choose Simpl Pay-in-3 as the checkout payment method.
It supports displaying the Simpl logo and payment calculations below product prices on individual product pages, cart page and the checkout page.

For each payment that is fulfilled by Simpl, an order will be created inside the WooCommerce system  You can easily track these orders in the ‘ORDERS’ section of the woocommerce admin panel. Additionally, we support instant refunds automatically. You can initiate a refund from the order detail page.

== Dependencies ==

1. WordPress v4.6 and later
2. Woocommerce v4.0 and later
3. PHP version 7.3 or greater
4. php-curl extension

== Configuration ==
1. Visit the WooCommerce settings page, and click on the Payments tab.
2. Click on Simpl - Pay in 3 to edit the settings. If you do not see Simpl in the list at the top of the screen make sure you have activated the plugin in the WordPress Plugin Manager.
3. Add in your merchant client id and secret.

== Support ==
If you have any queries/issues with integration, please contact us at merchant-support@getsimpl.com

== Changelog ==
= 1.2.5 =
* fixed Iframe Widget css issue
= 1.2.4 =
* Implemented NEW UI for Pay in 3 Model , Implemented Promotional Text Before And After widget
= 1.2.3 =
* Moved AirBreak integration as code part
= 1.2.2 =
* Moved AirBreak integration as optional thing for error debugging
= 1.2.1 =
* Changed payment alias of simpl + Bug Fixing
= 1.2.0 =
* Changed payment alias of simpl
= 1.1.9 =
* New feature -> Min Price & Max Price limit to show widget on PDP + On Checkout is implemented
* To show Pay in 3 widget anywhere now its simpl to use just use like  [simpl_pdp_payin3_widget sku=valid-product-sku]
= 1.1.8 =
* Reverted back the RENAME payment alias changes
= 1.1.6 =
* Implemented Shortcode for PDP page [simpl_pdp_payin3_widget] + bug fixes
= 1.1.5 =
* Implemented Shortcode for PDP page [simpl_pdp_payin3_widget]
= 1.1.4 =
* Reverted PDP changes'
= 1.1.3 =
* Order total issue for pay-in-3 resolved , Changed payment method alias to 'simpl-pay-in-3'
= 1.1.2 =
* Updated wordpress Version compatibility,Payment method's Image positioning problem on checkout page resolved.
= 1.1.1 =
* Integrated Airbrake for Error Track , Random payment method not visible to Frontend issue resolved
= 1.1.0 =
* Support order status update via Webhook
= 1.0.5 =
* Fixes price text in cart page
= 1.0.4 =
* Minor UI alignment fixes
= 1.0.3 =
* Fixes StringsAPI issue
= 1.0.2 =
* Fixes price text UI issues
= 1.0.1 =
* Adds price text for variable product type
= 1.0.0 =
* Initial Release
