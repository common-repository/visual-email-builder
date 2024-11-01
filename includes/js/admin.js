jQuery(document).ready(function ($) {
	$( '#test-email').on( 'click', function(){
		$( '#send-test-email').val( true );

		$( this ).attr( 'disabled', true );
		$( '#publish' ).trigger( 'click' );
	});

	// prevent post publishing on Enter key press
	$( '.post-type-wpmnotifications' ).keydown(function(event){
		if(event.keyCode == 13) {
		  event.preventDefault();
		  return false;
		}
	});
});
