<?php
/*
*	Standard single post template.  
*/

get_header();
if ( have_posts() ) { the_post(); }

// regardless of individually loading the post or not, we need to show a div with the post content.
	$postID = get_the_ID();
	$divName = 'post-' . $postID;
	?>
		<div id="<?php echo $divName;?>">
			<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>

			<div class="body-wrap">
				<div id="content">
					<h1><?php the_title();?></h1>
					<?php the_content(); ?>
				</div>
			</div>
			<ul id="post-cat-menu" class="menu edgetoedge metal">
				<?php include(get_theme_root().'/'.get_template().'/includes/post-subnav.php'); ?>
			</ul>

			<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>
		</div>
	<?php

get_footer();	
?>
