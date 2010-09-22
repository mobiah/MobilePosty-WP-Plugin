<?php

// loop through all categories containing this post, and display a link to them
foreach ( get_the_category() as $catObj ) {

	$link = get_bloginfo('url').'/?cat='.$catObj->term_id;
?>
		<li class="arrow"><a class="slide" href="<?=$link?>"><?=$catObj->name?></a></li>
<?php 
} // end foreach
?>