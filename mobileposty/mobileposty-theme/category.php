<?php
/*
*	Standard category template.  
*/

get_header();
$catID = get_cat_ID( single_cat_title( '', false ) );
$catObj = get_category( $catID );

// now show the div with the category contents
$divName = 'cat-'.$catID;
?>
	<div id="<?php echo $divName;?>">
		<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>

		<div class="body-wrap">
			<div id="content">
				<h1><?=$catObj->name?></h1>
			</div>
		</div>
		<ul id="cat-subcats" class="menu edgetoedge metal">
			<?php include(get_theme_root().'/'.get_template().'/includes/cats-subnav.php'); ?>
		</ul>
		<div class="body-wrap">
			<div id="content">
				<h1>Posts</h1>
			</div>
		</div>
		<ul id="cat-posts" class="menu edgetoedge metal">
			<?php include(get_theme_root().'/'.get_template().'/includes/cats-postlist.php'); ?>
		</ul>

		<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>
	</div>
<?php

get_footer();
?>