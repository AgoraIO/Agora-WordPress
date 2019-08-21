<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.agora.io
 * @since      1.0.0
 *
 * @package    WP_Agora
 * @subpackage WP_Agora/admin
 */
class WP_Agora_Admin {

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', array($this,'register_settings_page'));
		// add_action('admin_init', array($this,'register_agora_settings'));

		// https://hugh.blog/2012/07/27/wordpress-add-plugin-settings-link-to-plugins-page/
		$name = $plugin_name.'/wp-agora-io.php';
		// add_filter('plugin_action_links_'.$name, array($this, 'plugin_add_settings_link') );
	}

	public function register_settings_page() {
		// add_submenu_page('options-general.php',...)
		$this->options = get_option( 'agoraio_data' );
		// create new admin page here...
	}

	public function include_admin_template() {
		include_once('views/wp-agora-io-admin-settings.php');
	}

	public function plugin_add_settings_link($links) {
		$url = 'options-general.php?page='.$this->settings_slug;
		$links[] = '<a href="'. esc_url( get_admin_url(null, $url) ) .'">'.__('Settings').'</a>';

		return $links;
	}

	// Admin styles for settings pages...
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-agora-io-admin.css', array(), $this->version, 'all' );
	}

	// Admin scripts for ajax requests on settings pages...
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-agora-io-admin.js', array( 'jquery' ), $this->version, false );
	}

}
