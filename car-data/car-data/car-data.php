<?php
/**
 * Plugin Name: Automotive Inventory
 * Description: Vehicle inventory and REST API integration for the automotive SPA.
 * Version: 1.0.0
 * Author: Ramon Aburto
 * Author URI: https://aburto.dev
 * Text Domain: automotive-inventory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_VERSION', '1.0.0' );
define( 'AI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once AI_PLUGIN_PATH . 'includes/class-plugin.php';

AutomotiveInventory\Plugin::instance();