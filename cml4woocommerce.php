<?php
/*
Plugin Name: Ceceppa Multilingua support for Woocommerce
Plugin URI: http://www.ceceppa.eu/portfolio/ceceppa-multilingua/
Description: Plugin to make Ceceppa Multilingua work with Woocommerce.\nThis plugin required Ceceppa Multilingua 1.4.10.
Version: 0.1
Author: Alessandro Senese aka Ceceppa
Author URI: http://www.alessandrosenese.eu/
License: GPL3
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget, switcher, professional, human, translation, service, multilingua, customizr, theme
*/
// Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'CML_WOOCOMMERCE_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'CML_WOOCOMMERCE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

require_once( CML_WOOCOMMERCE_PATH . "admin.php" );
require_once( CML_WOOCOMMERCE_PATH . "frontend.php" );

class Cml4Woocommerce {
	public function __construct() {
		//Woocommerce post types
		$this->_post_types = array( 'product', 'product_variation', 'shop_order', 'shop_coupon' );
	}
}

if( is_admin() ) {
	$cml4woocommerce = new Cml4WoocommerceAdmin();
} else {
	$cml4woocommerce = new Cml4WoocommerceFrontend();
}

?>