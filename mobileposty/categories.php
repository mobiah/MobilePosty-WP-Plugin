<?php
/*	
*	-----------------------------------------------------------
*	CATEGORY FUNCTIONS
*	-----------------------------------------------------------
*/

/*
*	creates an array of ALL the category IDs in the system, and who their parent id is.
*	useful for testing visibility within the heirarchy (both in PHP and javascript)
*/
function momo_makeCatParents() {
	$catArray = get_categories('type=post');
	$parentArray = array();
	if ( is_array($catArray) ) {
		foreach( $catArray as $catObject ) {
			$parentArray[$catObject->term_id] = $catObject->parent ;
		}
	}
	return $parentArray;
}


/*
*	Tells whether a given category is set to be visible (regardless of ancestors being visible)
*/
function momo_catSetVisible($catID) {
	global $momo_visibleCats;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $catID , $momo_visibleCats ) && !$momo_visibleCats[$catID] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given category is visible (obeying heirarchy visibility: are all parents visible?)
*/
function momo_catVisible($catID) {

	// if the category is not set excluded/invisible, then check the ancestors
	if ( !momo_catSetVisible($catID) ) {
		return false;
	} 
	$invisAncestor = momo_getInvisCatAncestor($catID);
	return is_null( $invisAncestor ); // if there is no invisible ancestor, this category is visible
}

/*	
*	Returns an array of the IDs of ancestors of a given category
*/
function momo_getCatAncestors($catID) {
	$ancestorArray = array();
	$catObject = &get_category($catID);
	while ( !is_wp_error( $catObject ) && $catObject->parent && $catObject->parent != 0 ){
		$ancestorArray[] = $catObject->parent;
		$catObject = &get_category($catObject->parent);
	}
	return $ancestorArray;
}

/*	
*	Finds an invisible (excluded) ancestor of a given category ID
*	or if the category passed as an argument itself is invisible, the function returns $catID
*/
function momo_getInvisCatAncestor($catID) {
	// check if this category itself is visible
	if ( !momo_catSetVisible($catID) )  {
		return $catID;
	}
	// then check the anscestors
	$invisAncestor = NULL;
	$ancestorArray = momo_getCatAncestors($catID);
	// loop through all the ancestors, and see if any ancestor is marked as excluded/invisible
	while ( (list($key,$ancestorID) = each($ancestorArray)) && is_null($invisAncestor) ) {
		if ( !momo_catSetVisible( $ancestorID ) ) {
			$invisAncestor = $ancestorID;
		}
	}
	return $invisAncestor;
}

/*	
*	Returns an array of the IDs of descendants of a given category (id)
*/
function momo_getCatDescendants($catID) {
	$descendantArray = array();
	$childArray = get_categories( array( 'child_of' => $catID, 'hide_empty' => false ) );
	// get_categories graciously returns all DESCENDANTS, not just children, so no recursion
	if ( count($childArray) > 0 ) {
		foreach ( $childArray as $childCat ) {
			$descendantArray[] = $childCat->term_id;
		}
	}
	return $descendantArray;
}

/*
*	given a category array, re-orders the categories  in a new array
*	so that they come in tree order, with children coming right behind parents
*	
*/
function momo_catsOrderByParent( $catArray, $parent = '0', $depth = 0 ) {
	if ( !is_array($catArray) ) {
		return array(); // we should never get here...
	}
	$newArray = array();
	foreach ( $catArray as $catObj ) {
		if ( $catObj->category_parent == $parent ) {
			$catObj->depth = $depth;
			$newArray[] = $catObj;
			// get any children first, before moving on to other cats with the current parent
			$newArray = array_merge( $newArray, momo_catsOrderByParent( $catArray, $catObj->term_id, $depth+1) );
		}
	}
	return $newArray;
}
?>