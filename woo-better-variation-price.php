<?php
/*
Plugin Name: Better Variation Price for Woocommerce
Plugin Slug: wbvp
Text Domain: better-variation-price-for-woocommerce
Description: Replace the Woocommerce variable products price range with the lowest price or the selected variation price.
Version: 1.3.0
Requires Plugins: woocommerce
Requires at least: 5.8
Tested up to: 6.6
WC requires at least: 5.5
WC tested up to: 9.3
Author: Josserand Gallot
Author URI: https://josserandgallot.com/
Domain Path: /languages
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('WBVP', false)) {
	require_once dirname(__FILE__) . '/classes/WBVP.php';
}

$wbvp = new WBVP();
$wbvp->initialize();
