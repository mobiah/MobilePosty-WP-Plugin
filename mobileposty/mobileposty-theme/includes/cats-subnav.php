<?php
// loop through all categories containing this post, and display a link to them
$subCats = get_categories( array( 'child_of' => $catID, 'hide_empty' => false, 'title_li' => ''  ) );
foreach ( $subCats as $catObj ) {
	$link = get_bloginfo('url').'/?cat='.$catObj->term_id;
?>
                <li class="arrow"><a class="slide" href="<?=$link?>"><?=$catObj->name?></a></li>
<?php 
} // end foreach
?>
