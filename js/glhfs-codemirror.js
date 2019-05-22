jQuery( document ).ready( function ( $ ) {
	$( '.glhfs-textarea' ).each( function ( i, element ) {
		wp.codeEditor.initialize( element, cm_settings );
	} );
} );
