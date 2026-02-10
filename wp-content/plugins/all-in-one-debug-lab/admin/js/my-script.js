jQuery(document).ready(function($) {
	
	if (typeof jQuery('#aiodl-debug-log')[0] != 'undefined') {
        jQuery('#aiodl-debug-log').animate({scrollTop: jQuery('#aiodl-debug-log')[0].scrollHeight}, 1000);
    }
	if (typeof jQuery('#savequeries-log')[0] != 'undefined') {
        jQuery('#savequeries-log').animate({scrollTop: jQuery('#savequeries-log')[0].scrollHeight}, 1000);
    }	
	
	$( '#aiodl-debug-log' ).css( 'background-color', '#fff' );
	$( '#clearLogFile' ).click(function(){
		alert('Are you sure you want to clear the file?');
	});
})	