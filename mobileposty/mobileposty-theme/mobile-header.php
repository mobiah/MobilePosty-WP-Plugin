<?php global $momo_headerImage, $momo_homePageID; ?>
<?php 
$link = get_bloginfo('url');
?>
<div id="header">
<div onclick="window.location.href = '<?=$link?>';" id="headerLinkHolder"><a title="<? bloginfo('name '); ?> Home" href="<?=$link?>">
<?php
if ( strlen(trim($momo_headerImage)) > 0  ) {
	?>
<img alt="<? bloginfo('name '); ?> Home" src="<?=$momo_headerImage?>"/>
	<?php
} else {
	echo('<h1>'.get_bloginfo('name')."</h1>\n");
}
?>
</a></div>
</div>