<?php

/*
*	In this page, we allow the user to choose which content and what portions of that content are visible
*	in the mobile version of the site, and to get a preview of the content flow
*
*/


function momo_contentDisplay() {
	global $momo_options, $momo_envTest, $momo_visibleCats, $momo_visiblePages, $momo_visiblePosts;
		
//	$pagesAll = momo_pagesOrderByParent( get_pages( array( 'sort_column' => 'menu_order',  ) ) );
	$pagesAll = momo_pagesOrderByParent( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish,private', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => -1 ) ) );
	$postsAll = get_posts( array( 'post_type' => 'post', 'numberposts' => -1 ) );
	// get all the cats (reordered to come in the proper tree structure)
	$catsAll = momo_catsOrderByParent(get_categories( array('type' => 'post') ));
	
?>
<div class="wrap">
	<div class="icon32" id="icon-themes"><br/></div>
	<h2 style="clear:none;"><?_e("Customize Mobile Content Visibility");?></h2> 
	<div id="momo_contentContainer">
		<div id="momo_contentLeft">
			<? if ($momo_envTest == '' || $_GET['momo_doEnvTest']=='true') momo_envTester(); ?>
			<div class="momo_messageContainer" style="<?=($momo_envTest == 'failure' ? 'height:37px;' : '')?>">
				<div id="momo_contentLoading" style="display:none;"><img alt="" id="ajax-loading" src="images/wpspin_light.gif"/></div>
				<div id="momo_contentMessage" class="momo_message <?=($momo_envTest == 'failure' ? 'momo_error_message' : '')?>" style="<?=($momo_envTest == 'failure' ? '' : 'display:none;')?>" ><?=($momo_envTest == 'failure' ? __('Warning - your server config may prevent normal function of MobilePosty.').'(<a href="'.$_SERVER["REQUEST_URI"].'&momo_doEnvTest=true">'.__('Click to test again').'</a>)' : '&nbsp;')?></div>
			</div>
			<div id="momo_tabContainer">
				<ul id="momo_tabs">
					<li id="momo_pageTab" class="selected momo_tab">
						<?php _e('Pages'); ?>
					</li>
					<li id="momo_catTab" class="momo_tab">
						<?php _e('Categories'); ?>
					</li>
					<li id="momo_postTab" class="momo_tab">
						<?php _e('Posts'); ?>
					</li>
				</ul>
				<script type="text/javascript">
				// attach this function to each of the config page li tabs after the page loads
					jQuery(document).ready( function(){ 
						jQuery('#momo_tabs li').click( momo_configSwitchTab ); 
					} );
				</script>
				<div id="momo_divSwapContainer">
					<div id="momo_pagesTable">
						<table width="100%" class="widefat">
						<thead>
						<tr>
							<th colspan="10" class="momo_TableTitle"><?php _e('Page Display Settings'); ?>
							</th>
						</tr>
						<tr>
							<th><?php _e('Page'); ?></th>
							<th><?php _e('Visible'); ?>?</th>
							<th><?php _e('Author'); ?>/<br /><?php _e('Date'); ?></th>
							<th><?php _e('Comments'); ?></th>
							<th><?php _e('Images'); ?></th>
							<th><?php _e('Template'); ?></th>
						</tr>
						</thead>
						<?php
						$count = 0;
						foreach( $pagesAll as $pageObj) {
							$pageID = $pageObj->ID;
							// add indentation to indicate how deep in the tree a page is.
							$pageName = ( $pageObj->post_status == 'private' ? 'Private: ' : '').$pageObj->post_title ;
							$pageName = str_repeat( '&nbsp;-&nbsp;', $pageObj->depth ).momo_shortenText( $pageName );
						?>
						<tr id="page<?=$pageID?>Row" class="<?=(  false && $count%2==0 ? ' alt ' : '' )?> <?=( momo_pageVisible($pageID) ? '' : 'momo_invisible' )?>">
							<td class="name column-name">
								<?=$pageName?>
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="page<?=$pageID?>PageVisible" id="page<?=$pageID?>PageVisible" value="TRUE" 
									<?=( momo_pageSetVisible($pageID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="page<?=$pageID?>AuthVisible" id="page<?=$pageID?>AuthVisible" value="TRUE" 
									<?=( momo_pageAuthVisible($pageID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="page<?=$pageID?>CommVisible" id="page<?=$pageID?>CommVisible" value="TRUE" 
									<?=( momo_pageCommVisible($pageID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="page<?=$pageID?>ImgVisible" id="page<?=$pageID?>ImgVisible" value="TRUE" 
									<?=( momo_pageImgVisible($pageID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="">
								<select name="page<?=$pageID?>Template" id="page<?=$pageID?>Template" >
									<option value='' style="font-style: italic;">--None--</option>
									<? foreach( momo_getTemplates() as $templateName => $templateFile ) { ?>
									<option value='<?=$templateFile?>' <?=( $templateFile == momo_getPageTemplate($pageID) ? 'selected="selected"' : '' )?> ><?=$templateName?></option>
									<? }// end foreach ?>
							</td>
						</tr>
						<?
							$count++;
						} // end foreach
						?>
						</table>
					</div><!-- end momo_pagesTable -->

					<div id="momo_catsTable" style="display: none;">
						<table width="100%" class="widefat">
						<thead>
						<tr>
							<th colspan="10" class="momo_TableTitle"><?php _e('Category Display Settings'); ?>
							</th>
						</tr>
						<tr>
							<th><?php _e('Category'); ?></th>
							<th><?php _e('# Posts'); ?></th>
							<th><?php _e('Visible'); ?>?</th>
						</tr>
						</thead>
						<?php
						$count = 0;
						foreach( $catsAll as $catObj) {
							$catID = $catObj->term_id;
							// add indentation to indicate how deep in the tree a cat is.
							$catName = str_repeat('&nbsp;-&nbsp;', $catObj->depth).momo_shortenText( $catObj->name);
						?>
						<tr id="cat<?=$catID?>Row" class="<?=( false && $count%2==0 ? ' alt ' : '' )?> <?=( momo_catVisible($catID) ? '' : 'momo_invisible' )?>">
							<td class="name column-name">
								<?=$catName?>
							</td>
							<td class="postCount column-postCount">
								<?=$catObj->category_count?>
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="cat<?=$catID?>CatVisible" id="cat<?=$catID?>CatVisible" value="TRUE" 
									<?=( momo_catSetVisible($catID) ? 'checked="checked"' : '' )?> />
							</td>
						</tr>
						<?
							$count++;
						} // end foreach
						?>
						</table>
					</div><!-- end momo_catsTable -->

					<div id="momo_postsTable" style="display: none;">
						<table width="100%" class="widefat">
						<thead>
						<tr>
							<th colspan="10" class="momo_TableTitle"><?php _e('Post Display Settings'); ?>
							</th>
						</tr>
						<tr>
							<th><?php _e('Post'); ?></th>
							<th><?php _e('Categories'); ?></th>
							<th><?php _e('Visible'); ?>?</th>
							<th><?php _e('Author'); ?>/<br/><?php _e('Date'); ?></th>
							<th><?php _e('Comments'); ?></th>
							<th><?php _e('Images'); ?></th>
						</tr>
						</thead>
						<?php
						$count = 0;
						foreach( $postsAll as $postObj) {
							$postID = $postObj->ID;
							$catArray = get_the_category($postID);
						?>
						<tr id="post<?=$postID?>Row" class="<?=( false && $count%2==0 ? ' alt ' : '' )?> <?=( momo_postVisible($postID) ? '' : 'momo_invisible' )?>">
							<td class="name column-name">
								<?=momo_shortenText($postObj->post_title)?>
							</td>
							<td class="category column-category">
								<?
							$count = 1;
							foreach ( $catArray as $catObj ){
								$catID = $catObj->term_id;
								?>
								<span id="cat<?=$catID?>" class="catName <?=( momo_catVisible($catID) ? '' : 'momo_invisible' )?>">
									<?=$catObj->name?>
								</span><?=( $count == count($catArray) ? '' : ',' )?>
								<?
								$count++;
							}
								?>
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="post<?=$postID?>PostVisible" id="post<?=$postID?>PostVisible" value="TRUE" 
									<?=( momo_postSetVisible($postID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="post<?=$postID?>AuthVisible" id="post<?=$postID?>AuthVisible" value="TRUE" 
									<?=( momo_postAuthVisible($postID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="post<?=$postID?>CommVisible" id="post<?=$postID?>CommVisible" value="TRUE" 
									<?=( momo_postCommVisible($postID) ? 'checked="checked"' : '' )?> />
							</td>
							<td class="momo_checkbox">
								<input type="checkbox" name="post<?=$postID?>ImgVisible" id="post<?=$postID?>ImgVisible" value="TRUE" 
									<?=( momo_postImgVisible($postID) ? 'checked="checked"' : '' )?> />
							</td>
						</tr>
						<?
							$count++;
						} // end foreach
						?>
						</table>			
					</div><!-- end momo_postsTable -->
				</div><!-- end momo_divSwapContainer -->
			</div><!-- end momo_tabContainer -->
			<script type="text/javascript">
			// attach click handlers to  all "visible" checkboxes, 
			// which in turn go and update the CSS classes on the associated items
			jQuery(document).ready( function(){ 
				// attach the correct function to inputs whose ids end in a specified string.
				jQuery('div#momo_pagesTable input[id$=PageVisible]').click( momo_clickPageVisBox ); 
				jQuery('div#momo_catsTable input[id$=CatVisible]').click( momo_clickCatVisBox ); 
				jQuery('div#momo_postsTable input[id$=PostVisible]').click( momo_clickPostVisBox ); 

				// in the case that the user has refreshed the page, the visibility variables
				// may not match what is in the checkboxes.  Make them match
				momo_updateAllCats();
				momo_updateAllPosts();
				momo_updateAllPages();
				// then add the appropriate CSS classes to make everything look right.
				momo_markInvisCats();
				momo_markInvisPages()
				momo_markInvisPosts();
			} );
				
				
			</script>
		</div><!-- end momo_optionsLeft -->
		<div id="momo_contentRight">
			<div id="momo_actionButtons">
				
				<input type="submit" id="momo_reloadPreview" name="momo_reloadPreview" class="button-secondary momo_reloadPreview" value="Reload Preview"  />
				<input type="submit" id="momo_saveChanges" name="momo_saveChanges" class="button-primary momo_saveChanges" value="Save Changes"  />
			</div><!-- end momo_actionButtons -->
			<div id="momo_previewer">
				<div id="momo_previewLoading" style="display:none;"><img alt="" id="ajax-loading" src="images/wpspin_light.gif"/>...Loading...</div>
				<div id="momo_previewWindow">
				&nbsp;
				</div><!-- end momo_previewWindow -->
			</div><!-- end momo_previewer -->
			<div id="momo_actionButtons">
				<input type="submit" id="momo_reloadPreview" name="momo_reloadPreview" class="button-secondary momo_reloadPreview" value="Reload Preview"  />
				<input type="submit" id="momo_saveChanges" name="momo_saveChanges" class="button-primary momo_saveChanges" value="Save Changes"  />
			</div><!-- end momo_actionButtons -->
			<script type="text/javascript">
				// attach a function to the save changes buttons, such that it saves the entire visibility settings to php via AJAX
				// this function must be here (and not in a .js), so that it can get the proper URL for the POST, then pass it to the ajax requester
				jQuery(document).ready( function(){ 
					jQuery('.momo_saveChanges').click( function(){
						//just in case, let's sync the global variables and the checkboxes
						momo_updateAllCats();
						momo_updateAllPosts();
						momo_updateAllPages();
						// now, send the (maybe) updated data off.
						momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveVisibility', 
									{	"momo_visibleCats" :  jQuery.toJSON(momo_visibleCats), 
										"momo_visiblePages" : jQuery.toJSON(momo_visiblePages),
										"momo_visiblePosts" : jQuery.toJSON(momo_visiblePosts) },
									'momo_contentMessage',
									'momo_contentLoading'
									);
						momo_unsavedEdits = false;
					} );
					// set the function which confirms when the user leaves the page without saving
					window.onbeforeunload = momo_contentOnUnload;
					// when the reload button is clicked, go to the home
					jQuery('.momo_reloadPreview').click( function(){
						if ( momo_currentPreviewURL == null ) {
							momo_currentPreviewURL = '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_genPreview&momo_previewPage=home';
						}
						momo_preview(momo_currentPreviewURL);
					} ); 
					
					// on first load of the page, we should load the phone preview once.
					momo_preview('<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_genPreview&momo_previewPage=home');
				} );
			</script>
		</div><!-- end momo_optionsLeft -->
	</div><!-- end momo_optionsContainer -->
</div><!-- end wrap -->

<?php
} //end function momo_contentDisplay

?>