<?php
if ( ! defined( 'ABSPATH' ) ) die();

class Cml4WoocommerceAdmin extends Cml4Woocommerce {
	public function __construct() {
    parent::__construct();
		add_action( 'admin_init', array( & $this, 'add_meta_box' ) );

		//Add addon in "Addons page"
		add_filter( 'cml_addons', array( & $this, 'add_addon' ) );

		//Tell to CML to ingnore woocommerce post types, so "Post data" box will not be displayed
		add_filter( 'cml_manage_post_types', array( & $this, 'remove_woocommerce_types' ) );

		//Post title e editors
		add_action( 'edit_form_after_title', array( & $this, 'insert_title_translations' ), 10, 1 );

		//Save translations in post_meta
		add_action( 'save_post', array( & $this, 'save_translations' ), 10, 2 );
		add_action( 'publish_my_custom_post_type', array( & $this, 'save_translations' ), 10, 2 );

		//Wp style & script
    add_action( 'admin_enqueue_scripts', array( & $this, 'enqueue_style' ), 10 );
	}

	function enqueue_style() {
    wp_enqueue_style( 'cmlwoocommerce-style', CML_WOOCOMMERCE_URL . 'css/admin.css' );

    wp_enqueue_script( 'cmlwoocommerce-admin', CML_WOOCOMMERCE_URL . 'js/admin.js' );
	}

	function add_addon( $addons ) {
		$addon = array(
									'addon' => 'woocommerce',
									'title' => 'Woocommerce',
									);
		$addons[] = $addon;

		return $addons;
	}

	function admin_notices() {
		global $pagenow;

		if( ! defined( 'CECEPPA_DB_VERSION' ) ) {
echo <<< EOT
	<div class="error">
		<p>
			<strong>Ceceppa Multilingua for Woocommerce</strong>
			<br /><br />
			Hi there!	I'm just an addon for <a href="http://wordpress.org/plugins/ceceppa-multilingua/">Ceceppa Multilingua</a>, I can't work alone :(
		</p>
	</div>
EOT;
			return;
		}
	}

	function add_meta_box() {
		add_meta_box( 'cml-box-addons', 
									__( 'Woocommerce', 'woocommerce' ), 
									array( & $this, 'meta_box' ), 
									'cml_box_addons_woocommerce' );
	}

	function meta_box() {
?>
	  <div id="minor-publishing">
			<?php _e( 'This addon provide support to Woocommerce', 'cmlcustomizr' ); ?>
		</div>
<?php
	}

	//for cml
	function remove_woocommerce_types( $types ) {
		foreach( $this->_post_types as $key ) {
			unset( $types[ $key ] );
		}

		return $types;
	}

	/*
	 * Add extra <input> field for each language
	 */
	function insert_title_translations( $post ) {
		if( ! defined( 'CECEPPA_DB_VERSION' ) ) return;

		if( ! in_array( $post->post_type, $this->_post_types ) ) {
			return;
		}

		$titles = "";
		$tabs = "";
		$short_tabs = "";
		$editors = "";

		foreach( CMLLanguage::get_all() as $lang ) {
			$label = sprintf( __( 'Product name in %s', 'ceceppaml' ), $lang->cml_language );

			$img = CMLLanguage::get_flag_src( $lang->id );

			$meta = get_post_meta( $post->ID, "_cml_woo_" . $lang->id, true );

			$title = isset( $meta[ 'title' ] ) ? $meta[ 'title' ] : "";
			$content = isset( $meta[ 'content' ] ) ? $meta[ 'content' ] : "";
			if( empty( $content ) ) $content = $post->post_content;

			$short = isset( $meta[ 'short' ] ) ? $meta[ 'short' ] : "";
			if( empty( $short ) ) $short = $post->post_excerpt;


			if( ! $lang->cml_default ) {
$titles .= <<< EOT
<div id="titlewrap" class="cml-hidden cmlwoo-titlewrap">
	<img class="tipsy-s" title="$label" src="$img" />
	<label class="" id="title-prompt-text" for="title_$lang->id">$label</label>
	<input type="text" class="cmlwoo-title" name="cml_post_title_$lang->id" size="30" id="title_$lang->id" autocomplete="off" value="$title"/>
</div>
EOT;
			}

			$active = ( $lang->cml_default ) ? "nav-tab-active" : "";

			if( ! $lang->cml_default )  {	
				echo '<div id="cmlwoo-editor" class="cmlwoo-editor-' . $lang->id . ' cmlwoo-editor-wrapper cml-hidden postarea edit-form-section">';
					wp_editor( htmlspecialchars_decode( $content ), "cml_content_" . $lang->id );
				echo '</div>';

				$settings = array(
					'textarea_name'	=> 'cml_short_content_' . $lang->id,
					'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
					'tinymce' 	=> array(
						'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
						'theme_advanced_buttons2' => '',
					),
					'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
				);

				echo '<div id="cmlwoo-short-editor" class="cmlwoo-short-editor-' . $lang->id . ' cmlwoo-short-editor-wrapper cml-hidden wp-core-ui wp-editor-wrap tmce-active">';
					wp_editor( htmlspecialchars_decode( $short ), "cml_short_content_" . $lang->id, $settings );
				echo '</div>';
			}

$tabs .= <<< EOT
	<a id="cmlwoo-editor-$lang->id" class="nav-tab $active cmlwoo-switch" onclick="CmlWoo.switchTo( $lang->id, '' );">
		<img class="tipsy-s" title="$label" src="$img" />
		$lang->cml_language
	</a>
EOT;

//short description
$img = CMLLanguage::get_flag_img( $lang->id );
$short_tabs .= <<< EOT
	<a id="cmlwoo-short-editor-$lang->id" class="nav-tab $active cmlwoo-short-switch" onclick="CmlWoo.switchTo( $lang->id, 'short-' )">
		$img
		$lang->cml_language
	</a>
EOT;
		}

		echo $titles;

		echo '<h2 class="nav-tab-wrapper cmlwoo-nav-tab">&nbsp;&nbsp;';
		echo $tabs;
		echo '</h2>';

		echo '<h2 class="nav-tab-wrapper cmlwoo-short-nav-tab cmlwoo-nav-tab cml-hidden">&nbsp;&nbsp;';
		echo $short_tabs;
		echo '</h2>';

	}

	/*
	 * save translations in db
	 */
	function save_translations( $post_id, $post ) {
		global $wpdb;

		//Nothing to do for other post types
		if( ! in_array( $post->post_type, $this->_post_types ) ) return;

		foreach( CMLLanguage::get_no_default() as $lang ) {
			if( ! isset( $_POST[ 'cml_post_title_' . $lang->id ] ) ) continue;

			$title = esc_attr( $_POST[ 'cml_post_title_' . $lang->id ] );
			$content = @$_POST[ 'cml_content_' . $lang->id ];
			$short = @$_POST[ 'cml_short_content_' . $lang->id ];

			$meta = array( 'title' => $title,
											'content' => $content,
											'short' => $short );

			//Store translations in meta field
			update_post_meta( $post_id, "_cml_woo_" . $lang->id, $meta );

			//Tell to CML that post exists in all languages
			CMLPost::set_as_unique( $post_id );
		}

		$query = sprintf( "SELECT ID FROM $wpdb->posts WHERE post_type IN ( '%s' ) AND post_status = 'publish'",
												join( "', '", $this->_post_types ) );

		$posts = $wpdb->get_results( $query, ARRAY_N );
		$ids = array();

		foreach( $posts as $post ) {
			$ids[] = $post[ 0 ];
		}

		update_option( "cml_woo_indexes", $ids );
	}
}
