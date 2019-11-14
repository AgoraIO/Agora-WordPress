<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.agora.io
 * @since      1.0.0
 *
 * @package    WP_Agora
 * @subpackage WP_Agora/includes
 */
class WP_Agora {

	// The unique identifier of this plugin.
	protected $plugin_name;

	// The current version of the plugin.
	protected $version;

	// internals:
	private $plugin_admin;
	public $plugin_public;

	/**
	 * Initialize the core functionality of the plugin.
	 */
	public function __construct() {
		if ( defined( 'WP_AGORA_IO_VERSION' ) ) {
			$this->version = WP_AGORA_IO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-agora-io';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action( 'init', array('WP_Agora_Channel', 'init_agoraio'), 10, 0 );
	}

	

	/**
	 * Load common, admin and public dependencies for this plugin.
	 */
	private function load_dependencies() {

		// Admin Settings
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-agora-io-admin.php';

		// Widgets, shortcodes, and public templates
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-agora-io-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array($this, 'load_plugin_textdomain') );
	}
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'agoraio',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {

		$this->plugin_admin = new WP_Agora_Admin( $this->get_plugin_name(), $this->get_version() );
		
		add_action( 'admin_enqueue_scripts', array($this->plugin_admin, 'enqueue_styles') );
		add_action( 'admin_enqueue_scripts', array($this->plugin_admin, 'enqueue_scripts') );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->plugin_public = new WP_Agora_Public( $this->get_plugin_name(), $this->get_version() );

		add_action( 'wp_enqueue_scripts', array($this->plugin_public, 'enqueue_styles') );
		add_action( 'wp_enqueue_scripts', array($this->plugin_public, 'enqueue_scripts') );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
