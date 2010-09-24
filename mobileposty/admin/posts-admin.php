<?php
/*
*	Admin-area filters for posts.  As of yet, this just adds a box to the new/edit post screen, for setting visibility.
*/ 

function momo_postsAdminInit() {
	// add the meta box for the post edit view
	add_meta_box('momo_post_box', __('Mobile Settings'), 'momo_post_metabox', 'post', 'side', 'low');
}
add_action( 'admin_init', 'momo_postsAdminInit' );


// Show the form fields which allow this page to be marked as visible or invisible
// on the mobile version of the site.
function momo_post_metabox() {
	global $post_ID;

	// see if there are any invisible ancestors
	$invisCatObj = NULL;
	$invisCatID = momo_getInvisPostCat($post_ID);
	if ( !is_null($invisCatID) ) {
		$invisCatObj = &get_category( $invisCatID );
	}

	$postVisible = momo_postSetVisible($post_ID);
	$postAuthVisible = momo_postAuthVisible($post_ID);
	$postCommVisible = momo_postCommVisible($post_ID);
	$postImgVisible = momo_postImgVisible($post_ID);

	?>
	<div id="momo_visibility">
		<table class="momo_visTable" cellpadding="0">
		<tr>
			<th><? _e('Post Element'); ?></th>
			<th><? _e('Visible?'); ?></th>
		</tr>
		<tr>
			<td><? _e('Post'); ?></td>
			<td><input type="checkbox" name="momo_postVisible" id="momo_postVisible" <?=( $postVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr class="even">
			<td><? _e('Author/Date'); ?></td>
			<td><input type="checkbox" name="momo_authVisible" id="momo_authVisible" <?=( $postAuthVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr>
			<td><? _e('Comments'); ?></td>
			<td><input type="checkbox" name="momo_commVisible" id="momo_commVisible" <?=( $postCommVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr class="even">
			<td><? _e('Images'); ?></td>
			<td><input type="checkbox" name="momo_imgVisible" id="momo_imgVisible" <?=( $postImgVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		</table>
		<input type="hidden" name="momo_checkVisibility" value="<?=$post_ID?>" />
		<div class="cat_invis_alert" id="cat_invis_alert" style="<?=( is_null($invisCatID) ? 'display:none;' : '' )?>" ><strong><? _e('Warning: At least one category containing this post is not visible in the mobile site, so this post will also be invisible. '); ?></strong><br />
			<a id="cat_edit_link" href="edit-tags.php?action=edit&taxonomy=category&tag_ID=<?=$invisCatID?>"
				title="<? _e('Edit the category'); ?>"><? _e('Edit the category'); ?></a>
		</div>
	</div><!-- #momo_visibility -->
	<script language="Javascript">
		jQuery(document).ready( function(){ 
			jQuery('#categorychecklist input[type=checkbox]').click( momo_testPostCats );
		} );
	</script>
<?php
}
//add_action('submitpage_box', 'momo_page_metabox');


?>