<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              whimcu
 * @since             1.0.0
 * @package           Whimcu
 *
 * @wordpress-plugin
 * Plugin Name:       Whimcu
 * Plugin URI:        https://whosmycustomer.com
 * Description:       Customer and product analytics designed to maximize sales.
 * Version:           1.0.1
 * Author:            TeamDevBees
 * Author URI:        https://whosmycustomer.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       whimcu
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('WHIMCU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WHIMCU_API_NAMESPACE', 'whimcu/v1');
define('WHIMCU_PLUGIN', 'whimcu/whimcu.php');
define('WHIMCU_WC_API_KEY_NAME', 'Whimcu App');
define('WHIMCU_MAIN_APP_URL', 'https://wp-app.whosmycustomer.com');
define('WHIMCU_MAIN_API_URL', 'https://api.whosmycustomer.com');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WHIMCU_VERSION', '1.0.1');

if (!function_exists('is_plugin_active')) {
	include_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

/**
 * Check for the existence of WooCommerce and any other requirements
 */
function whimcu_check_requirements()
{
	if (is_plugin_active('woocommerce/woocommerce.php')) {
		return true;
	} else {
		add_action('admin_notices', 'whimcu_missing_wc_notice');
		return false;
	}
}

/**
 * Display a message advising WooCommerce is required
 */
function whimcu_missing_wc_notice()
{
	$class = 'notice notice-error';
	$message = __('Whimcu requires WooCommerce to be installed and active.', 'Whimcu');

	printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-whimcu-activator.php
 */
function whimcu_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-whimcu-activator.php';
	Whimcu_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-whimcu-deactivator.php
 */
function whimcu_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-whimcu-deactivator.php';
	Whimcu_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'whimcu_activate');
register_deactivation_hook(__FILE__, 'whimcu_deactivate');


/**
 * Inject GA tracking code
 */
function whimcu_enqueue_scripts() {
	$gaWebPropertyId = get_option('whimcu_ga_web_property_id');
	if (!$gaWebPropertyId) {
		return;
	}
	$script_url = 'https://www.googletagmanager.com/gtag/js';
	$args = 'id=' . $gaWebPropertyId;
	wp_enqueue_script('Whimcu-inject-js', $script_url . '?' . $args, array(), false, true);

	$inline_script  = 'window.dataLayer = window.dataLayer || [];';
	$inline_script .= 'function whimcu_gtag() { dataLayer.push(arguments); }';
	$inline_script .= 'whimcu_gtag("js", new Date());';
	$inline_script .= 'whimcu_gtag("config", "' . $gaWebPropertyId . '");';

	wp_add_inline_script( 'Whimcu-inject-js', $inline_script, 'after');

}

add_action('wp_enqueue_scripts', 'whimcu_enqueue_scripts');

/**
 * Activate account
 */
function whimcu_create_woocommerce_api_keys()
{
	require_once ABSPATH . '/wp-includes/pluggable.php';
	$api_key = get_option('whimcu_api_key');
	$store_url = get_site_url();
	$params = [
		'wc-auth-version' => '1',
		'wc-auth-route' => 'authorize',
		'app_name' => WHIMCU_WC_API_KEY_NAME,
		'scope' => 'read_write',
		'user_id' => $api_key,
		'return_url' => "{$store_url}/wp-admin/plugins.php",
		'callback_url' => WHIMCU_MAIN_API_URL . '/wordpress/install'
	];
	$query_string = http_build_query($params);
	$redirect_url = $store_url . '?' . $query_string;

	wp_redirect($redirect_url);
	exit;
}

function whimcu_activate_account()
{
	global $pagenow;
	$activation_step = get_option('whimcu_activation_step');
	if ($pagenow !== 'plugins.php' || !$activation_step) {
		return;
	}
	require_once plugin_dir_path(__FILE__) . 'includes/class-whimcu-utils.php';
	if ($activation_step === 'REQUIRE_WC_API_KEYS') {
		Whimcu_Utils::update_option('whimcu_activation_step', 'REQUESTED_WC_API_KEYS');
		whimcu_create_woocommerce_api_keys();
	} else if ($activation_step === 'REQUESTED_WC_API_KEYS') {
		Whimcu_Utils::delete_option('whimcu_activation_step');
		try {
			$account = Whimcu_Utils::http_api_call('POST', '/wordpress/activate');
			Whimcu_Utils::update_option('whimcu_ga_web_property_id', $account->gaWebPropertyId);
		} catch (Exception $e) {
			deactivate_plugins(plugin_basename(__FILE__));
		}
	}
}

whimcu_activate_account();

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-whimcu.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function whimcu_run()
{
	if (whimcu_check_requirements()) {
		$plugin = new Whimcu();
		$plugin->run();
	}
}

whimcu_run();
