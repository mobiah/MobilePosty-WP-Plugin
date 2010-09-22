<?php
/*
*	just some debug stuff to go at the bottom of the page
*/
function debug_test() { 
	global $momo_visiblePages;
	pre($momo_visiblePages);
	
	$pageID = 4;
	$pageObject = &get_post( $pageID );
	$pageObject->newproperty = 10;
	//pre($pageObject);
	$catObj = get_category(3);
	//pre( $catObj );
	$catArray = get_categories( array('type' => 'post') );
	//pre( $catArray );
	//pre(momo_reorderCats( $catArray ));
	$postID =  214 ;
	$postObj = get_post($postID);
	//pre($postObj);
	//pre(get_the_category($postID));
	
}
//add_action('admin_footer','debug_test');
?>