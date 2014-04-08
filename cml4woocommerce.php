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

require_once( CML_WOOCOMMERCE_PATH . "admin/admin.php" );
require_once( CML_WOOCOMMERCE_PATH . "frontend/frontend.php" );

class Cml4Woocommerce {
  public function __construct() {
    //Woocommerce post types
    $this->_post_types = array( 'product', 'product_variation', 'shop_order', 'shop_coupon' );
    
    add_action( 'init', array( & $this, 'rewrite_rules' ), 10 );
  }

  /*
   * allow category slug translation in url
   */
  function rewrite_rules() {
    $permalinks = get_option( "cmlwoo_permalinks", array() );
	$woo = get_option( 'woocommerce_permalinks' );

    foreach( CMLLanguage::get_no_default() as $lang ) {
      if( ! isset( $permalinks[ $lang->id ] ) ||
          empty( $permalinks[ $lang->id ][ 'category_base' ] ) ) continue;

      $category = $permalinks[ $lang->id ][ 'category_base' ];
      if( $category == $woo[ 'category_base' ] ) continue;

      add_rewrite_rule( $category . '/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$','index.php?product_cat=$matches[1]&feed=$matches[2]', 'top' );
      add_rewrite_rule( $category . '/(.+?)/(feed|rdf|rss|rss2|atom)/?$','index.php?product_cat=$matches[1]&feed=$matches[2]', 'top' );
      add_rewrite_rule( $category . '/(.+?)/page/?([0-9]{1,})/?$','index.php?product_cat=$matches[1]&paged=$matches[2]', 'top' );
      add_rewrite_rule( $category . '/(.+?)/?$','index.php?product_cat=$matches[1]', 'top' );
    }

    flush_rewrite_rules();
  }

}

if( is_admin() ) {
	$cml4woocommerce = new Cml4WoocommerceAdmin();
} else {
	$cml4woocommerce = new Cml4WoocommerceFrontend();
}
?>