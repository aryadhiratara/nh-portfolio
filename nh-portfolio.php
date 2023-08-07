<?php
	
/**
* Plugin Name: NH Portfolio
* Description: Portfolio Post Type Plugin.
* Author: Arya Dhiratara
* Author URI: https://thinkdigital.co.id
* Version: 1.0.0
* Requires at least: 5.8
* Requires PHP: 7.4
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: nh-portfolio
*/

namespace NhPortfolio;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* Start defining constant */

define('NH_PORTFOLIO_SLUG', 'nh-portfolio');
define('NH_PORTFOLIO_VERSION', '1.0.0');


define("NH_PORTFOLIO_DIR", plugin_dir_path(__FILE__)); // for php files, *returns the servers filesystem directory path, pointing to the current plugin folder
define("NH_PORTFOLIO_URL", plugin_dir_url(__FILE__)); // for assets files (css, js, img, etc), *returns the web address of the current plugin folder

define("NH_PORTFOLIO_ADMIN_ASSETS_URL", NH_PORTFOLIO_URL . 'admin/');
define("NH_PORTFOLIO_PUBLIC_ASSETS_URL", NH_PORTFOLIO_URL . 'public/');


/* End defining constant */

// include the required main plugin function file
include_once(NH_PORTFOLIO_DIR . '/includes/Core.php');

// Initialize the plugin
add_action( 'plugins_loaded', 'NhPortfolio\nh_portfolio_init' );
function nh_portfolio_init() {
    Core::init();
}

/* Activation and Deactivation hooks */

$plugin_file = plugin_dir_path( __FILE__ );

// Activation hook callback
register_activation_hook( $plugin_file, array( 'NhPortfolio\Core', 'activate' ) );

// Deactivation hook callback
register_deactivation_hook( $plugin_file, array( 'NhPortfolio\Core', 'deactivate' ) );