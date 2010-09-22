<?php
/*	
*	-----------------------------------------------------------
*	POST FUNCTIONS
*	-----------------------------------------------------------
*/

/*
*	makes an array of all posts, with the post ID as the array key, and an array of which categories
*	the post is in as the value
*/
function momo_makePostCats() {
	$postArray = get_posts( array( 'post_type' => 'post', 'numberposts' => -1 ) );
	$postCatArray = array();
	
	if ( is_array($postArray) ) {
		foreach ( $postArray as $postObj ) {
			$postCatArray[$postObj->ID] = momo_getPostCats($postObj->ID,true); // get ancestors!
		}
	}
	
	return $postCatArray;
}


/*
*	Tells whether a given post is set to be visible (regardless of categories being visible)
*/
function momo_postSetVisible($postID) {
	global $momo_visiblePosts;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $postID , $momo_visiblePosts ) 
			&& is_array($momo_visiblePosts[$postID])
			&& array_key_exists( 'post' , $momo_visiblePosts[$postID] ) 
			&& !$momo_visiblePosts[$postID]['post'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given post's Author is set to be visible
*/
function momo_postAuthVisible($postID) {
	global $momo_visiblePosts;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $postID , $momo_visiblePosts ) 
			&& is_array($momo_visiblePosts[$postID])
			&& array_key_exists( 'auth' , $momo_visiblePosts[$postID] ) 
			&& !$momo_visiblePosts[$postID]['auth'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given post's comments are set to be visible
*/
function momo_postCommVisible($postID) {
	global $momo_visiblePosts;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $postID , $momo_visiblePosts ) 
			&& is_array($momo_visiblePosts[$postID])
			&& array_key_exists( 'comm' , $momo_visiblePosts[$postID] ) 
			&& !$momo_visiblePosts[$postID]['comm'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given post's images are set to be visible
*/
function momo_postImgVisible($postID) {
	global $momo_visiblePosts;
	// if it's not explicitly excluded/invisible, then it's visible
	if ( array_key_exists( $postID , $momo_visiblePosts ) 
			&& is_array($momo_visiblePosts[$postID])
			&& array_key_exists( 'img' , $momo_visiblePosts[$postID] ) 
			&& !$momo_visiblePosts[$postID]['img'] ) {
		return false;
	} else {
		return true;
	}
}

/*
*	Tells whether a given post is visible (obeying heirarchy visibility: are all parents visible?)
*/
function momo_postVisible($postID) {

	// if the category is not set excluded/invisible, then check the ancestors
	if ( !momo_postSetVisible($postID) ) {
		return false;
	} 
	$invisCat = momo_getInvisPostCat($postID);
	return is_null($invisCat); // if there is no invisible post category (including ancestors), this post is visible
}


/*	
*	Returns an array of the IDs of ALL categories  of a post (including cat ancestors if specified)
*/
function momo_getPostCats($postID, $getAncestors = false) {
	$catArray = array();
	
	$catObjArray = get_the_category($postID);

	if ( !empty($catObjArray) ){
		// if there are categories, loop through them and add them to the array
		foreach( $catObjArray as $curCatObj ) {
			$catArray[] = $curCatObj->term_id;
			if ($getAncestors) {
				$catAncestors = momo_getCatAncestors($curCatObj->term_id);
				foreach ( $catAncestors as $ancestorID ) {
					if ( !in_array($ancestorID, $catArray) ) {
						$catArray[] = $ancestorID;
					}
				}
			}
		}
	}

	return $catArray;
}

/*	
*	Finds an invisible (excluded) category which contains a given post ID
*/
function momo_getInvisPostCat($postID) {
	$invisCat = NULL;
	$catArray = momo_getPostCats($postID, true);
	// loop through all the categories, and see if any category or its ancestors are marked as excluded/invisible
	while ( (list($key,$catID) = each($catArray)) && is_null($invisCat) ) {
		if ( !momo_catSetVisible( $catID ) ){
			$invisCat = $catID;
		}
	}
	return $invisCat;
}

?>