<?php
/*
*	Special functions for when the site is being PREVIEWED from the back end in the mobile theme,
*	not being viewed from a mobile device or browser.
*/

// are we just previewing the site?  check the querystring
function momo_isPreview() {
	return ( array_key_exists( 'momo_previewing', $_GET ) && $_GET['momo_previewing'] == 'true' );
}

function momo_previewInit() {
	// are we previewing settings?  then overwrite some of the global settings variables, just for this time
	if ( momo_isPreview() ) {
		global $momo_enabled, $momo_headerImage, $momo_fontFamily, $momo_homePageID , $momo_themeStyle ;
		
		$momo_enabled = true;
		
		// only overwrite global settings when they've been passed in!
		
		if ( array_key_exists( 'momo_headerImage', $_GET ) ) {
			$momo_headerImage = $_GET['momo_headerImage'];
		}
		if ( array_key_exists( 'momo_fontFamily', $_GET ) && $_GET['momo_fontFamily'] != '' ) { 
			$momo_fontFamily = $_GET['momo_fontFamily'];
		}
		if ( array_key_exists( 'momo_homePageID', $_GET ) && $_GET['momo_homePageID'] != '' && is_numeric($_GET['momo_homePageID']) ) {
			$momo_homePageID = $_GET['momo_homePageID'];
		}
		if ( array_key_exists( 'momo_themeStyle', $_GET ) && $_GET['momo_themeStyle'] != '' ) {
			$momo_themeStyle = $_GET['momo_themeStyle'];
		}
	}
}
add_action( 'init', 'momo_previewInit' );