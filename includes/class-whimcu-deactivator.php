<?php

/**
 * Fired during plugin deactivation
 *
 * @link       whimcu
 * @since      1.0.0
 *
 * @package    Whimcu
 * @subpackage Whimcu/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Whimcu
 * @subpackage Whimcu/includes
 * @author     Andrew Nase
 */
class Whimcu_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function deactivate()
	{
		if (is_plugin_inactive(WHIMCU_PLUGIN)) {
			return;
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-whimcu-utils.php';
		self::deactivate_account();
	}

	public static function deactivate_account()
	{
		try {
			Whimcu_Utils::http_api_call('POST', '/web/wordpress/deactivate');
		} catch (Exception $e) {
			//
		}
		Whimcu_Utils::delete_option('whimcu_api_key');
		Whimcu_Utils::delete_option('whimcu_ga_web_property_id');
	}
}
