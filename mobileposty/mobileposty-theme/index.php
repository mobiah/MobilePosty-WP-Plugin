<?php

global $momo_homePageID; // should we load a special page as the home page?

// if not, show the mobile home page only

get_header();

if ( $momo_homePageID != 0 ) {
	// we want to load a specific page as the home page
	query_posts( array( 'page_id' => $momo_homePageID ) );
	if ( have_posts() ) {
		the_post();
		include(get_theme_root().'/'.get_template().'/page.php');
	}
} else {
	// show the default home page content
	include(get_theme_root().'/'.get_template().'/mobile-home.php');
	
}

get_footer();

?>

