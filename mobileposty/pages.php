<?php
/*	
*	-----------------------------------------------------------
*	PAGE FUNCTIONS
*	functions which help deal with page heirarchy, content visibility, and templates
*	-----------------------------------------------------------
*/

/*
*	creates an array of ALL the page IDs in the system, and who their parent id is.
*	each existing pageid is a key in the array, and the value is the parent page ID if any
*	useful for testing visibility within the heirarchy (both in PHP and javascript)
*/
function momo_makePageParents() {
//	$pageArray = get_pages( array( ) );
	$pageArray = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish,private', 'numberposts' => -1 ) );
	$parentArray = array();
	if ( is_array($pageArray) ) {
		foreach( $pageArray as $pageObject ) {
			// wordpress bug 10381 - this fixes the issue.  make sure the page's ancestors are in the WP cache
			if ( !isset($pageObject->ancestors) ) {
				_get_post_ancestors($pageObject);
				wp_cache_set($pageObject->ID, $pageObject, 'posts');
			}
			$parentArray[$pageObject->ID] = $pageObject->post_parent ;
		}
	}

	return $parentArray;
}

/*
*	Tells whether a given page is set to be visible (regardless of ancestors being visible)
*	looks in the momo_visiblePages array
*/
function momo_pageSetVisible($pageID) {
	global $momo_visiblePages;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $pageID , $momo_visiblePages ) 
			&& is_array($momo_visiblePages[$pageID])
			&& array_key_exists( 'post' , $momo_visiblePages[$pageID] ) 
			&& !$momo_visiblePages[$pageID]['post'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given page's Author/Date is set to be visible
*	looks in the momo_visiblePages array
*/
function momo_pageAuthVisible($pageID) {
	global $momo_visiblePages;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $pageID , $momo_visiblePages ) 
			&& is_array($momo_visiblePages[$pageID])
			&& array_key_exists( 'auth' , $momo_visiblePages[$pageID] ) 
			&& !$momo_visiblePages[$pageID]['auth'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given page's comments are set to be visible
*	looks in the momo_visiblePages array
*/
function momo_pageCommVisible($pageID) {
	global $momo_visiblePages;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $pageID , $momo_visiblePages ) 
			&& is_array($momo_visiblePages[$pageID])
			&& array_key_exists( 'comm' , $momo_visiblePages[$pageID] ) 
			&& !$momo_visiblePages[$pageID]['comm'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given page's images are set to be visible
*	looks in the momo_visiblePages array
*/
function momo_pageImgVisible($pageID) {
	global $momo_visiblePages;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $pageID , $momo_visiblePages ) 
			&& is_array($momo_visiblePages[$pageID])
			&& array_key_exists( 'img' , $momo_visiblePages[$pageID] ) 
			&& !$momo_visiblePages[$pageID]['img'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given page is visible (obeying heirarchy visibility: are all parents visible?)
*	looks in the momo_visiblePages array
*/
function momo_pageVisible($pageID) {

	// if the category is not set excluded/invisible, then check the ancestors
	if ( !momo_pageSetVisible($pageID) ) {
		return false;
	} 
	// try to find an invisible ancestor
	$invisAncestor = momo_getInvisPageAncestor($pageID);
	return is_null($invisAncestor); // if there is no invisible page ancestor, this page is visible
}


/*	
*	Returns an array of the IDs of ancestors of a given page, closest ancestors first
*/
function momo_getPageAncestors($pageID) {
	$ancestorArray = array();
	$pageObject = &get_page($pageID);

	if ( isset( $pageObject->ancestors ) && is_array( $pageObject->ancestors ) ) {
		  // sometimes you get an array of ancestors.  Convenient, but unreliable.
		foreach( $pageObject->ancestors as $ancestor ) {
			$ancestorArray[] =  $ancestor;
		}
	} else { // there was no ancestor in the object.  Where did it go? just use post_parent to find all the ancestors
		while ( $pageObject->post_parent && $pageObject->post_parent != 0 && !is_wp_error( $pageObject ) ){
			$ancestorArray[] = $pageObject->post_parent;
			$pageObject = &get_page($pageObject->post_parent);
		}
	}
	return $ancestorArray;
}

/*	
*	Finds an invisible (excluded) ancestor of a given page ID
*/
function momo_getInvisPageAncestor($pageID) {
	$invisAncestor = NULL;
	$ancestorArray = momo_getPageAncestors($pageID);
	// loop through all the ancestors, and see if any ancestor is marked as excluded/invisible
	while ( (list($key,$ancestorID) = each($ancestorArray)) && is_null($invisAncestor) ) {
		if ( !momo_pageSetVisible( $ancestorID ) ){
			$invisAncestor = $ancestorID;
		}
	}
	return $invisAncestor;
}

/*	
*	Returns an array of the IDs of descendants of a given category (id)
*	Unlike get_categories, get_posts only returns the DIRECT children of the given page,
*	so a recursive call is used.
*/
function momo_getPageDescendants($pageID) {
	global $momo_pageParents;

	$descendantArray = array();
	foreach ( $momo_pageParents as $childID => $parentID ) {
		if ( $parentID == intval($pageID) ) {
			$descendantArray[] = $childID;
			$descendantArray = array_merge( $descendantArray, momo_getPageDescendants($childID) );
		}
	}
	return $descendantArray;
}

/*
*	given a page array, re-orders the pages  in a new array
*	so that they come in tree order, with children coming right behind parents
*	
*/
function momo_pagesOrderByParent( $pageArray, $parent = '0', $depth = 0 ) {
	if ( !is_array($pageArray) ) {
		return array(); // why was this called?
	}
	$newArray = array();
	foreach ( $pageArray as $pageObj ) {
		if ( $pageObj->post_parent == $parent ) {
			$pageObj->depth = $depth;
			$newArray[] = $pageObj;
			// get any children first, before moving on to other pages with the current parent
			$newArray = array_merge( $newArray, momo_pagesOrderByParent( $pageArray, $pageObj->ID, $depth+1 ) );
		}
	}
	return $newArray;
}

/*
*	Gets a page's chosen template from options array, if any has been chosen
*/
function momo_getPageTemplate( $pageID ) {
	global $momo_visiblePages;

	if ( array_key_exists( $pageID , $momo_visiblePages ) 
			&& is_array($momo_visiblePages[$pageID])
			&& array_key_exists( 'template' , $momo_visiblePages[$pageID] ) ) {
		return $momo_visiblePages[$pageID]['template'];
	} else {
		return '';
	}
}

?>