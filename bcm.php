<?php
/*
Plugin Name: BCM Security Web Application Plugin
Description: BCM Security APP
Version: 1.0.0
Text Domain: bcm
*/

if ( ! defined ( 'ABSPATH' ) ) exit; // Exit if accessed directly

define ( 'BCM_PATH', plugin_dir_path(__FILE__) );
define ( 'BCM_URL', plugin_dir_url( __FILE__ ) );
define ( 'BCM_BASENAME', plugin_basename(__FILE__) );
define ( 'BCM_VERSION', '1.0' );

require_once( 'inc/class.bcm.php' );
require_once( 'inc/metabox.php' );

// Instantiate.
$bcm = new BCM();
$bcm->initialize();
