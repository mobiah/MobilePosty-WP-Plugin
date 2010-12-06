<?php
/*
Plugin Name: MobilePosty Mobile Site Generator
Plugin URI: http://www.mobiah.com/
Description: Allows wordpress to have a mobile site whose content is a subset of the full site, allowing admins to choose which pages/posts/categories are to show up on the mobile site.  <br /> The mobile site should also resemble a native mobile app.
Version: 0.6
Author: Mobiah 
*/

/*  Copyright 2010  Mobiah http://www.mobiah.com
*/

/*
*	GLOBAL PATHS and variables
*
*/

define('DEBUG', true);
define("MOMO_PLUGIN_FILE", __FILE__);
define("MOMO_PATH", dirname(__FILE__));
define("MOMO_ADMIN_PATH", MOMO_PATH.'/admin');
$pathExploded = explode( '/', MOMO_PATH );
define("MOMO_URL", WP_PLUGIN_URL.'/'.$pathExploded[ count($pathExploded)-1 ]);
define("MOMO_ADMIN_URL", MOMO_URL.'/admin');
define("MOMO_IMG_URL", MOMO_URL.'/images');

define('MOMO_THEME_SWITCHER', 'momo_themeSwitcher');
define('MOMO_MOBILE_THEME', 'mobileposty');
define('MOMO_DEFAULT_MOBILE_THEME', 'mobileposty');
define('MOMO_MOBILE_STYLESHEET', 'mobileposty');
define('MOMO_DEFAULT_MOBILE_STYLESHEET', 'mobileposty');
define('MOMO_THEME_PATH',get_theme_root().'/'.MOMO_MOBILE_THEME);

define('MOMO_VERSION', '0.2');
define("MOMO_REPORTING_ACTION", 'momo_reporting');
define("MOMO_REPORTING_FREQ", 'daily');
define("MOMO_REPORTING_URL", 'http://mobiah.com/mobileposty/reporting.php');


/*
*	INCLUDES
*	Include all php scripts from the admin folder
*/
include_once( MOMO_ADMIN_PATH . '/categories-admin.php' );
include_once( MOMO_ADMIN_PATH . '/general-admin.php' );
include_once( MOMO_ADMIN_PATH . '/js.php' );
include_once( MOMO_ADMIN_PATH . '/momo-config.php' );
include_once( MOMO_ADMIN_PATH . '/momo-content.php' );
include_once( MOMO_ADMIN_PATH . '/pages-admin.php' );
include_once( MOMO_ADMIN_PATH . '/posts-admin.php' );
include_once( MOMO_ADMIN_PATH . '/debug-admin.php' );

// these includes cover all the functions which determine cat/page/post visibility, heirarchy
// and are used throughout the plugin, both in admin and not.
include_once( MOMO_PATH . '/categories.php' );
include_once( MOMO_PATH . '/pages.php' );
include_once( MOMO_PATH . '/posts.php' );
include_once( MOMO_PATH . '/preview.php' );
include_once( MOMO_PATH . '/theme-switcher.php' );
include_once( MOMO_PATH . '/momo-filters.php' );
include_once( MOMO_PATH . '/simple_html_dom.php' );
include_once( MOMO_PATH . '/momo-install.php' );
include_once( MOMO_PATH . '/momo-reporting.php' );


/*	
*	GLOBAL VARIABLES
*	global variables needed throughout the plugin, stored in the options table
*	global arrays for which catgories, posts, and pages are visibile
*		(multi-dimensional arrays for posts and pages, recording which kind of content is visible
*		in these three arrays, the cat/page/post ID is the KEY of the array, not the VALUE
*/
define( 'MOMO_OPTIONS', 'momo_options' );
global $momo_options;
$momo_options = get_option( MOMO_OPTIONS, array() );

// look for a unique identifier for this mobileposty installation.  In the future this may be replaced by
// actual registration for an API key.  For now, if we dont' find a key, generate a random one.
global $momo_key;
if ( is_array($momo_options) && array_key_exists( 'momo_key', $momo_options ) && $momo_options['momo_key'] != '' ) {
	$momo_key = $momo_options['momo_key'];
} else {
	$momo_key = momo_randomString( 25 );  // make a key of random alphanumeric characters, length 25
	$momo_options['momo_key'] = $momo_key;  // put it in the options array
	update_option( MOMO_OPTIONS, $momo_options ); // and save it back to the options table
}

// this variable tells whether or not we have run the server environment compatibility tests.
// if it is blank, they have not been run.  one can set it to blank to re-run the tests.
global $momo_envTest;
if ( is_array($momo_options) && array_key_exists( 'momo_envTest', $momo_options ) ) {
	$momo_envTest = $momo_options['momo_envTest'];
} else {
	$momo_envTest = '';
}

global $momo_enabled;
if ( is_array($momo_options) && array_key_exists( 'momo_enabled', $momo_options ) ) {
	$momo_enabled = $momo_options['momo_enabled'];
} else {
	$momo_enabled = false;
}
// special case - if we're just previewing, enable the mobile site for this one time
if ( momo_isPreview() ){
	$momo_enabled = true;
}

global $momo_useJQTouch;
if ( is_array($momo_options) && array_key_exists( 'momo_useJQTouch', $momo_options ) ) {
	$momo_useJQTouch = $momo_options['momo_useJQTouch'];
} else {
	$momo_useJQTouch = false;
}
// special case - if we're just previewing, don't use jqtouch, it messes with the preview a little.
if ( momo_isPreview() ){
	$momo_useJQTouch = false;
}
// for now... NO JQTOUCH. 2010/09/08
$momo_useJQTouch = false;

global $momo_useThumbs; // resize post/page images with timthumb script?
if ( is_array($momo_options) && array_key_exists( 'momo_useThumbs', $momo_options ) ) {
	$momo_useThumbs = $momo_options['momo_useThumbs'];
} else {
	$momo_useThumbs = false;
}

global $momo_imgWidth; // resize to what width?
if ( is_array($momo_options) && array_key_exists( 'momo_imgWidth', $momo_options ) ) {
	$momo_imgWidth = $momo_options['momo_imgWidth'];
} else {
	$momo_imgWidth = 0;
}

global $momo_imgHeight; // resize to what height?
if ( is_array($momo_options) && array_key_exists( 'momo_imgHeight', $momo_options ) ) {
	$momo_imgHeight = $momo_options['momo_imgHeight'];
} else {
	$momo_imgHeight = 0;
}

global $momo_visibleCats; // an array with the category ID as key, and true/false as value
if ( is_array($momo_options) && array_key_exists( 'momo_visibleCats', $momo_options ) ) {
	$momo_visibleCats = $momo_options['momo_visibleCats'];
} else {
	$momo_visibleCats = array();
}// get the visible categories from the options table

global $momo_catParents; // an array with the category ID as key, and the parent category ID as value
$momo_catParents = momo_makeCatParents(); // get each category's ancestors from the database

global $momo_visiblePages; // an array with the page ID as key, and a sub array with the visibility of the page / author/date / images / comments 
if ( is_array($momo_options) && array_key_exists( 'momo_visiblePages', $momo_options ) ) {
	$momo_visiblePages = $momo_options['momo_visiblePages'];
} else {
	$momo_visiblePages = array();
}// get the visible pages from the options table

global $momo_visiblePosts; // an array with the post ID as key, and a sub array with the visibility of the page / author/date / images / comments 
if ( is_array($momo_options) && array_key_exists( 'momo_visiblePosts', $momo_options ) ) {
	$momo_visiblePosts = $momo_options['momo_visiblePosts'];
} else {
	$momo_visiblePosts = array();
}// get the visible posts from the options table

global $momo_headerImage;  // the url of the header image to use in the mobile site
if ( is_array($momo_options) && array_key_exists( 'momo_headerImage', $momo_options ) ) {
	$momo_headerImage = $momo_options['momo_headerImage'];
} else {
	$momo_headerImage = '' ; // set default here
}// get the image they've chosen/uploaded for their header image.

global $momo_fontFamily;  // the css-style font family to be used in the mobile site
if ( is_array($momo_options) && array_key_exists( 'momo_fontFamily', $momo_options ) ) {
	$momo_fontFamily = $momo_options['momo_fontFamily'];
} else {
	$momo_fontFamily = 'Arial, Helvetica, sans-serif' ; // set default here
}// get which font style they've chosen, if any.

global $momo_homePageID;  // get which page they've chosen for their mobile home page, if any.
if ( is_array($momo_options) && array_key_exists( 'momo_homePageID', $momo_options ) ) {
	$momo_homePageID = $momo_options['momo_homePageID'];
} else {
	$momo_homePageID = 0 ; // set default here
}

global $momo_themeStyle; // get which theme style they've chosen, if any. 
if ( is_array($momo_options) && array_key_exists( 'momo_themeStyle', $momo_options ) ) {
	$momo_themeStyle = $momo_options['momo_themeStyle'];
} else {
	$momo_themeStyle = 'neutral' ; // set default here
}

global $momo_showSwitcherLink; // the variable which controls whether or not the theme switcher link shows in the footer.
if ( is_array($momo_options) && array_key_exists( 'momo_showSwitcherLink', $momo_options ) ) {
	$momo_showSwitcherLink = $momo_options['momo_showSwitcherLink'];
} else {
	$momo_showSwitcherLink = 'mobile' ; // set default here
}

// Does the combination of useragent and cookie indicate that we should show the mobile version of the site?
global $momo_isMobile;


global $momo_fonts;  // this array is useful in setting up the pull-down menus for admins to choose fonts.
$momo_fonts = array(
	"Arial/Helvetica" => "Arial, Helvetica, sans-serif",
	"Courier" => "'Courier New', Courier, monospace",
	"Georgia" => "Georgia, 'Times New Roman', Times, serif",
	"Lucida Console/Monaco" => "'Lucida Console', Monaco, monospace",
	"Lucida Grande" => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
	"Palatino/Book Antiqua" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
	"Tahoma/Geneva" => "Tahoma, Geneva, sans-serif",
	"Times New Roman" => "'Times New Roman', Times, serif",
	"Trebuchet/Helvetica" => "'Trebuchet MS', Helvetica, sans-serif",
	"Verdana/Geneva" => "Verdana, Geneva, sans-serif",
	"MS Sans Serif/Geneva" => "MS Sans Serif', Geneva, sans-serif",
	"MS Serif/New York" => "'MS Serif', 'New York', serif",
);

global $momo_styles; // this array is useful in setting up the pull-down menus for admins to choose Theme styles
$momo_styles = array(
	"Neutral" => "neutral",
	"Black/Yellow" => "black-yellow",
	"White/Black" => "white-black",
	"Gray" => "gray",
	"White" => "white",
	"Red/White" => "red-white",
	"Red/Black" => "red-black",
	"Orange/Blue" => "orange-blue",
	"Green" => "green",
	"Blue/Black" => "blue-black",
	"Blue/White" => "blue-white",
	"Blue/Gray" => "blue-gray",
	"Purple" => "purple",
	"TriColor" => "tricolor",
	"Brown" => "brown",
);





// and one hook  which sets global variables whose computation requires a little more
// running of code than should happen in this main plugin file.  
// wait until plugins_loaded - but run it at the BEGINNING of that hook (hence the 1).
add_action( 'plugins_loaded', 'momo_globalsInit', 1 );
function momo_globalsInit() {
	global $momo_isMobile, $momo_pageParents, $momo_postCats;
	$momo_isMobile = momo_testForMobile(); // determine if we're viewing the mobile version of the site
	$momo_pageParents = momo_makePageParents(); // get each page's ancestors from the database
	$momo_postCats = momo_makePostCats(); // for each post, make an array containing all category ids which have this post
}

/*	
*	-----------------------------------------------------------
*	GENERAL USE FUNCTIONS
*	-----------------------------------------------------------
*/

// useful for debugging
if ( !function_exists( 'pre' ) ) {
function pre( $value = '!@#$%^&*()_!@#$%^&*()' ) {

	if ( $value === '!@#$%^&*()_!@#$%^&*()' ) {
		// dummy value, show the stack trace
		echo('<pre>'.var_export(debug_backtrace(),true).'</pre>'."<br />\n");
		return;
	}
	echo('<pre>'.var_export($value,true).'</pre>'."<br />\n");
}
}

/*
*	random string generator
*/
function momo_randomString($length = 10, $letters = '1234567890qwertyuiopasdfghjklzxcvbnm') {
	$s = '';
	$lettersLength = strlen($letters)-1;
	for($i = 0 ; $i < $length ; $i++) {
		$s .= $letters[rand(0,$lettersLength)];
	}
	return $s;
} 

?>
