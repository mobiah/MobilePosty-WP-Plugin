<?php
/*
*	This file contains all the filters for pages/categories/ and posts
*	to limit what appears on the mobile site.
*/

/*
*	Add the filters only if the mobile site is enabled, and never in the admin interface
*/
function momo_filterInit() {
	global $momo_isMobile;
	if ( !is_admin() && ( $momo_isMobile ) ) {
		
		// filters to hide categories
		add_filter('list_terms_exclusions','momo_hideCats');
		
		// filters to hide posts and pages
		add_action('pre_get_posts', 'momo_hidePosts');
		add_filter('wp_list_pages_excludes', 'momo_hidePages');
		
		// filters to hide author/date info
		add_filter('the_author', 'momo_hideAuthor');
		add_filter('author_link', 'momo_hideAuthor');
		
		// filters to hide comments
		add_filter('comments_template','momo_hideCommentTemplate');
		
		// filters to change post content based on
		add_filter('the_content', 'momo_contentImages');
		
		// output the chosen style elements to the page
		add_action('wp_head', 'momo_styleOutput');
		
		// if we're on a page, and this page has a special page template chosen we need to hijack
		// wordpress's usual template inclusion system
		add_action('template_redirect', 'momo_usePageTemplate');
	}
}
add_action('init', 'momo_filterInit');


/*
*	edits query vars in any query for posts
*	
*/
function momo_hidePosts( $wp_query ) {
	global $momo_visibleCats, $momo_visiblePages, $momo_visiblePosts;

	// FIRST, lets exclude invisible categories.
	// if there are already some category__not_in variables, keep them!
	// currently, the newCatExcl variable doesn't get used because the use of momo_postVisible
	// already takes into account category visibility.
	if ( array_key_exists( 'cat', $wp_query->query_vars ) 
		&& $wp_query->query_vars['cat'] != '' ) {
		$newCatExcl = explode( ',', $wp_query->query_vars['cat'] );
	} else {
		$newCatExcl = array();
	}
	foreach( $momo_visibleCats as $catID => $catVisible ) {
		if ( !momo_catVisible($catID) ) {
			$newCatExcl[] = '-'.$catID;
		}
	}

	// now lets add the specifically excluded posts 
	// keeping any (unlikely)  variables already in query_vars['post__not_in']
	if ( array_key_exists( 'post__not_in', $wp_query->query_vars ) 
		&& is_array($wp_query->query_vars['post__not_in']) ) {
		$newPostExcl = $wp_query->query_vars['post__not_in'];
	} else {
		$newPostExcl = array();
	}

	// fill the array with invisible posts (but only one entry per post ID)
	foreach( $momo_visiblePosts as $postID => $postVisible ) {
		if ( !momo_postVisible($postID) && ! in_array( $postID, $newPostExcl ) ) {
			$newPostExcl[] = $postID;
		}
	}

	$newPageExcl = array();
	// and, while we're here, add any excluded pages (which are, of course, posts)
	// but, most importantly, add descendants of each page, too.
	foreach( $momo_visiblePages as $pageID => $pageVisible ) {
		if ( !momo_pageVisible($pageID) ) {
			if ( !in_array( $pageID, $newPageExcl ) ) {
				$newPageExcl[] = $pageID;
			}
			$descendants = momo_getPageDescendants($pageID);
			foreach ( $descendants as $descendantID ) {
				if ( !in_array( $descendantID, $newPageExcl ) ) {
					$newPageExcl[] = $descendantID;
				}
			}
		}
	}

	// now merge the posts array, and the pages array, and set the wp_query value so it 
	// knows to exclude all pages/posts in the merged array
	$wp_query->query_vars['post__not_in'] = array_merge( $newPostExcl, $newPageExcl );		
}

/*
*	excludes pages from wp_list_pages 
*/
function momo_hidePages ( $pageExcl ) {
	global $momo_visiblePages;

	if ( !is_array($pageExcl) ) {
		$pageExcl = array();
	}
	
	// add each page into the excluded pages array
	foreach( $momo_visiblePages as $pageID => $pageVisible ) {
		if ( !momo_pageVisible($pageID) ) {
			// it's not visible
			if ( !in_array( $pageID, $pageExcl ) ) {
				// it's not in the array yet, so add it.
				$pageExcl[] = $pageID;
			}
			// now we have to get all the descendands, and do the same checks
			$descendants = momo_getPageDescendants($pageID);
			foreach ( $descendants as $descendantID ) {
				if ( !in_array( $descendantID, $pageExcl ) ) {
					$pageExcl[] = $descendantID;
				}
			}
		}
	}
	return $pageExcl;
}

/*
*	Filters out the invisible/excluded categores from any call to get_categories
*	this directly adds SQL  statements to the current $exclusions ,
*	one per excluded category ID
*/
function momo_hideCats( $exclusions ) {
	global $momo_visibleCats;

	$newExclusions = '';
	foreach ( $momo_visibleCats as $catID => $catVisible ) {
		// if the category isn't visible, add some SQL to the exclusions which
		// makes it excluded
		if ( !momo_catVisible($catID) ) {
			if ( empty($newExclusions) ) {
				$newExclusions = ' ( t.term_id <> ' . intval($catID) . ' ';
			} else {
				$newExclusions .= ' AND t.term_id <> ' . intval($catID) . ' ';
			}
		}
	}
	if ( !empty($newExclusions) ) {
		$newExclusions.=' ) ';

		$exclusions.= ' AND '.$newExclusions;
	}
	return $exclusions;
}

/*
*	disable author template tags if the current post or page's author info isn't visible
*	
*/
function momo_hideAuthor( $currentAuth ) {
	if ( !momo_postAuthVisible(get_the_id()) || !momo_pageAuthVisible(get_the_id()) ) {
		return '';
	} else {
		return $currentAuth;
	}
}

/*
*	disable author template tags if the current post or page's author info isn't visible
*	
*/
function momo_hideCommentTemplate( $currentComm ) {
	if ( !momo_postCommVisible(get_the_id()) || !momo_pageCommVisible(get_the_id()) ) {
		return '';
	} else {
		return $currentComm;
	}
}


/*
*	useful for removing and resizing images out of the content in post
*
*/

function momo_contentImages ( $theContent ) {
	global $momo_visiblePosts, $momo_visiblePages, $momo_useThumbs, $momo_imgWidth, $momo_imgHeight;
	$postID = get_the_id();
	
	if ( is_page() ) {
		$imgVisible = momo_pageImgVisible($postID);
	} else {
		$imgVisible = momo_postImgVisible($postID);
	}
	
	// should we remove all images? 
	if ( !$imgVisible ) {
		return preg_replace( '/<img .*?>/is', '', $theContent );
	}

	// should we resize images?
	// that is, should we change the URL of the image to use timthumb.php to resize it?
	// ( at least the width or height needs to have a value greater than 0, otherwise it's meaningless to resize)
	if ( $momo_useThumbs && ( $momo_imgWidth > 0 || $momo_imgHeight > 0 ) ) {
		// Create a DOM object
		$contentHtml = str_get_html($theContent);
		//$contentHtml->load($theContent);
		
		// loop through all nodes that match 'img', and change the src of the image
		foreach( $contentHtml->find('img') as $imgElmt ) {
			$oldSrc = $imgElmt->src;

			// create a timthumbURL
			// w = width h = height zc = 0(zoom) 1(crop) src= path of image 
			$timthumbURL = MOMO_URL.'/thumbs/timthumb.php?w='.$momo_imgWidth.'&h='.$momo_imgHeight.'&zc=0&src=';
			// if this image is not hosted on this server, then we cannot resize with timthumb.php.
			// how to tell if it's on this WP installation's server:
			//   if there's no http://, then it must be on this server (a relative link)
			//   if the blog url is in the src, then it must be on this server.
			if ( strpos( $oldSrc, 'http://' ) === false || strpos( $oldSrc, get_bloginfo('url') ) !== false ) {
				$imgElmt->src = $timthumbURL.str_replace( get_bloginfo('url'), '', $oldSrc );
				$imgElmt->width = '';
				$imgElmt->height = '';
				$imgElmt->border = 0;
			}
		}
		
		return $contentHtml->save();
	}
	
	// we didn't do anything to theContent, just return it.
	return $theContent;
}

/*
*	Add some style elements to the header of the mobile pages, 
*	according to what options the user has chosen.
*/
function momo_styleOutput() {
	global $momo_fontFamily;

	if ( strlen(trim($momo_fontFamily)) > 0 ) {
?>
	<style type="text/css">
	body {
		font-family: <?=$momo_fontFamily?>;
	}
	</style>
<?php
	}
} // end function momo_styleOutput

/*
*	this function checks what page we're loading, and tries to load any special page template
*	the user has chosen for this page ( in the mobileposty content settings page )
*/
function momo_usePageTemplate() {
	global $wp_query;
	
	// are we loading a page? and everything looks good in $wp_query?
	if ( is_page() && array_key_exists( 'page_id', $wp_query->query_vars ) && is_numeric($wp_query->query_vars['page_id']) ) {
		$pageTemplate = momo_getPageTemplate($wp_query->query_vars['page_id']);
		$fullPath = get_theme_root().'/'.get_template().'/'.$pageTemplate;
		// in that case, does this page have a special template chosen?
		// and does that file actually exist and is it readable?
		if ( $pageTemplate != '' && is_readable( $fullPath ) ) {
			// ONLY after all these conditions are true, should we include that template file.
			include( $fullPath );
			exit;
		}
	}
}

?>