<?php
/*
*	Standard page template.  
*/

// this case is needed for when users have their "front page" set to some page or another in their "settings"->"Reading" settings 
// 
if ( is_front_page() ) {  
	get_header();
	require( get_theme_root().'/'.get_template().'/mobile-home.php' );
	get_footer();
	return;
}

// if we're here, we're actually loading some 
get_header();
if ( have_posts() ) { 
	the_post(); 
}

$pageID = get_the_ID();
$divName = 'page-' . $pageID;
?>
	<div id="<?php echo $divName;?>">
<?php
$pageTemplate = momo_getPageTemplate($pageID);
$fullPath = get_theme_root().'/'.get_template().'/'.$pageTemplate;
if ( $pageTemplate != '' && is_readable( $fullPath ) ) { 
	// if they've selected a page template for this page, then show it.
	include( $fullPath );
} elseif ( $pageID == get_option('page_for_posts') ) { 
	// if this is their "blog" page (in the reading settings), show a list of posts.
?>
	
	<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>

	<div class="body-wrap">
		<div id="content">
			
				<?php $my_query = new WP_Query('show_posts=5'); ?>

				<?php while ($my_query->have_posts()) : $my_query->the_post(); ?>

				<h1><?php the_title(); ?></h1>
				<?php the_date(); ?>
				<?php the_content(); ?>

				<?php endwhile; ?>
		</div>
	</div>
	<ul id="page-subnav-menu" class="menu edgetoedge metal">
		<?php include(get_theme_root().'/'.get_template().'/includes/page-subnav.php'); ?>
	</ul>

	<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>

<?php
} else {
	// if we're not showing a page template, and this isn't the chosen "blog" page, show
	// the standard page content with subnavigation
?>

		<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>

		<div class="body-wrap">
			<div id="content">
				<h1><?php the_title();?></h1>
				<?php the_content(); ?>
			</div>
		</div>
		<ul id="page-subnav-menu" class="menu edgetoedge metal">
			<?php include(get_theme_root().'/'.get_template().'/includes/page-subnav.php'); ?>
		</ul>

		<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>
<?php 
} // end if( $pageTemplate != '' && is_readable( $fullPath ) ) 
?>
	</div>
<?php
	
get_footer();
?>