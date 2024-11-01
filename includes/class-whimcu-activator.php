<?php

/**
 * Fired during plugin activation
 *
 * @link       whimcu
 * @since      1.0.0
 *
 * @package    Whimcu
 * @subpackage Whimcu/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Whimcu
 * @subpackage Whimcu/includes
 * @author     Andrew Nase
 */
class Whimcu_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate()
	{
		if (is_plugin_active(WHIMCU_PLUGIN)) {
			return;
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-whimcu-utils.php';

		$account = self::create_account();
		Whimcu_Utils::update_option('whimcu_api_key', $account->apiKey);
		if (!$account->wcApiKeySet) {
			Whimcu_Utils::update_option('whimcu_activation_step', 'REQUIRE_WC_API_KEYS');
		} else {
			self::activate_account();
		}
	}

	public static function create_account()
	{
		return Whimcu_Utils::http_api_call('POST', '/web/wordpress', [
			'storeUrl'	=> get_site_url()
		]);
	}

	public static function activate_account()
	{
		$account = Whimcu_Utils::http_api_call('POST', '/web/wordpress/activate');
		Whimcu_Utils::update_option('whimcu_ga_web_property_id', $account->gaWebPropertyId);
	}
}
