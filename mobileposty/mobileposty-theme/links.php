<?php
/*
Template Name: linksTemplate
*/


get_header();

?>
	<div id="Links">
		<?php include(get_theme_root().'/'.get_template().'/mobile-header.php'); ?>
		<div class="body-wrap">
			<div id="content">
				<h2>Links:</h2>
			</div>
		</div>
		<ul id="linkList">
		<?php get_links_list(); ?>
		</ul>
		<?php include(get_theme_root().'/'.get_template().'/mobile-footer.php'); ?>
	</div>
<?php get_footer(); ?>
