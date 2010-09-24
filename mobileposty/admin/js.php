<?php
/*
*	These functions are useful when the browser is making a non text/html request (i.e. - not a "page" request, but something else)
*/

// this function outputs javascript headers, and prevents wordpress from doing anything else
// the result of this is that a plain javascript file can be loaded inside wp-admin
// (requiring the user to be logged in to see the scripts)
// TODO:  maybe add user level rights to this info?  perhaps security not that necessary.
function outputAdminJS() {
	$qs = $_SERVER['QUERY_STRING'];
	
	if ( array_key_exists( 'momo_globalJSVars', $_REQUEST ) ) {
		// we want to show the current visible cats/pages/posts vars.
		// no arguments means No Caching
		jsHeaders();
		// this is *included* because we want php to generate the variables in JSON
		include( MOMO_ADMIN_PATH.'/js/momo-globals.js.php' );
		exit();
	}
	
	if ( array_key_exists( 'momo_ajax', $_REQUEST ) ) {
		// we're doing some kind of ajax operation here!
		// let the ajax script decide what to output.
		include( MOMO_ADMIN_PATH.'/momo-ajax.php');
		
		exit();
	}

}
add_action('admin_init', 'outputAdminJS');

function jsHeaders( $noCache = true ) {
	header( 'Content-type: text/javascript');
	if ( $noCache ){
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
	}
}

?>