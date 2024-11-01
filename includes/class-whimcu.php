<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       whimcu
 * @since      1.0.0
 *
 * @package    Whimcu
 * @subpackage Whimcu/includes
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
 * @package    Whimcu
 * @subpackage Whimcu/includes
 * @author     Andrew Nase
 */
class Whimcu
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Whimcu_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	public function __construct()
	{
		if (defined('WHIMCU_VERSION')) {
			$this->version = WHIMCU_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'whimcu';

		$this->load_dependencies();
		$this->set_locale();
		$this->init_ui();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Whimcu_Loader. Orchestrates the hooks of the plugin.
	 * - Whimcu_i18n. Defines internationalization functionality.
	 * - Whimcu_Admin. Defines all hooks for the admin area.
	 * - Whimcu_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-whimcu-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-whimcu-i18n.php';

		$this->loader = new Whimcu_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Whimcu_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Whimcu_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	private function init_ui()
	{
		add_action('admin_menu', [$this, 'add_menu']);
		add_action('admin_head', [$this, 'render_dashboard_ui']);
	}

	public function add_menu()
	{
		add_menu_page(
			'Whimcu',
			'Whimcu',
			'manage_options',
			'whimcu',
			'',
			plugins_url( '/images/whimcu_logo_16x16.png', __FILE__ ),
			57
		);
	}

	public function render_dashboard_ui() {
		$whimcu_dashboard_url = WHIMCU_MAIN_APP_URL . '/dashboard?apiKey=' . get_option('whimcu_api_key');
		?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('ul li#toplevel_page_whimcu a').attr('target','_blank');
                jQuery('ul li#toplevel_page_whimcu a').attr("href", "<?php echo($whimcu_dashboard_url); ?>")
            });
        </script>
        <?php
	}

	public function render_ui()
	{
		$website_url = WHIMCU_MAIN_APP_URL . '?apiKey=' . get_option('whimcu_api_key');
		echo '
			<div style="padding: 16px; width: calc(100% - 32px); height: 100vh;">
				<iframe style="width: 100%; height: 100%;" src="' . $website_url . '" />
			</div>
		';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Whimcu_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
