<?php

/*
*	Let it be known: Here lies the main configuration page.
*
*	this function is used in admin/general-admin.php in the menu page declarations.
*/

function momo_configDisplay() {
	global	$momo_options, 
			$momo_envTest, 
			$momo_enabled, 
			$momo_useJQTouch, 
			$momo_useThumbs, 
			$momo_imgWidth, 
			$momo_imgHeight,
			$momo_showSwitcherLink,
			$momo_homePageID,
			$momo_fontFamily,
			$momo_headerImage,
			$momo_themeStyle,
			$momo_fonts,
			$momo_styles;
	
	
	$pagesAll = momo_pagesOrderByParent( get_pages( array( 'sort_column' => 'menu_order'  ) ) );
?>
<div class="wrap">
	<div class="icon32" id="icon-themes"><br/></div>
	<h2 style="clear:none;"><?_e("Mobile Site Settings");?></h2> 
	<div id="momo_optionsContainer">
			<? if ($momo_envTest == '' || $_GET['momo_doEnvTest']=='true') momo_envTester(); ?>
			<div class="momo_messageContainer">
				<div id="momo_contentLoading" style="display:none;"><img alt="" id="ajax-loading" src="images/wpspin_light.gif"/></div>
				<div id="momo_contentMessage" class="momo_message <?=($momo_envTest == 'failure' ? 'momo_error_message' : '')?>" <?=($momo_envTest == 'failure' ? '' : 'style="display:none;"')?> ><?=($momo_envTest == 'failure' ? __('Warning - your server configuration may prevent the normal function of MobilePosty.').'(<a href="'.$_SERVER["REQUEST_URI"].'&momo_doEnvTest=true">'.__('Click to test again').'</a>)' : '&nbsp;')?></div>
			</div>
			<div id="momo_tabContainer">
				<ul id="momo_tabs">
					<li id="momo_mainTab" class="selected momo_tab">
						<?php _e('Main'); ?>
					</li>
					<li id="momo_mobileThemeTab" class="momo_tab">
						<?php _e('Mobile Theme'); ?>
					</li>
				</ul>
				<script type="text/javascript">
				// attach this function to each of the config page li tabs after the page loads
					jQuery(document).ready( function(){ 
						jQuery('#momo_tabs li').click( momo_configSwitchTab ); 
					} );
				</script>
				<div id="momo_divSwapContainer">	
					<div id="momo_mainSettings">
						<table width="100%" class="widefat">
						<thead>
						<tr>
							<th colspan="10" class="momo_TableTitle"><?php _e('Main Mobile Settings'); ?>
							</th>
						</tr>
						</thead>
						<tr>
						<td>
						<div id="momo_enabledDiv">
							<label for="momo_enabled">
								<input type="checkbox" name="momo_enabled" id="momo_enabled" value="TRUE" <?=( $momo_enabled ? 'checked="checked"' : '' )?> />
								<?_e('Mobile Site Enabled?');?>
							</label>
							<script type="text/javascript">
								// attach a function to the checkbox, such that saves the setting to php via AJAX
								// this function must be here, so that it can get the proper URL for the POST, then pass it to the ajax requester
								jQuery(document).ready( function(){ 
									jQuery('#momo_enabled').click( function(){
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_enabled&momo_enabled='+this.checked.toString(),
													null,
													'momo_contentMessage',
													'momo_contentLoading');
									} ); 
								} );
							</script>
						</div><!-- end momo_enabledDiv -->
						<br />
						<div id="momo_thumbDiv">
							<label for="momo_useThumbs">
								<input type="checkbox" name="momo_useThumbs" id="momo_useThumbs" value="TRUE" <?=( $momo_useThumbs ? 'checked="checked"' : '' )?> />
								<?_e('Resize post/page images?');?>
							</label>
							<br />
							&nbsp;&nbsp;&nbsp;Maximum Image Width: <input type="text" name="momo_imgWidth" id="momo_imgWidth" value="<?=intval($momo_imgWidth)?>" size="3" /> pixels ( 0 for any width )
							<br />
							&nbsp;&nbsp;&nbsp;Maximum Image Height: <input type="text" name="momo_imgHeight" id="momo_imgHeight" value="<?=intval($momo_imgHeight)?>" size="3" /> pixels ( 0 for any height )
							<br />
							<input type="button" name="momo_saveUseThumbs" id="momo_saveUseThumbs" value="Save Resizing Settings" class="button-primary" />
							<script type="text/javascript">
								jQuery(document).ready( function(){ 
									jQuery('#momo_saveUseThumbs').click( function(){
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveUseThumbs',
													{	'momo_useThumbs': jQuery('#momo_useThumbs').get(0).checked.toString(),
														'momo_imgWidth' : jQuery('#momo_imgWidth').val(),
														'momo_imgHeight' : jQuery('#momo_imgHeight').val()
													},
													'momo_contentMessage',
													'momo_contentLoading');
									} ); 
								} );
							</script>
						</div><!-- end momo_thumbDiv -->
						<br />
						<div id="momo_switcherControl">
							Show the mobile/standard theme switcher link in the footer of the site?<br />
							<select id="momo_showSwitcherLink" name="momo_showSwitcherLink">
								<option value="always" <?=( $momo_showSwitcherLink == 'always' ? 'selected="selected"' : '' )?> >Always</option>
								<option value="mobile" <?=( $momo_showSwitcherLink == 'mobile' ? 'selected="selected"' : '' )?> >Only on the mobile theme</option>
								<option value="standard" <?=( $momo_showSwitcherLink == 'standard' ? 'selected="selected"' : '' )?> >Only on the standard theme</option>
								<option value="never" <?=( $momo_showSwitcherLink == 'never' ? 'selected="selected"' : '' )?> >Never</option>
							</select>
							<script type="text/javascript">
								// attach a function to the select dropdown, such that saves the setting to php via AJAX
								// this function must be here (and not in a .js file), 
								// so that it can get the proper URL for the POST, then pass it to the ajax requester
								jQuery(document).ready( function(){ 
									jQuery('#momo_showSwitcherLink').change( function(){
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveSwitcherLink&momo_showSwitcherLink='+this.value,
													null,
													'momo_contentMessage',
													'momo_contentLoading');
									} ); 
								} );
							</script>
						</div><!-- end momo_switcherControl -->
						</td>
						</tr>
						</table>
					</div><!-- end momo_mainSettings -->
					<div id="momo_mobileThemeSettings" style="display:none;">
						<div id="momo_mainSettings">
						<table width="100%" class="widefat">
						<thead>
						<tr>
							<th colspan="10" class="momo_TableTitle"><?php _e('Mobile Theme Settings'); ?>
							</th>
						</tr>
						</thead>
						<tr>
						<td>
							<div id="momo_optionsLeft" >
							<div id="momo_logoUploadButtons">
								<input type="hidden" name="momo_headerImageURL" id="momo_headerImageURL" value="<?=$momo_headerImage?>" />
								<? _e("Upload a Logo (optional)") ?>:<br />&nbsp;
								<input type="button" id="momo_chooseHeaderImage" name="momo_chooseHeaderImage" onClick="momo_getMediaImage('momo_headerImageURL','momo_headerImage');" value="<? _e("Choose an Image") ?>" class="button-secondary" />
								<input type="button" id="momo_noHeaderImage" name="momo_noHeaderImage" onClick="momo_removeHeaderImage('momo_headerImageURL','momo_headerImage','<?=MOMO_IMG_URL.'/blank.gif'?>');" value="<? _e("Remove Image") ?>" class="button-secondary" />
								<script type="text/javascript">
									jQuery(document).ready( function(){ 
										jQuery('#momo_saveHeaderImage').click( function(){
											momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveHeaderImage',
														{ 'momo_headerImage': jQuery('#momo_headerImageURL').val() },
														'momo_contentMessage',
														'momo_contentLoading');
										} ); 
									} );
								</script>
							</div>
							<br style="clear: both;" />
							<img id="momo_headerImage" src="<?=( $momo_headerImage != '' ? $momo_headerImage : MOMO_IMG_URL.'/blank.gif' )?>" />
							<br style="clear: both;" />&nbsp;
							<input type="button" id="momo_saveHeaderImage" value="Save Image Settings" class="button-primary" />
							<br />
							<br />
							<? _e("Mobile Site font") ?>: <br />&nbsp;
							<select id="momo_fontFamily" name="momo_fontFamily">
								<?php foreach($momo_fonts as $fontName => $fontStyle)  { ?>
								<option value="<?=$fontStyle?>" style="font-family:<?=$fontStyle?>;" <?=( $momo_fontFamily == $fontStyle ? 'selected="selected"' : '' );?> ><?=$fontName?></option>
								<?php } // end foreach ?>
							</select><br />&nbsp;
							<input type="button" name="momo_saveFont" id="momo_saveFont" value="<? _e("Save Font") ?>" class="button-primary" />
							<script type="text/javascript">
								jQuery(document).ready( function(){ 
									jQuery('#momo_saveFont').click( function(){
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveFont',
													{ 'momo_fontFamily': jQuery('#momo_fontFamily').val() },
													'momo_contentMessage',
													'momo_contentLoading');
									} ); 
								} );
							</script>
							<br />
							<br />
							<? _e("Mobile Site Home Page"); ?>: <br />&nbsp;
							<select id="momo_homePageID" name="momo_homePageID">
								<option value="0" style="font-style: italic;">-- <? _e("None (use index)"); ?> --</option>
								<?php 
								foreach($pagesAll as $pageObj )  { 
									$pageName = str_repeat( '&nbsp;-&nbsp;', $pageObj->depth ).momo_shortenText( $pageObj->post_title )
								?>
								<option value="<?=$pageObj->ID?>" <?=( $momo_homePageID == $pageObj->ID ? 'selected="selected"' : '' );?> ><?=$pageName?></option>
								<?php } // end foreach ?>
							</select><br />&nbsp;
							<input type="button" name="momo_saveHomePageID" id="momo_saveHomePageID" value="<? _e("Save Mobile Home Page"); ?>" class="button-primary" />
							<script type="text/javascript">
								jQuery(document).ready( function(){ 
									jQuery('#momo_saveHomePageID').click( function(){
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveHomePageID',
													{ 'momo_homePageID': jQuery('#momo_homePageID').val() },
													'momo_contentMessage',
													'momo_contentLoading');
									} ); 
								} );
							</script>
							<br />
							<br />
							<? _e('Choose a Style'); ?>:<br />
							<ul id="momo_styleList">
							<? foreach ( $momo_styles as $styleName => $styleID ) { ?>
								<li id="momo_<?=$styleID?>Item" >
									<label for="momo_<?=$styleID?>Theme">
									<img src="<?=MOMO_URL?>/mobileposty-theme/css/<?=$styleID?>.png" class="momo_stylePreviewImg" />
									<br />
									<?=$styleName?>
									</label>
									<input type="radio" id="momo_<?=$styleID?>Theme" name="momo_themeStyle" value="<?=$styleID?>" <?=( $momo_themeStyle == $styleID ? 'checked="checked"' : '' )?>/>
								</li>
							<? } // end foreach ( $momo_styles as $style ) ?>
							</ul>
							<br />&nbsp;
							<input type="submit" id="momo_saveStyle" name="momo_saveStyle" class="button-primary" value="Save Style"  />
							<script type="text/javascript">
								jQuery(document).ready( function(){ 
									jQuery('#momo_saveStyle').click( function() {
										momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveThemeStyle', 
													{ "momo_themeStyle" : jQuery("input[name=momo_themeStyle]:checked").val() },
													'momo_contentMessage',
													'momo_contentLoading'
													);
									} );
								} );
							</script>
							</div><!-- end momo_optionsLeft -->
							<div id="momo_optionsRight">
								<div id="momo_actionButtons">
									<input type="submit" id="momo_reloadPreview" name="momo_reloadPreview" class="button-secondary momo_reloadPreview" value="Preview"  />
									<input type="submit" id="momo_saveAllTheme" name="momo_saveAllTheme" class="button-primary momo_saveAllTheme" value="Save All Settings" />
								</div><!-- end momo_actionButtons -->
								<div id="momo_previewer">
									<div id="momo_previewLoading" style="display:none;"><img alt="" id="ajax-loading" src="images/wpspin_light.gif"/>...<? _e("Loading"); ?>...</div>
									<div id="momo_previewWindow">
									<iframe id="momo_previewIFrame" name="momo_previewIFrame"></iframe>
									</div><!-- end momo_previewWindow -->
								</div><!-- end momo_previewer -->
								<div id="momo_actionButtons">
									<input type="submit" id="momo_reloadPreview" name="momo_reloadPreview" class="button-secondary momo_reloadPreview" value="Preview"  />
									<input type="submit" id="momo_saveAllTheme" name="momo_saveAllTheme" class="button-primary momo_saveAllTheme" value="Save All Settings" />
								</div><!-- end momo_actionButtons -->
								<script type="text/javascript">
									// preview the site with the chosen settings before saving
									jQuery(document).ready( function(){ 
										jQuery('.momo_saveAllTheme').click( function() {
											momo_ajax( '<?=get_bloginfo('url')?>/wp-admin/?momo_ajax&momo_callFunction=momo_saveAllTheme', 
														{ 	'momo_themeStyle' : jQuery('input[name=momo_themeStyle]:checked').val(),
															'momo_homePageID': jQuery('#momo_homePageID').val(),
															'momo_fontFamily': jQuery('#momo_fontFamily').val(),
															'momo_headerImage': jQuery('#momo_headerImageURL').val()
														},
														'momo_contentMessage',
														'momo_contentLoading'
														);
										} );
										// set the functions which reloads the preview of the site
										jQuery('.momo_reloadPreview').click( function() {
											// set up the querystring
											var qs = 'momo_previewing=true&';
											qs += 'momo_headerImage='+escape(jQuery('#momo_headerImageURL').val())+'&';
											qs += 'momo_fontFamily='+escape(jQuery('#momo_fontFamily').val())+'&';
											qs += 'momo_homePageID='+escape(jQuery('#momo_homePageID').val())+'&';
											qs += 'momo_themeStyle='+escape(jQuery("input[name=momo_themeStyle]:checked").val())+'&';
											// then set the src of the preview iframe
											jQuery('#momo_previewIFrame').get(0).src = '<?=trailingslashit(get_bloginfo('url'))?>?'+qs;
											// then, when the iframe is done loading, add the querystring to all the appropriate links
											// within the iframe, whenever it reloads.
											jQuery('#momo_previewIFrame').load( function() { 
												momo_addQueryStringVars( jQuery('#momo_previewIFrame').get(0).contentDocument, qs );
											} );
										} );
									} );
								</script>
							</div><!-- end momo_optionsRight -->
						</td>
						</tr>
						</table>
					</div><!-- end momo_mobileThemeSettings -->
				</div><!-- end momo_divSwapContainer -->
			</div><!-- end momo_tabContainer -->
	</div><!-- end momo_optionsContainer -->
</div><!-- end wrap -->
<?php
//		</div><!-- end momo_optionsLeft -->

} // end  function momo_configDisplay() 
?>