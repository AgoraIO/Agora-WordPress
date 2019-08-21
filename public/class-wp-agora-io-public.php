<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.agora.io
 * @since      1.0.0
 *
 * @package    WP_Agora
 * @subpackage WP_Agora/public
 */
class WP_Agora_Public {

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Declaration of shortcodes and widgets
		add_shortcode( 'agora-communication', array($this, 'agoraCommunicationShortcode') );
		add_shortcode( 'agora-broadcast', array($this, 'agoraBroadcastShortcode') );

		add_action( 'widgets_init', array($this, 'initAgoraWidgets'));

		// Use this in case of you need custom pages or templates...
		// add_filter( 'template_include', array($this, 'agora_pages'), 99 );
	}

	/**  Render Agora Commnication shortcode **/
	public function agoraCommunicationShortcode( $atts ) {
		// require_once("shortcode.agora-communication.php");
		// return renderCommnicationShortcode( $this, $atts );
	}

	/**  Render Agora Broadcast shortcode **/
	public function agoraBroadcastShortcode( $atts ) {
		// require_once("shortcode.agora-broadcast.php");
		// return renderBroadcastShortcode( $this, $atts );
	}


	public function initAgoraWidgets() {
		// include("widget.agora-something.php");
		// $agoraWidget = new Agora_Widget();
		// register_widget( $agoraWidget );
	}


	// Overall public styles
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-agora-io-public.css', array(), $this->version, 'all' );

		// isset($this->plugin_data['agora_bootstrap']) ? $this->plugin_data['agora_bootstrap'] : '';
		// TODO: Auto detect bootstrap or use a custom one version of bootstrap for CSS Styles
		$use_bootstrap = false;
		if($use_bootstrap==='true') {
			$bootstrap_css = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css';
			wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-agora-io-public.js', array( 'jquery' ), $this->version, false );

		// isset($this->api_data['agora_bootstrap']) ? $this->api_data['agora_bootstrap'] : '';
		$use_bootstrap = false;
		if($use_bootstrap==='true') {
			$bootstrap_js = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js';
			wp_enqueue_script( 'bootstrap', $bootstrap_js, array( 'jquery' ), null, true );
		}

		// add data before JS plugin
		// useful to load dynamic settings and env vars
		add_action( 'wp_footer', array($this, 'createPublicJSvars'), 1);
	}

		// Create public JS Variables to pass to external script
	public function createPublicJSvars () {
		$vars = 'var ajax_url="'.admin_url( 'admin-ajax.php' ).'";';

		// append here more settings vars
		
		// return $vars;
		echo '<script>'.$vars.'</script>';
	}

}
