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

	private static $shortcodeRendered = array();

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = null;

		// Declaration of shortcodes and widgets
		add_shortcode( 'agora-communication', array($this, 'agoraCommunicationShortcode') );
		add_shortcode( 'agora-broadcast', array($this, 'agoraBroadcastShortcode') );

		add_action( 'widgets_init', array($this, 'initAgoraWidgets'));

		$this->settings = get_option($this->plugin_name);
		if (!$this->settings) {
			$this->settings = array();
		}

		// Use this in case of you need custom pages or templates...
		// add_filter( 'template_include', array($this, 'agora_pages'), 99 );
		if (isset($this->settings['customerID'])) {
			require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-wp-agora-cloud-recording.php';
			new AgoraCloudRecording($this->settings);
		}
	}

	/**  Render Agora Commnication shortcode **/
	public function agoraCommunicationShortcode( $atts ) {
		require_once("shortcode-agora-communication.php");
		require_once(__DIR__.'/../includes/token-server/RtcTokenBuilder.php');

		return renderCommnicationShortcode( $this, $atts );
	}

	/**  Render Agora Broadcast shortcode **/
	public function agoraBroadcastShortcode( $atts ) {
		require_once("shortcode-agora-broadcast.php");
		require_once(__DIR__.'/../includes/token-server/RtcTokenBuilder.php');

		return renderBroadcastShortcode( $this, $atts );
	}

	public function enqueueShortcodeStyles($type) {
		$bootstrap_css = 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css';
	  $fontawesome = 'https://use.fontawesome.com/releases/v5.7.0/css/all.css';
	  wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
	  wp_enqueue_style( 'fontawesome', $fontawesome, array('bootstrap'), null, 'all' );
	  wp_enqueue_script( 'AgoraSDK', 'https://cdn.agora.io/sdk/web/AgoraRTCSDK-2.8.0.js', array('jquery'), null );

	  wp_enqueue_script( $this->plugin_name.'-screen', plugin_dir_url( __FILE__ ) . 'js/screen-share.js', array( 'jquery' ), $this->version, false );
	  
	  $scriptUI = $type==='broadcast' ? 'js/broadcast-ui.js' : 'js/communication-ui.js';
	  wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . $scriptUI, array( 'jquery' ), $this->version, false );
	}


	public function getShortcodeAttrs($shortcode, $attrs) {
		$instance = shortcode_atts(
	      array(
	        'channel_id' => 0,
	        'audio' => 'true',
	        'video' => 'true',
	        'screen' => 'false',
	        'screenprofile' => '480p_2',
	        'videoprofile' => '480p_9' // https://docs.agora.io/en/Video/API%20Reference/web/interfaces/agorartc.stream.html#setvideoprofile
	      ), $attrs, $shortcode );

	  if(!$instance) { $instance = []; }

	  if ($instance['video']===$instance['screen']) {
	    // Show some error???
	    $instance['screen'] = $instance['video']==='false' ? 'true' : 'false';
	  }

	  return $instance;
	}

	public function validateShortcode($instance) {
		if (((int)$instance['channel_id'])===0) {
	    return '<p class="error">'.__('Please define the <b>channel_id</b> attribute to use on this shortcode', 'agoraio').'</div>';
	  }


	  if (!isset($this->settings['appId'])) {
	    return '<p class="error">'.__('Please configure your <b>Agora App ID</b> before use this shortcode', 'agoraio').'</div>';
	  }

	  return false;
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
			$bootstrap_css = 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css';
			wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
		}
	}

	public function enqueue_scripts() {
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ui.js', array( 'jquery' ), $this->version, false );

		// isset($this->api_data['agora_bootstrap']) ? $this->api_data['agora_bootstrap'] : '';
		$use_bootstrap = false;
		if($use_bootstrap==='true') {
			$bootstrap_js = 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js';
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

	public static function isShortcodeRendered($shortcode) {
    return isset(self::$shortcodeRendered[$shortcode]);
  }

  public static function addShortcodeRendered($shortcode) {
    self::$shortcodeRendered[$shortcode] = true;
  }

}
