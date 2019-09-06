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
		if (is_admin()) {
			add_action( 'admin_enqueue_scripts', array($this, 'agora_enqueue_color_picker') );
		}

		// https://hugh.blog/2012/07/27/wordpress-add-plugin-settings-link-to-plugins-page/
		$name = $plugin_name.'/wp-agora-io.php';
		// add_filter('plugin_action_links_'.$name, array($this, 'plugin_add_settings_link') );

		add_action('wp_ajax_save-agora-setting', array($this, 'saveAjaxSettings'));
	}


	public function saveAjaxSettings() {
		unset($_REQUEST['action']);
		$keys = array_keys($_REQUEST);
		$key = $keys[0];
		$value = $_REQUEST[$key];


		$options = get_option($this->plugin_name);
		if (!$options) {
			$options = array();
		}
		$options[$key] = $value;

 		$r = update_option($this->plugin_name, $options);

		header('Content-Type: application/json');
		echo json_encode(array(
        'updated' => $r
    ));
		wp_die();
	}

	public function register_settings_page() {
		global $_wp_last_object_menu;

		$_wp_last_object_menu++;
		$this->options = get_option( 'agoraio_data' );
		// create new admin page here...
		add_menu_page(
			__('Agora Video', 'agoraio'), 
			__('Agora Channels', 'agoraio'), 
			'manage_options', 'agoraio',
			array($this, 'include_agora_channels_page'), 'dashicons-admin-settings',
			$_wp_last_object_menu );

		$addnew = add_submenu_page( 'agoraio',
			__( 'Add New Agora Channel', 'agoraio' ),
			__( 'Add New Channel', 'agoraio' ),
			'manage_options', 'agoraio-new-channel',
			array($this, 'include_agora_new_channel_page') );

		add_action( 'load-' . $addnew, array($this, 'agora_load_channel_pages'), 10, 0 );

		$settings = add_submenu_page( 'agoraio',
			__( 'Agora Settings', 'agoraio' ),
			__( 'Settings', 'agoraio' ),
			'manage_options', 'agoraio-settings',
			array($this, 'include_agora_settings_page') );

		add_action( 'load-' . $settings, array($this, 'agora_load_settings_pages'), 10, 0 );

	}

	public function include_agora_channels_page() {
		if ( ! class_exists( 'Agora_Channels_List_Table' ) ) {
		  require_once( 'class-agora-channels-list-table.php' );
		}
		$this->channels_obj = new Agora_Channels_List_Table();
		include_once('views/agora-admin-channels.php');
	} 

	public function include_agora_new_channel_page() {
		$post = WP_Agora_Channel::get_current();

		if ( !$post ) {
			$post = WP_Agora_Channel::get_template();
		}
 
    add_meta_box(
    	'agora-form-settings',
    	__('Channel Settings', 'agoraio'),
    	'render_agoraio_channel_form_settings',
    	null,
    	'agora_channel_settings'
    );
    add_meta_box(
    	'agora-form-appearance',
    	__('Channel Appearance', 'agoraio'),
    	'render_agoraio_channel_form_appearance',
    	null,
    	'agora_channel_appearance'
    );

		add_action( 'agoraio_channel_form_settings', array($this, 'handle_channel_form_metabox_settings'), 10, 1 );
		add_action( 'agoraio_channel_form_appearance', array($this, 'handle_channel_form_metabox_appearance'), 10, 1 );

		$post_id = -1;
		include_once('views/agora-admin-new-channel.php');
	}


	public function agora_enqueue_color_picker() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		// die("<pre>".print_r('this', true)."</pre>");
	}


	// http://fieldmanager.org/docs/misc/adding-fields-after-the-title/
	// https://metabox.io/how-to-create-custom-meta-boxes-custom-fields-in-wordpress/
	public function handle_channel_form_metabox_settings($channel) {
		global $wp_meta_boxes;

		do_meta_boxes( get_current_screen(), 'agora_channel_settings', $channel );
		unset( $wp_meta_boxes['post']['agora_channel_settings'] );
	}
	public function handle_channel_form_metabox_appearance($channel) {
		global $wp_meta_boxes;

		do_meta_boxes( get_current_screen(), 'agora_channel_appearance', $channel );
		unset( $wp_meta_boxes['post']['agora_channel_appearance'] );
	}

	public function include_agora_settings_page() {
		$agora_options = get_option($this->plugin_name);
		include_once('views/agora-admin-settings.php');
	}

	// action load after post requests on new channel page
	public function agora_load_channel_pages() {
		global $plugin_page;
		$current_screen = get_current_screen();

		// die("<pre>AGORA Load action:".print_r($current_screen, true)."</pre>");
	}

	public function agora_load_settings_pages() {
		global $plugin_page;
		$current_screen = get_current_screen();
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


function agora_current_action() {
	if ( isset( $_REQUEST['action'] ) and -1 != $_REQUEST['action'] ) {
		return $_REQUEST['action'];
	}

	return false;
}