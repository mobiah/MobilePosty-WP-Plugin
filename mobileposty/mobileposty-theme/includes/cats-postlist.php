<?php
$catPostList = new WP_Query( array('post_type'=>'post', 'cat'=>$catID ) );
while( $catPostList->have_posts() ) {
	$catPostList->the_post();
	$link = get_bloginfo('url').'/?p='.get_the_id();
?>
			<li class="arrow"><a class="slide" href="<?=$link?>"><?php the_title(); ?></li>
<?php
} // end while
?>