var _cml_is_switching = false;

jQuery( document ).ready( function( $ ) {
	$( '.cmlwoo-titlewrap' ).insertAfter( $( '#titlediv > #titlewrap' ) );
	$( '.cmlwoo-titlewrap' ).removeClass( 'cml-hidden' );

	//Hide label if value is not empty
	$( '.cmlwoo-title' ).each( function() {
		if( $( this ).val() != "" ) {
			$( this ).prev().fadeOut( 0 );
		}
	});

	$( '.cmlwoo-title' ).focus( function() {
		$( this ).prev().fadeOut( 'fast' );
	});

	$( '.cmlwoo-titlewrap input' ).focusout( function() {
		$this = $( this );

		if( $this.val() != "" ) return;

		$this.prev().fadeIn( 'fast' );
	});

	//Move switch tab inside ".wp-editor-tabs" div
	$to = $( '#postdivrich #wp-content-editor-tools .wp-editor-tabs' )

	//move my textarea above #postdivrich
	$( '.cmlwoo-editor-wrapper' ).insertAfter( $( '#postdivrich' ) );

	//move short description after #wp-excerpt-wrap
	$( '.cmlwoo-short-editor-wrapper' ).insertAfter( $( '#wp-excerpt-wrap' ) );
	$( 'h2.cmlwoo-short-nav-tab' ).insertBefore( $( '#wp-excerpt-wrap' ) ).removeClass( 'cml-hidden' );
});

var CmlWoo = {
	switchTo: function( index, type ) {
		jQuery( '.cmlwoo-' + type + 'nav-tab > a' ).removeClass( 'nav-tab-active' );
		jQuery( '.cmlwoo-' + type + 'nav-tab > a#cmlwoo-' + type + 'editor-' + index ).addClass( 'nav-tab-active' );

		jQuery( '.cmlwoo-' + type + 'editor-wrapper' ).addClass( 'cml-hidden' );
		jQuery( '.cmlwoo-' + type + 'editor-' + index ).removeClass( 'cml-hidden' );

		if( index == ceceppaml_admin.default_id ) {
			jQuery( '#cmlwoo-' + type + 'editor' ).addClass( 'cml-hidden' );

			if( type == "" ) {
				jQuery( '#postdivrich' ).removeClass( 'cml-hidden' );
			} else {
				jQuery( '#wp-excerpt-wrap' ).removeClass( 'cml-hidden' );
			}
		} else {
			if( type == "" ) {
				jQuery( '#postdivrich' ).addClass( 'cml-hidden' );
			} else {
				jQuery( '#wp-excerpt-wrap' ).addClass( 'cml-hidden' );
			}
			jQuery( '#cmlwoo-' + type + 'editor.cmlwoo-editor-' + index ).removeClass( 'cml-hidden' );
		}
	}
}