<?php

// all the filters/actions/etc.. which only need to happen when loading an admin page
function momo_pagesAdminInit() {
	// add the meta box for the page edit view
	add_meta_box('momo_page_box', __('Mobile Settings'), 'momo_page_metabox', 'page', 'side', 'low');


}
add_action( 'admin_init', 'momo_pagesAdminInit' );


// Show the form fields which allow this page to be marked as visible or invisible
// on the mobile version of the site.
function momo_page_metabox() {
	global $post_ID;

	// see if there are any invisible ancestors
	$invisAncestorObj = NULL;
	$invisAncestorID = momo_getInvisPageAncestor($post_ID);
	if ( !is_null($invisAncestorID) ) {
		$invisAncestorObj = &get_post( $invisAncestorID );
	}
	
	$pageVisible = momo_pageSetVisible($post_ID);
	$pageAuthVisible = momo_pageAuthVisible($post_ID);
	$pageCommVisible = momo_pageCommVisible($post_ID);
	$pageImgVisible = momo_pageImgVisible($post_ID);

	?>
	<div id="momo_visibility">
		<table class="momo_visTable" cellpadding="0">
		<tr>
			<th><? _e('Page Element'); ?></th>
			<th><? _e('Visible?'); ?></th>
		</tr>
		<tr>
			<td><? _e('Page'); ?></td>
			<td><input type="checkbox" name="momo_postVisible" id="momo_postVisible" <?=( $pageVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr class="even">
			<td><? _e('Author/Date'); ?></td>
			<td><input type="checkbox" name="momo_authVisible" id="momo_authVisible" <?=( $pageAuthVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr>
			<td><? _e('Comments'); ?></td>
			<td><input type="checkbox" name="momo_commVisible" id="momo_commVisible" <?=( $pageCommVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		<tr class="even">
			<td><? _e('Images'); ?></td>
			<td><input type="checkbox" name="momo_imgVisible" id="momo_imgVisible" <?=( $pageImgVisible ?'checked="checked"' : '' )?> value="TRUE" /></td>
		</tr>	
		</table>
		<input type="hidden" name="momo_checkVisibility" value="<?=$post_ID?>" />
		<div class="ancestor_alert" id="ancestor_alert" style="<?=( is_null($invisAncestorID) ? 'display:none;' : '' )?>" ><strong><? _e('Warning: An ancestor of this page is not visible in the mobile site, so this page will also be invisible. '); ?></strong><br />
			<a id="ancestor_edit_link" href="page.php?action=edit&amp;post=<?=$invisAncestorID?>"
				title="<? _e('Edit the ancestor'); ?>"><? _e('Edit the ancestor'); ?></a>
		</div>
	</div><!-- #momo_visibility -->
	<script language="Javascript">
	// attach the test function to the parent pull-down
		jQuery(document).ready( function(){ 
			jQuery('#parent_id').change( momo_testPageParent );
		} );
	</script>
<?php
}
//add_action('submitpage_box', 'momo_page_metabox');


?>