<?php

/**
 * Page Template loader for this plugin
 *
 * @link       https://www.agora.io
 * @since      1.0.0
 *
 * @package    WP_Agora
 * @subpackage WP_Agora/public
 */
class WP_Agora_PageTemplate {

  // array of templates to track.
  protected $templates;

  protected $agora;

  public function __construct($agora) {
    $this->agora = $agora;
    $this->templates = array();

    // Add a filter to the attributes metabox
    add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );

    // Add a filter to the save post to inject out template into the page cache
    add_filter( 'wp_insert_post_data', array( $this, 'register_project_templates' ) );

    // Add a filter to the template include to determine if the page has our 
    // template assigned and return it's path
    add_filter( 'template_include', array( $this, 'view_project_template') );

    // Add your templates to this array.
    $this->templates = array( 'agora-fullscreen-communication.php' => 'Agora.io FullScreen');
  }

  public function add_new_template( $posts_templates ) {
    $posts_templates = array_merge( $posts_templates, $this->templates );
    return $posts_templates;
  }


  public function register_project_templates( $atts ) {
    // Create the key used for the themes cache
    $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

    // Retrieve the cache list. 
    // If it doesn't exist, or it's empty prepare an array
    $templates = wp_get_theme()->get_page_templates();
    if ( empty( $templates ) ) {
      $templates = array();
    } 

    // New cache, therefore remove the old one
    wp_cache_delete( $cache_key , 'themes');

    // Now add our template to the list of templates by merging our templates
    // with the existing templates array from the cache.
    $templates = array_merge( $templates, $this->templates );

    // Add the modified cache to allow WordPress to pick it up for listing
    // available templates
    wp_cache_add( $cache_key, $templates, 'themes', 1800 );

    return $atts;
  }


  public function view_project_template( $template ) {
    // Get global post
    global $post;

    // Return template if post is empty
    if ( !$post ) {
      return $template;
    }
    
    $matches = [];
    $found = preg_match('/channel_id="(.*?)"/mi', $post->post_content, $matches);

    if ($found) {
      global $instance;
      global $channel;
      global $agora;

      $channel_id = $matches[1];
      if (strpos($post->post_content, '[agora-communication')>=0) {
        $instance = $this->agora->getShortcodeAttrs('agora-communication', []);
      } else {
        $instance = $this->agora->getShortcodeAttrs('agora-broadcast', []);
      }
      $channel = WP_Agora_Channel::get_instance($channel_id);
      $agora = $this->agora;

      $bgMatches = [];
      $bgFound = preg_match('/ background="(.*?)"/mi', $post->post_content, $bgMatches);
      if ($bgFound) {
        $instance['background'] = $bgMatches[1];
      }


      wp_enqueue_script( 'AgoraSDK', plugin_dir_url( __FILE__ ).'js/agora/AgoraRTCSDK-3.6.11.js', array('jquery'), null );
      wp_enqueue_script( 'AgoraRTM', plugin_dir_url( __FILE__ ).'js/agora/agora-rtm-sdk-1.2.2.js', array('jquery'), null );

      $bootstrap_css = plugin_dir_url( __FILE__ ) . 'js/bootstrap/bootstrap.min.css';
      $bootstrap_js = plugin_dir_url( __FILE__ ) . 'js/bootstrap/bootstrap.min.js';
      $bootstrap_popper_js = plugin_dir_url( __FILE__ ) . 'js/bootstrap/popper.min.js';
      // wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
      wp_enqueue_script( 'bootstrap_popper', $bootstrap_popper_js, array('jquery'), null );
      wp_enqueue_script( 'bootstrap_js', $bootstrap_js, array('jquery'), null );

      wp_enqueue_style( 'fontawesome', plugin_dir_url( __FILE__ ) . 'css/fontawesome/css/all.min.css', array(), null, 'all' );

      // Return default template if we don't have a custom one defined
      $template_in_use = get_post_meta( $post->ID, '_wp_page_template', true );
      if ( !isset( $this->templates[$template_in_use] ) ) {
        return $template;
      }

      wp_enqueue_script( 'agora-screen-share', plugin_dir_url( __FILE__ ) . 'js/screen-share.js', array( 'jquery' ), $this->agora->version, true );
      wp_enqueue_script( 'AgoraRTM-setup', plugin_dir_url( __FILE__ ).'js/agora-rtm.js', array('AgoraRTM'), $this->agora->version, true );

      $file = plugin_dir_path(__FILE__) . 'views/' . get_post_meta($post->ID, '_wp_page_template', true);

      
      if ( strpos($post->post_content, '[agora-communication')!==FALSE ) {

        wp_enqueue_script( 'agora-communication-client',
          plugin_dir_url( __FILE__ ) .'js/agora-communication-client.js', array('jquery'), $this->agora->version, true );
        wp_enqueue_script( 'agora-communication-ui',
          plugin_dir_url( __FILE__ ) .'js/communication-ui.js', array('jquery'), $this->agora->version, true );

      } else if (strpos($post->post_content, '[agora-broadcast')!==FALSE) {
      
        $current_user = wp_get_current_user();
        $props = $channel->get_properties();
        
        if ((int)$props['host']===$current_user->ID) {
          $file = str_replace('agora-fullscreen-communication.php', 'agora-fullscreen-broadcast.php', $file);

          wp_enqueue_script('broadcast-client',
            plugin_dir_url( __FILE__ ) . "js/agora-broadcast-client.js", array('jquery'), $this->agora->version, true);
          wp_enqueue_script('broadcast-ui', 
            plugin_dir_url( __FILE__ ) . "js/broadcast-ui.js", array('jquery'), $this->agora->version, true);
        } else {
          $file = str_replace('agora-fullscreen-communication.php', 'agora-fullscreen-audience.php', $file);
        }
      }

      // Just to be safe, we check if the file exist first
      if ( file_exists( $file ) ) {
        return $file;
      }
    }

    // Return template
    return $template;
  }
}