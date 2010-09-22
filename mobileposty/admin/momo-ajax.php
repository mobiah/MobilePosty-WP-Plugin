<?php
/*
*	This file should handle most ajax calls created by momo affected pages.
*	NOTE!!!! Mobileposty uses a slightly different method of calling ajax from geo/dyna/social.
*	In admin/js.php, there is a hook which looks for a momo_ajax request variables
*	and if it finds it, then it includes this file.
*
*/

// call an arbitrary plugin function based on what's in the querystring
// (but only momo functions! don't just let someone call any function... jeez.)
if ( array_key_exists( 'momo_callFunction', $_GET ) 
	&& strpos( $_GET['momo_callFunction'], 'momo_' ) === 0
	&& function_exists($_GET['momo_callFunction']) ) {
		$momo_callFunction = $_GET['momo_callFunction'];
		$momo_callFunction();
}
return;

/*
*	enable or disable the mobile version of the site.
*/
function momo_enabled() {
	// we are setting the enabled status of the mobile site.
	global $momo_options, $momo_enabled;
	if ( $_GET['momo_enabled'] == 'true' ) {
		$momo_options['momo_enabled'] = true;
		$momo_enabled = true;
		echo("Mobile Site Enabled!");
	} elseif ( $_GET['momo_enabled'] == 'false' ) {
		$momo_options['momo_enabled'] = false;
		$momo_enabled = false;
		echo("Mobile Site Disabled.");
	}
	// then update the value in the options table
	update_option( MOMO_OPTIONS, $momo_options);
	return;
}

/*
*	Save the visibility settings of from the mobile content page
*/	
function momo_saveVisibility() {
	global $momo_visibleCats, $momo_visiblePages, $momo_visiblePosts;
	
	$oldCatCount = count($momo_visibleCats);
	$oldPageCount = count($momo_visiblePages);
	$oldPostCount = count($momo_visiblePosts);
	// decode all three array from the JSON posted data.
	$momo_visibleCats = momo_jsonDecode(stripslashes($_POST['momo_visibleCats']) ,true);
	$momo_visiblePages = momo_jsonDecode(stripslashes($_POST['momo_visiblePages']) ,true);
	$momo_visiblePosts = momo_jsonDecode(stripslashes($_POST['momo_visiblePosts']) ,true);
	
	// if we couldn't decode the data, this is no good.  show an error
	// and don't overwrite the current options.
	if ( is_null($momo_visiblePosts) || !is_array($momo_visiblePosts) 
		|| is_null($momo_visiblePages) || !is_array($momo_visiblePages)
		|| is_null($momo_visibleCats) || !is_array($momo_visibleCats) ) {
		echo("Error: Problem Decoding Data - Not Saved. (sorry, that's our fault.)");
		return;
	}
	
	// otherwise save the global variables
	global $momo_options;
	$momo_options['momo_visiblePages'] = $momo_visiblePages;
	$momo_options['momo_visiblePosts'] = $momo_visiblePosts;
	$momo_options['momo_visibleCats'] = $momo_visibleCats;
	// then update the value in the options table
	update_option( MOMO_OPTIONS, $momo_options);
	// and say something nice.
	echo( 'Changes Saved!' );
	return;
}

/*
*	turn Jqtouch on or off
*/
function momo_useJQTouch () {
	// we are setting the "use jqtouch" setting.
	global $momo_options, $momo_useJQTouch;

	if ( $_GET['momo_useJQTouch'] == 'true' ) {
		$momo_options['momo_useJQTouch'] = true;
		$momo_useJQTouch = true;
		echo("JQTouch now in use!");
	} elseif ( $_GET['momo_useJQTouch'] == 'false' ) {
		$momo_options['momo_useJQTouch'] = false;
		$momo_useJQTouch = false;
		echo("JQTouch no longer used.");
	}
	// then update the value in the options table
	update_option( MOMO_OPTIONS, $momo_options);
	return;
}

/*
*	Save the settings involving timthumb.php and resizing images
*/
function momo_saveUseThumbs() {
	// we are setting the "use jqtouch" setting.
	global $momo_options, $momo_useThumbs, $momo_imgWidth, $momo_imgHeight;

	if ( array_key_exists( 'momo_useThumbs', $_POST ) && $_POST['momo_useThumbs'] == 'true' ) {
		$momo_options['momo_useThumbs'] = true;
		$momo_useThumbs = true;
		echo("Settings saved - now resizing images.");
	} elseif ( array_key_exists( 'momo_useThumbs', $_POST ) &&  $_POST['momo_useThumbs'] == 'false' ) {
		$momo_options['momo_useThumbs'] = false;
		$momo_useThumbs = false;
		echo("Settings saved - no longer using image resizer.");
	}

	if ( array_key_exists( 'momo_imgWidth', $_POST ) ) {
		$momo_options['momo_imgWidth'] = preg_replace("/[^0-9]+/", '', $_POST['momo_imgWidth'] );
		$momo_imgWidth = $momo_options['momo_imgWidth'];
	}
	if ( array_key_exists( 'momo_imgHeight', $_POST ) ) {
		$momo_options['momo_imgHeight'] = preg_replace("/[^0-9]+/", '', $_POST['momo_imgHeight'] );
		$momo_imgHeight = $momo_options['momo_imgHeight'];
	}

	// then update the value in the options table
	update_option( MOMO_OPTIONS, $momo_options);
	return;
}

/*
*	Save their choice of when to show the theme switcher link
*/
function momo_saveSwitcherLink () {
	global $momo_options;

	if ( array_key_exists( 'momo_showSwitcherLink', $_GET ) ) {
		$momo_options['momo_showSwitcherLink'] = $_GET['momo_showSwitcherLink'];
		update_option( MOMO_OPTIONS, $momo_options);
		echo("Theme switcher link settings saved.");
	}
}
/*
*	Save whichever font they chose
*/
function momo_saveFont () {
	global $momo_options;
	
	if ( array_key_exists( 'momo_fontFamily', $_POST ) ) {
		$momo_options['momo_fontFamily'] = stripslashes($_POST['momo_fontFamily']);
		// then update the value in the options table
		update_option( MOMO_OPTIONS, $momo_options);
		echo("Font Saved.");
	}
}

/*
*	Save which page ID should be the homepage for the mobile site
*/
function momo_saveHomePageID() {
	global $momo_options;
	
	if ( array_key_exists( 'momo_homePageID', $_POST ) ) {
		$momo_options['momo_homePageID'] = stripslashes($_POST['momo_homePageID']);
		// then update the value in the options table
		update_option( MOMO_OPTIONS, $momo_options);
		echo("Mobile Home Page Saved.");
	}
}

/*
*	Save the chosen header image URL
*/
function momo_saveHeaderImage() {
	global $momo_options;
	
	if ( array_key_exists( 'momo_headerImage', $_POST ) ) {
		$momo_options['momo_headerImage'] = stripslashes($_POST['momo_headerImage']);
		// then update the value in the options table
		update_option( MOMO_OPTIONS, $momo_options);
		echo("Header Image Saved.");
	}
}

/*
*	Save the chosen header image URL
*/
function momo_saveThemeStyle() {
	global $momo_options;
	
	if ( array_key_exists( 'momo_themeStyle', $_POST ) ) {
		$momo_options['momo_themeStyle'] = stripslashes($_POST['momo_themeStyle']);
		// then update the value in the options table
		update_option( MOMO_OPTIONS, $momo_options);
		echo("Mobile Theme Style Saved.");
	}
}

/*
*	Save all mobile theme settings from momo_config.php
*/
function momo_saveAllTheme() {
	global $momo_options;

	if ( array_key_exists( 'momo_fontFamily', $_POST ) ) {
		$momo_options['momo_fontFamily'] = stripslashes($_POST['momo_fontFamily']);
	}
	if ( array_key_exists( 'momo_homePageID', $_POST ) ) {
		$momo_options['momo_homePageID'] = stripslashes($_POST['momo_homePageID']);
	}
	if ( array_key_exists( 'momo_headerImage', $_POST ) ) {
		$momo_options['momo_headerImage'] = stripslashes($_POST['momo_headerImage']);
	}
	if ( array_key_exists( 'momo_themeStyle', $_POST ) ) {
		$momo_options['momo_themeStyle'] = stripslashes($_POST['momo_themeStyle']);
	}
	
	update_option( MOMO_OPTIONS, $momo_options);
	echo("All mobile theme options saved.");
}

/*
*	generate the phone preview in the Content Area
*/
function momo_genPreview () {
	global $momo_options, $momo_visibleCats, $momo_visiblePages, $momo_visiblePosts;
	
	$page = $_GET['momo_previewPage'];
	if ( $page == '' ) {
		echo("no page specified, oops.");
		return;
	}
	
	// decode all three array from the JSON posted data.
	$momo_visibleCats = momo_jsonDecode(stripslashes($_POST['momo_visibleCats']) ,true);
	$momo_visiblePages = momo_jsonDecode(stripslashes($_POST['momo_visiblePages']) ,true);
	$momo_visiblePosts = momo_jsonDecode(stripslashes($_POST['momo_visiblePosts']) ,true);
	
	$baseURL = get_bloginfo('url').'/wp-admin/?momo_ajax&momo_callFunction=momo_genPreview&momo_previewPage=';

	//add the appropriate filters, only showing cats/pages/posts which are set to visible 
	add_action('pre_get_posts', 'momo_hidePosts');
	add_filter('list_terms_exclusions','momo_hideCats');
	
	// first, the default page to display:  the home page
	if ( $page == 'home' ) {
		$pageArgs = array(
		  'orderby' => 'menu_order',
		  'order' => 'ASC',
		  'post_type' => 'page',
		  'post_parent' => 0,
		  'nopaging' => true,
		  'post_status' => 'publish,private',
		  );
		$homePages = new WP_Query($pageArgs);

		$catArgs = array(
			'child_of' => 0,
			'depth' => 1,
		);
		$topLevelCats = get_categories( $catArgs );

		?>
		<h1 class="momo_previewH1"><? _e('Mobile Site Home');?></h1>
		<h3 class="momo_previewH3"><? _e('Pages');?></h3>
		<ul id="previewHomePages" class="momo_previewNav">
		<?php 
		if ($homePages->have_posts()) { 
		
		while ($homePages->have_posts()) {

			$homePages->the_post();
		?>
			<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'page-'.get_the_id()?>'); return false;" ><?php the_title();?></a></li>
		<?php
		} // end while ($homePages->have_posts()) {
		?>
		</ul>
		<?php
		} // end if ($homePages->have_posts()) { 

		if ( count($topLevelCats) > 0 ) {
		?>
		<h3 class="momo_previewH3"><? _e('Categories');?></h3>
		<ul id="previewHomeCats" class="momo_previewNav">
		<?php
		foreach ( $topLevelCats as $catObj ) {
			if ( $catObj->category_parent == '0' ) {
			?>
			<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'cat-'.$catObj->term_id?>'); return false;" ><?=$catObj->name?></a></li>
			<?php
			}
		} // end foreach ( $topLevelCats as $catObj ) {
		?>
		</ul>
		<?php
		} // end if ( count($topLevelCats) > 0 ) {
		return;
	}
	
	// show category pages
	if ( strpos( $page, 'cat-' ) === 0 ) {
		$catID = str_replace( 'cat-', '', $page );
		$catObj = get_category( $catID );
		$subCats = get_categories( array( 'child_of' => $catID, 'hide_empty' => false, 'title_li' => ''  ) );
		
		$catPostList = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'category__in' => array($catID) ) );
		?>
		<h1 class="momo_previewH1"><a href="" onClick="momo_preview('<?=$baseURL.'home'?>'); return false;"><? _e('Home'); ?></a></h1>
		<h3 class="momo_previewH2"><?=$catObj->name?></h3>
		<?php 
		if ( count($subCats) > 0 ) {
		?>
		<h3 class="momo_previewH3"><? _e('Subcategories');?></h3>
		<ul id="previewSubCats" class="momo_previewNav">

		<?php
		foreach ( $subCats as $subCatObj ) {
		?>
			<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'cat-'.$subCatObj->term_id?>'); return false;" ><?=$subCatObj->name?></a></li>
		<?php
		} // end foreach ( $subCats as $subCatObj ) {
		?>
		
		<?php
		} // end if ( count($subCats) > 0 )
		?>
		</ul>
		<?php
		if ( $catPostList->have_posts() ) { 
		?>
		<h3 class="momo_previewH3"><? _e('Posts');?></h3>
		<ul id="previewCatPosts" class="momo_previewNav">
		<?php
		while ( $catPostList->have_posts() ) { $catPostList->the_post();
			?>
			<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'post-'.get_the_id()?>'); return false;" ><? the_title() ?></a></li>
			<?php
			
		} // end while 
		?>
		</ul>
		<?php
		
		} // end if ( $catPostList->have_posts() ) {
		return;
	}
	
	// display pages
	if ( strpos( $page, 'page-' ) === 0 ) {
		$pageID = str_replace( 'page-', '', $page );
		$pageObj = get_page($pageID);

		$pageArgs = array(
		  'post_type' => 'page',
		  'nopaging' => true,
		  'post_status' => 'publish',
		  'post__in' => array($pageID),
		  );
		$pageQuery = new WP_Query($pageArgs);
		if ( $pageQuery->have_posts() ) {
			$pageQuery->the_post();
		} else {
			echo('UNABLE TO FIND PAGE.');
			return;
		}
		
		$subPageArgs = array(
		  'orderby' => 'menu_order',
		  'order' => 'ASC',
		  'post_type' => 'page',
		  'post_parent' => $pageID,
		  'nopaging' => true,
		  'post_status' => 'publish',
		  );
		$subPages = new WP_Query($subPageArgs);
		?>
		<h1 class="momo_previewH1"><a href="" onClick="momo_preview('<?=$baseURL.'home'?>'); return false;"><? _e('Home'); ?></a></h1>
		<h3 class="momo_previewH2"><? the_title(); ?></h3>
		<?php 
		if ($subPages->have_posts()) { 
		?>
		<h3 class="momo_previewH3"><? _e('Sub-Pages');?></h3>
		<ul id="previewSubPages" class="momo_previewNav">
		<?php
		while ($subPages->have_posts()) { $subPages->the_post();
		?>
			<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'page-'.get_the_id()?>'); return false;" ><?php the_title();?></a></li>
		<?php
		} // end while 
		?>
		</ul>

		<?php
		} // end if
		
		$pageQuery->rewind_posts();
		$pageQuery->the_post();
		?>
		<h3 class="momo_previewH3"><? _e('Page Content');?></h3>
		<? the_excerpt(); ?>
		<?php
		return;
	}	

	// display posts
	if ( strpos( $page, 'post-' ) === 0 ) {
		$postID = str_replace( 'post-', '', $page );
		$postObj = get_page($postID);
		$postArgs = array(
		  'post_type' => 'post',
		  'nopaging' => true,
		  'post_status' => 'publish',
		  'post__in' => array($postID),
		  );
		$postQuery = new WP_Query($postArgs);
		if ($postQuery->have_posts()){
			$postQuery->the_post();
		} else {
			echo('UNABLE TO FIND POST.');
			return;
		}
		?>
		<h1 class="momo_previewH1"><a href="" onClick="momo_preview('<?=$baseURL.'home'?>'); return false;"><? _e('Home'); ?></a></h1>
		<h3 class="momo_previewH2"><? the_title(); ?></h3>
		<br />
		<?php
		the_excerpt();
		
		$postCats = get_the_category();
		if (count($postCats) > 0 ) {
		?>
		<br />
		<h3 class="momo_previewH3"><? _e('Related Categories'); ?></h3>
		<ul id="momo_previewPostCats" class="momo_previewNav">
		<?php
			foreach ( $postCats as $catObj ) {
			?>
				<li class="arrow"><a href="" onClick="momo_preview('<?=$baseURL.'cat-'.$catObj->term_id?>'); return false;" ><?=$catObj->name?></a></li>
			<?php
			}
		?>
		</ul>
		<?php
		} // end if
		return;
	}	

}

?>