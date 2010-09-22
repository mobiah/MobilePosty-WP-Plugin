<?php
/*
*	Default Homepage for the mobile version of the site
*	Can be overridden in the mobile theme settings.
*/

// get the top-level pages and the top-level Categories
// then display them in a menu in the home div, for top-level navigation
$pageArgs = array(
  'orderby' => 'menu_order',
  'order' => 'ASC',
  'post_type' => 'page',
  'post_parent' => 0,
  'nopaging' => true,
  );
query_posts($pageArgs);

$catArgs = array(
	'child_of' => 0,
	'depth' => 1,
);
$topLevelCats = get_categories( $catArgs );
?>

<div id="home" class="subhead">
	<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>

	<ul id="home-menu" class="menu metal edgetoedge">
		<?php 
		if (have_posts()) : while (have_posts()) : the_post();
			$link = get_bloginfo('url').'/?page_id='.get_the_id();
		?> 
			<li class="arrow"><a class="slide" href="<?php echo $link;?>"><?php the_title();?></a></li>
		<?php 
		endwhile;	
		endif;
		?>
	</ul>
	<? if ( count($topLevelCats) > 0 ) { ?>
	<div class="body-wrap">
		<div id="content">
			<h1>Categories</h1>
		</div>
	</div>
	<? } // endif ( count($topLevelCats) > 0 ) ?>
	<ul class="menu metal edgetoedge">
		<?
		foreach ( $topLevelCats as $catObj ) {
			if ( $catObj->category_parent == '0' ) {
				$link = get_bloginfo('url').'/?cat='.$catObj->term_id;
		?>
			<li class="arrow"><a class="slide" href="<?php echo $link;?>"><?=$catObj->name?></a></li>
		<?
			} // end if
		} // end foreach
		?>
	</ul>
	<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>
</div>
