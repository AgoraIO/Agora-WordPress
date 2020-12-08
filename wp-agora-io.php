<?php

/**
 * The WP Integration plugin for Agora-io platform
 *
 * @link              https://www.agora.io
 * @since             1.0.0
 * @package           WP_Agora
 *
 * @wordpress-plugin
 * Plugin Name:       WP Agora.io
 * Plugin URI:        https://github.com/digitallysavvy/Agora-Word-Press/
 * Description:       Integrate the Agora Communication and Streaming platform directly into your wordpress content. This plugin let you create channels and manage their settings directly into WP.
 * Version:           2.0.6
 * Author:            Agora.io
 * Author URI:        https://www.agora.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       agoraio
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version based on SemVer - https://semver.org
 */
define( 'WP_AGORA_IO_VERSION', '2.0.6' );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_agora_io() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-agora-io-activator.php';
	WP_Agora_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_agora_io() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-agora-io-deactivator.php';
	WP_Agora_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_agora_io' );
register_deactivation_hook( __FILE__, 'deactivate_wp_agora_io' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-agora-io.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-agora-io-channel.php';

$wp_agora_plugin = new WP_Agora();
