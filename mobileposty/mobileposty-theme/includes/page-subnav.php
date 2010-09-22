<?php
$subPageQuery = new WP_Query('orderby=menu_order&order=ASC&post_parent=' . $pageID . '&post_type=page');
while ($subPageQuery->have_posts()) : $subPageQuery->the_post(); 
	$link = get_bloginfo('url').'/?page_id='.get_the_id();
?>
                <li class="arrow"><a class="slide" href="<?=$link?>"><?php the_title(); ?></a></li>
<?php 
endwhile;
?>