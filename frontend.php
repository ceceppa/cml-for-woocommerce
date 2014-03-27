<?php
if ( ! defined( 'ABSPATH' ) ) die();

class Cml4WoocommerceFrontend extends Cml4Woocommerce {
	protected $_metas = array();

	public function __construct() {
    parent::__construct();

    $this->_indexes = get_option( "cml_woo_indexes", array() );

		add_filter( 'the_title', array( & $this, 'get_translated_title' ), 10, 2 );
		add_filter( 'the_content', array( & $this, 'get_translated_content' ), 10, 1 );
		add_filter('the_excerpt', array( & $this, 'get_translated_excerpt' ), 10, 1 );
	}

	function get_meta( $id = null ) {
		if( null == $id ) {
			$id = get_the_ID();
		}

		if( ! isset( $this->_metas[ $id ] ) ) {
			$meta = get_post_meta( $id, "_cml_woo_" . CMLLanguage::get_current_id(), true );

			$this->_metas[ $id ] = $meta;
		} else {
			$meta = $this->_metas[ $id ];
		}

		return $meta;
	}

	function get_translated_title( $title, $id ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) || CMLLanguage::is_default() ) return $title;
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

	function get_translated_excerpt( $excerpt ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) ) return $excerpt;
		if( ! in_array( get_the_ID(), $this->_indexes ) ) return $excerpt;

		$meta = $this->get_meta();

		$c = isset( $meta[ 'short' ] ) ? $meta[ 'short' ] : "";
		if( !empty( $c ) ) $excerpt = $c;

		return $excerpt;
	}
}
?>