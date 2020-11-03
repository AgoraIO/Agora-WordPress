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
	public $version;

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

		$this->settings = get_option($this->plugin_name);
		if (!$this->settings) {
			$this->settings = array();
		}

		// Use this in case of you need custom pages or templates...
		// add_filter( 'template_include', array($this, 'agora_pages'), 99 );
		if (isset($this->settings['customerID'])) {
			require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-wp-agora-cloud-recording.php';
			new AgoraCloudRecording($this->settings, $this);
		}

		$ajaxTokenServer = array($this, 'ajaxTokenServer');
	    add_action( 'wp_ajax_generate_token', $ajaxTokenServer );
	    add_action( 'wp_ajax_nopriv_generate_token', $ajaxTokenServer );

	    $userAvatarAjax = array($this, 'getUserAvatar');
	    add_action( 'wp_ajax_get_user_avatar', $userAvatarAjax );
	    add_action( 'wp_ajax_nopriv_get_user_avatar', $userAvatarAjax );

	    // Page Template loader for FullScreen
	    require_once plugin_dir_path(dirname( __FILE__ )) . 'public/class-wp-agora-page-template.php';
	    new WP_Agora_PageTemplate($this);

	    require_once(__DIR__.'/../includes/token-server/RtcTokenBuilder.php');
	    require_once(__DIR__.'/../includes/token-server/RtmTokenBuilder.php');
	}

	public function getUserAvatar() {
		$uid = isset($_POST['uid']) ? sanitize_key($_POST['uid']) : 0;
		$avatar = get_avatar_data( $uid, array('size' => 168) );
		$user = get_user_by('ID', $uid);
		$userData = null;
		if ($user) {
			$userData = array(
				'display_name' => $user->data->display_name,
				'user_nicename' => $user->data->user_nicename
			);
		}

		header('Content-Type: application/json');
		echo json_encode(array( "avatar" => $avatar, "user" => $userData  ));

		wp_die();
	}

	public function ajaxTokenServer() {
		
		header('Content-Type: application/json');
		if (!isset($_POST['cid'])) {
			header("HTTP/1.1 404 Channel Not Found"); 
			echo '{"error": "Undefined channel!", "code": "404"}';
			wp_die();
			return;
		}


		$appID = $this->settings['appId'];
	    $appCertificate = $this->settings['appCertificate'];
	    
	    if($appCertificate && strlen($appCertificate)>0) {
			$cid = isset($_POST['cid']) ? sanitize_key($_POST['cid']) : 0;
				
			$current_user = wp_get_current_user();
	    	$uid = isset($_POST['uid']) ? sanitize_key($_POST['uid']) : $current_user->ID; // Get current user id
	    	// die("<pre>".print_r($uid, true)."</pre>");
	    	// $uid = intval($uid);

	    	// RTM or RTC
	    	$tokenType = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'RTC';

			$token = $this->generateNewToken($cid, $uid, $tokenType);

			if (is_wp_error( $token )) {
				header("HTTP/1.1 404 Channel Not Found"); 
				echo '{"error": "Channel!", "code": "404"}';
				wp_die();
				return;
			}

	      echo json_encode(array( "token" => $token ));
	    } else {
	      header("HTTP/1.1 400 Token not configured"); 
				echo '{"error": "Token Server not configured!", "code": "400"}';
	    }

	    wp_die();
	}

	//
	public function generateNewToken($channel_id, $uid, $tokenType) {
		$channel = WP_Agora_Channel::get_instance($channel_id);
		if (!$channel->id()) {
			return new WP_Error('Channel', 'Channel not found');
		}

		$appID = $this->settings['appId'];
    	$appCertificate = $this->settings['appCertificate'];

		$channelName = $channel->title();
		
	    // role should be based on the current user host...
	    // $settings = $channel->get_properties();
	    // $current_user = wp_get_current_user();

	    // TODO: Validate if this should be changed according to the current user and current shortcode from the ajax call...
	    $privilegeExpireTs = 0;

	    if ($tokenType==='rtm') {
	    	// $appID, $appCertificate, $userAccount, $role, $privilegeExpireTs
	    	$role = '1';
	    	$token = AgoraRtmTokenBuilder::buildToken($appID, $appCertificate, $uid, $role, $privilegeExpireTs);
	    } else {
	    	$role = 'Role_Publisher';
	    	$token = AgoraRtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs);
	    }

	    return $token;
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
		$bootstrap_js = plugin_dir_url( __FILE__ ) . 'js/bootstrap/bootstrap.min.js';
		$bootstrap_popper_js = plugin_dir_url( __FILE__ ) . 'js/bootstrap/popper.min.js';
	  	$fontawesome = plugin_dir_url( __FILE__ ) . 'css/fontawesome/css/all.min.css';


		wp_enqueue_script( 'AgoraSDK', plugin_dir_url( __FILE__ ).'js/agora/AgoraRTCSDK-3.2.1.100.js', array('jquery'), null );
		wp_enqueue_script( 'AgoraRTM', plugin_dir_url( __FILE__ ).'js/agora/agora-rtm-sdk-1.2.2.js', array('jquery'), null );
		
		wp_enqueue_style( 'fontawesome', $fontawesome, array('bootstrap'), null, 'all' );
		wp_enqueue_script( 'bootstrap_popper', $bootstrap_popper_js, array('jquery'), null );
		wp_enqueue_script( 'bootstrap_js', $bootstrap_js, array('jquery'), null );

		wp_enqueue_script( $this->plugin_name.'-screen', plugin_dir_url( __FILE__ ) . 'js/screen-share.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_script( 'AgoraRTM-setup', plugin_dir_url( __FILE__ ).'js/agora-rtm.js', array('AgoraRTM'), $this->version, true );

		$scriptUI = $type==='broadcast' ? 'js/broadcast-ui.js' : 'js/communication-ui.js';
		wp_enqueue_script( $this->plugin_name.'-ui', plugin_dir_url( __FILE__ ) . $scriptUI, array( 'jquery' ), $this->version, false );

	}


	public function getShortcodeAttrs($shortcode, $attrs) {
		$instance = shortcode_atts(
	      array(
	        'channel_id' => 0,
	        'audio' => 'true',
	        'video' => 'true',
	        'screen' => 'false',
	        'screenprofile' => '720p_1',
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


	// Overall public styles
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-agora-styles.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-agora-io-public.js', array( 'jquery' ), $this->version, false );

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
