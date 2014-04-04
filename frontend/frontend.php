<?php
if ( ! defined( 'ABSPATH' ) ) die();

class Cml4WoocommerceFrontend extends Cml4Woocommerce {
	protected $_metas = array();

	public function __construct() {
    parent::__construct();

    $this->_indexes = get_option( "cml_woo_indexes", array() );

    //Translate product link
    add_filter( 'post_type_link', array( & $this, 'translate_product_link' ), 10, 4 );

    //Tell to wp the original product name :)
    add_filter( 'pre_get_posts', array( & $this, 'change_product_name' ), 0, 1 );

    //Product translations
    add_filter( 'the_title', array( & $this, 'get_translated_title' ), 0, 2 );
    add_filter( 'woocommerce_cart_item_name', array( & $this, 'get_translated_title' ), 10, 3 );
    add_filter( 'the_content', array( & $this, 'get_translated_content' ), 0, 1 );
    add_filter( 'woocommerce_short_description', array( & $this, 'get_translated_description' ), 0, 1 );

    /*
     * When I translate category url I have to inform wordpress which is "original" category.
     * I can't use is_category for translated url of custom categories, so I have to use
     * cml_is_custom_category filter.
     *
     * is_woocommerce_tag detect if current url is a woocommerce category
     */
    add_filter( 'cml_is_custom_category', array( & $this, 'is_woocommerce_category' ), 10, 2 );
    add_filter( 'cml_custom_category_name', array( & $this, 'get_category_name' ), 10, 2 );
    add_filter( 'cml_change_wp_query_values', array( & $this, 'change_wp_query_values' ), 10, 2 );

    //Translate cart product title
    add_filter( 'woocommerce_cart_item_product', array( & $this, 'translate_product' ), 10, 3 );
    add_filter( 'woocommerce_order_get_items', array( & $this, 'translate_items_name' ), 10, 2 );

    //Add language to form, so error will be displayed in current language
    add_action( 'woocommerce_before_checkout_billing_form', array( & $this, 'checkout_form' ), 10 );
  }

	function get_meta( $id = null ) {
		if( null == $id ) {
			$id = get_the_ID();
		}

		$lang = CMLUtils::_get( "_forced_language_id", CMLLanguage::get_current_id() );

		if( ! isset( $this->_metas[ $id ][ $lang ] ) ) {
			$meta = get_post_meta( $id, "_cml_woo_" . $lang, true );

			$this->_metas[ $id ][ $lang ] = $meta;
		} else {
			$meta = $this->_metas[ $id ][ $lang ];
		}

		return $meta;
	}

	function get_translated_title( $title, $id ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) ) return $title;

		//Get request language
		$lang = CMLUtils::_get( "_forced_language_id", CMLLanguage::get_current_id() );
		if( CMLLanguage::is_default( $lang ) ) return $title;

		//In the loop in can't use is_singular so I check if current $id exists
		//in "woocommerce" post types
		if( ! in_array( $id, $this->_indexes ) ) return $title;

		$meta = $this->get_meta( $id );
		if( empty( $meta ) ) return $title;

		$c = isset( $meta[ 'title' ] ) ? $meta[ 'title' ] : "";
		if( !empty( $c ) ) $title = $c;

		return $title;
	}

	function get_translated_content( $content ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) ) return $content;
		if( ! in_array( get_the_ID(), $this->_indexes ) ) return $content;

		$meta = $this->get_meta();

		$c = isset( $meta[ 'content' ] ) ? $meta[ 'content' ] : "";
		if( !empty( $c ) ) $content = $c;

		$content = str_replace( ']]>', ']]&gt;', $content );
		return $content;
	}

	function get_translated_description( $excerpt ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) ) return $excerpt;
		if( ! in_array( get_the_ID(), $this->_indexes ) ) return $excerpt;

		$meta = $this->get_meta();

		$c = isset( $meta[ 'short' ] ) ? $meta[ 'short' ] : "";
		if( !empty( $c ) ) $excerpt = $c;

		return $excerpt;
	}

	function translate_product_link( $permalink, $post, $leavename, $sample ) {
      if( ! in_array( get_post_type( $post ), $this->_post_types ) ||
          empty( CMLUtils::get_permalink_structure() ) ||
          is_preview() )  {
        return $permalink;
      }

      global $wp_rewrite;

      //Current slug
      $lang = CMLUtils::_get( "_forced_language_id", CMLLanguage::get_current_id() );

      //Current language id
      if( CMLLanguage::is_default( $lang ) ) return $permalink;

      $url = explode( "/", untrailingslashit( $permalink ) );
      unset( $url[ count( $url ) - 1 ] );

      //Get translated title
      $title = $this->get_translated_title( $post->post_title, $post->ID );

      $url[] = strtolower( sanitize_title( $title ) );

      return join( "/", $url );
	}

	/*
	 * tell to wp the original name of produt to avoid 404 error
   */
  function change_product_name( $wp_query ) {
    global $wpdb;

    if( ! defined( 'CECEPPA_DB_VERSION' ) ) return;

    if( cml_is_homepage() ||
      CMLLanguage::is_default() ||
      ! isset( $wp_query->query[ 'product' ] ) ) {
      return;
    }

    $product = strtolower( $wp_query->query[ 'product' ] );
    $key = CMLTranslations::search( CMLLanguage::get_current_id(), $product, "_woo_" );

    //Nothing found
    if( empty( $key ) ) return;

    if( ! preg_match( "/\d*$/", $key, $out ) ) return;

    $id = end( $out );
    $post = get_post( $id );

    $wp_query->query[ 'product' ] = $post->post_title; 
    $wp_query->query[ 'name' ] = $post->post_title; 

    $wp_query->query_vars[ 'product' ] = $post->post_title; 
    $wp_query->query_vars[ 'name' ] = $post->post_title;
  }

  /*
   * detect if current url is a woocommerce category
   */
  function is_woocommerce_category( $is_custom, $wp_query ) {
      if( isset( $wp_query->query[ 'product_cat' ] ) ) {
          return true;
      }

      return $is_custom;
  }

  /*
   * return woocommerce category name
   */
  function get_category_name( $cat, $wp_query ) {
      if( ! $this->is_woocommerce_category( false, $wp_query ) ) {
          return $cat;
      }

      return $wp_query->query[ 'product_cat' ];
  }

  /*
   * add missing values to wp_query, so wordpress doesn't show 404 error
   */
  function change_wp_query_values( $wp_query, $cat ) {
    $cat = end ( $cat );

    if( empty( $cat ) ) return $wp_query;

    $wp_query->query[ 'product_cat' ] = $cat;
    $wp_query->query_vars[ 'product_cat' ] = $cat;

    return $wp_query;
  }
    
  function get_translated_slug( $args ) {
    $lang = CMLLanguage::get_default_id();
    $lang = CMLUtils::_get( "_forced_language_id", CMLLanguage::get_current_id() );
    if( CMLLanguage::is_default( $lang ) ) return $args;

    $permalinks = CMLUtils::_get( "_cmlwoo_permalinks", null );
    if( null == $permalinks ) {
      $permalinks = get_option( "cmlwoo_permalinks", array() );

      CMLUtils::_set( "cmlwoo_permalinks", $permalinks );
    }

    if( isset( $permalinks[ $lang ][ 'category_base' ] ) ) {
      $args[ 'rewrite' ][ 'slug' ] = $permalinks[ $lang ][ 'category_base' ];
    }

    return $args;
  }
  
  function translate_items_name( $items ) {
    if( CMLLanguage::is_default() ) return $items;

    foreach( $items as $key => $item ) {
      if( isset( $item[ 'product_id' ] ) ) {
        $items[ $key ][ 'name' ] = $this->get_translated_title( $item[ 'name' ], $item[ 'product_id' ] );
      }
    }
    
    return $items;
  }

  function translate_product( $post, $item, $key ) {
    $post->post->post_title = $this->get_translated_title( $post->post->post_title, $post->id );

    return $post;
  }
  
  function checkout_form( $checkout ) {
    echo '<input type="hidden" name="lang" value="' . CMLLanguage::get_current()->cml_locale . '" />';

    return $checkout;
  }
}

/*
 * I can't use this function in my class or I cant translate
 * checkout page id when order is confirmed ( the class isn't initialized )
 */
//Translate cart and checkout id
function cmlwoo_get_translated_page_id( $id ) {
  if( CMLLanguage::is_default() ) return $id;

  $linked = CMLPost::get_translation( CMLLanguage::get_current_id(), $id );

  if( $linked == 0 ) $linked = $id;

  return $linked;
}

$pages = array( 'cart', 'product', 'myaccount', 'shop', 'change_password', 'checkout' );
foreach( $pages as $page ) {
  add_filter( 'woocommerce_get_' . $page . '_page_id', 'cmlwoo_get_translated_page_id', 10, 1 );
}
?>