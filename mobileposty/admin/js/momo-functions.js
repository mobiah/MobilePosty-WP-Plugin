/*
*	mobileposty functions
*
*/

/*
*	Check the page heirarchy to see if this page has invisible parents
*	returns the first invisible ancestor or null, just like the PHP function
*/
function momo_getInvisPageAncestor(pageID) {
	if ( typeof momo_visiblePages != 'object' || typeof momo_pageParents != 'object' ) {
		// if somehow the global list of visible pages isn't around...  
		// there's nothing we can do.
		return null;
	}
	if ( !momo_pageSetVisible(pageID) ) {
		return pageID;
	}
	curParentID = momo_pageParents[pageID];
	while ( typeof curParentID != 'undefined' && curParentID != 0 ) {
		if ( !momo_pageSetVisible(curParentID) ) {
			return curParentID;
		}
		curParentID = momo_pageParents[curParentID];
	}
	// if we get here, and no ancestor has been marked as invisible, then return null;
	return null;
}

function momo_pageSetVisible(pageID) {
	// tells if the page passed in is set to be invisible
	if ( typeof momo_visiblePages[pageID] == 'object'
			&& typeof momo_visiblePages[pageID]['post'] == 'boolean'
			&& !momo_visiblePages[pageID]['post'] ) {
		return false;
	} else {
		return true;
	}
}


/*
*	Check the cat heirarchy to see if this post is in any invisible categories
*	returns the first invisible category (or ancestor) or null, just like the PHP function
*/
function momo_getInvisPostCat(postID) {
	if ( typeof momo_visiblePosts != 'object' || typeof momo_postCats != 'object' ) {
		// if somehow the global list of visible posts isn't around...  
		// there's nothing we can do.
		return null;
	}
	postCatArray = momo_postCats[postID];
	if (typeof postCatArray == 'object') { // it REALLY should be. all posts should be there.
		for ( var key in postCatArray ) {
			if ( !momo_catSetVisible(postCatArray[key]) ){
				// this is the invisible category ID!
				return postCatArray[key];
			}
		}
	} 

	// if we have found NO categories which are invisible, then everything is okay.
	return null;

}

function momo_postSetVisible(postID) {
	// tells if the post passed in is set to be invisible
	if ( typeof momo_visiblePosts[postID] == 'object'
			&& typeof momo_visiblePosts[postID]['post'] == 'boolean'
			&& !momo_visiblePosts[postID]['post'] ) {
		return false;
	} else {
		return true;
	}
}

// considering categories, is the post visible?
function momo_postVisible(postID){
	// to be visible, there must be no invisible containing cateogries, and 
	var invisCatID = momo_getInvisPostCat(postID);
	return ( invisCatID == null && momo_postSetVisible(postID) );
}

/*
*	Check the cat heirarchy to see if this cat has invisible parents
*	returns the first invisible ancestor or null, just like the PHP function
*/
function momo_getInvisCatAncestor(catID) {
	if ( typeof momo_visibleCats != 'object' || typeof momo_catParents != 'object' ) {
		// if somehow the global list of visible cats isn't around...  
		// there's nothing we can do.
		return null;
	}
	if ( !momo_catSetVisible(catID) ) {
		return catID;
	}
	curParentID = momo_catParents[catID];
	while ( typeof curParentID != 'undefined' && curParentID != 0 ) {
		if ( !momo_catSetVisible(curParentID) ) {
			return curParentID;
		}
		curParentID = momo_catParents[curParentID];
	}
	// if we get here, and no ancestor has been marked as invisible, then return null;
	return null;
}

// is this category set to be visible?
function momo_catSetVisible(catID) {
	// if the cat passed is set to be invisible
	if ( typeof momo_visibleCats[catID] == 'boolean'
			&& !momo_visibleCats[catID] ) {
		return false;
	} else {
		return true;
	}
}

// is it ACTUALLY visible?
function momo_catVisible(catID) {
	return ( momo_getInvisCatAncestor(catID) == null );
}

/*
*	Functions for causing DHTML page changes on admin pages
*
*/

// on wp-admin/edit-page.php
// test a parent for visibility, and show a notification if there's an invisible ancestor
function momo_testPageParent( eventObj ){
	// which parent have they selected?
	selectObj = eventObj.target;
	selectedID = selectObj.options[selectObj.selectedIndex].value;
	// check to see if the selected parent page or any ancestors are invisible
	invisAncestorID = momo_getInvisPageAncestor(selectedID);
	if ( invisAncestorID != null ) {
		// if so - first, change the "edit" link, so that they can edit the new parent
		jQuery('#ancestor_alert #ancestor_edit_link').attr('href',"page.php?action=edit&post="+invisAncestorID);
		// then, make sure to show the alert div
		jQuery('#ancestor_alert').show();
	} else {
		// otherwise, hide the div.
		jQuery('#ancestor_alert').hide();
	}

}


// on wp-admin/edit-post.php
// test all selected categories (and ancestors) for visibility and show an message if one is invisible
function momo_testPostCats() {
	// look through all the category checkboxes to see if any of the checked boxes
	// are invisible
	checkList = jQuery('#categorychecklist input[type=checkbox]');
	for ( var i in checkList ) {
		if ( checkList[i].checked ) {
			invisCatID = momo_getInvisCatAncestor( checkList[i].value );
			if ( null != invisCatID ){
				// we found an invisible ancestor, first change cat edit link
				jQuery('#cat_invis_alert #cat_edit_link').attr('href',"categories.php?action=edit&cat_ID="+invisCatID);
				// then show the alert div
				jQuery('#cat_invis_alert').show();
				return;
			}
		}
		// if we got all the way here, and no cats were invisible, then hide the alert div.
		// there's nothing to alert
		jQuery('#cat_invis_alert').hide();
	}
	
}

// on the main admin/config page
// switch tabs when tabs are clicked
// the div to be activated should contain the name of the clicked li
//   sans 'momo_' and sans 'Tab'
function momo_configSwitchTab( eventObj ) {
	// get the tab that they actually clicked.
	var clickedTab = eventObj.target;
	// sanity checking - is this a momo tab? if not, get outta here.
	if ( !jQuery(clickedTab).hasClass('momo_tab') ) {
		return;
	}	// if this is already the selected tab, do nothing.
	if ( jQuery(clickedTab).hasClass('selected') ) {
		return;
	}
	// "un-select" all tabs and disable all div tables
	jQuery('#momo_tabs li').removeClass('selected');
	jQuery('#momo_divSwapContainer > div').hide();
	// select the right tab, and show the associated div & table
	jQuery(clickedTab).addClass('selected');
	
	// go through each tab,
	jQuery('#momo_divSwapContainer > div').each( function(){
		//get the div type (from its ID - page/cat/post/etc),
		var divType = clickedTab.id.replace('momo_','').replace('Tab','');
		// and if this is the clicked tab, show() it.
		if ( this.id.indexOf(divType) != -1 ) {
			jQuery(this).show();
		}
	});
}

// update the global array of pages with this page's visibility info
function momo_updatePage( pageID ) {
	var pageVisibility = {
		"post": jQuery('#page'+pageID+'PageVisible').attr('checked'),
		"auth": jQuery('#page'+pageID+'AuthVisible').attr('checked'),
		"comm": jQuery('#page'+pageID+'CommVisible').attr('checked'),
		"img": jQuery('#page'+pageID+'ImgVisible').attr('checked'),
		"template": jQuery('#page'+pageID+'Template').val()
	};
	momo_visiblePages[ pageID.toString() ] = pageVisibility;
}

// update the entire global array of pages
function momo_updateAllPages() {
	if ( typeof momo_pageParents != 'object' ) {
		return;
	}
	for ( var i in momo_pageParents ) {
		momo_updatePage( i );
	}
}

// update the global array of posts with this post's visibility info
function momo_updatePost( postID ) {
	var postVisibility = {
		"post": jQuery('#post'+postID+'PostVisible').attr('checked'),
		"auth": jQuery('#post'+postID+'AuthVisible').attr('checked'),
		"comm": jQuery('#post'+postID+'CommVisible').attr('checked'),
		"img": jQuery('#post'+postID+'ImgVisible').attr('checked')
	};
	momo_visiblePosts[ postID.toString() ] = postVisibility;
}

// update the entire global array of posts
function momo_updateAllPosts() {
	if ( typeof momo_postCats != 'object' ) {
		return;
	}
	for ( var i in momo_postCats ) {
		momo_updatePost( i );
	}
}

// update the global array of cats with this cat's visibility info
function momo_updateCat( catID ) {
	momo_visibleCats[ catID.toString() ] = jQuery('#cat'+catID+'CatVisible').attr('checked');
}

// update the entire global array of categories
function momo_updateAllCats() {
	if ( typeof momo_catParents != 'object' ) {
		return;
	}
	for ( var i in momo_catParents ) {
		momo_updateCat( i );
	}
}

// decides whether to show a confirmation about leaving the page, if the user has changed
// content settings, and has not saved them.
var momo_unsavedEdits = false;
function momo_contentOnUnload () {
	if ( typeof(momo_unsavedEdits) == 'boolean' && momo_unsavedEdits ) {
		return "You have changed some Mobile Content Settings without saving.  Are you sure?";
	}
}

// triggered when a page's Visible? box is changed
function momo_clickPageVisBox( eventObj ) {
	// get the clicked checkbox
	var clickedBox = eventObj.target;
	// sanity checking - is this a checkbox? and the right kind? if not, get outta here.
	if ( clickedBox.type != 'checkbox' || clickedBox.id.indexOf('Visible') == -1 ) {
		return;
	}	

	// update the global javascript variable
	momo_updateAllPages();
	// then mark the appropriate pages' visibility
	momo_markInvisPages();

	// we now have unsaved edits if something has been clicked!
	momo_unsavedEdits = true;
}

// triggered when a category's Visible? box is changed
function momo_clickCatVisBox( eventObj ) {
	// get the clicked checkbox
	var clickedBox = eventObj.target;
	// sanity checking - is this a checkbox? and the right kind? if not, get outta here.
	if ( clickedBox.type != 'checkbox' || clickedBox.id.indexOf('Visible') == -1 ) {
		return;
	}	

	// update the global javascript variable
	momo_updateAllCats();
	// then mark the appropriate categories' visibility
	momo_markInvisCats();
	// AND mark the appropriate posts' visibility 
	momo_markInvisPosts();

	// we now have unsaved edits if something has been clicked!
	momo_unsavedEdits = true;
}

// triggered when a category's Visible? box is changed
function momo_clickPostVisBox( eventObj ) {
	// get the clicked checkbox
	var clickedBox = eventObj.target;
	// sanity checking - is this a checkbox? and the right kind? if not, get outta here.
	if ( clickedBox.type != 'checkbox' || clickedBox.id.indexOf('Visible') == -1 ) {
		return;
	}	

	// update the global javascript variable
	momo_updateAllPosts();
	// then mark the appropriate posts' visibility 
	momo_markInvisPosts();
	
	// we now have unsaved edits if something has been clicked!
	momo_unsavedEdits = true;
}


// on the main admin/config page
// add momo_invisible css class to pages who won't be visible
function momo_markInvisPages() {
	jQuery('div#momo_pagesTable tr').each( function () {
		// if this isn't a row associated with a Page, do nothing - this shouldn't happen.
		if ( this.id.indexOf('page') == -1 ) {
			return;
		}
		// get the pageID
		var pageID = this.id.replace('page','').replace('Row','');
		// does it have an invisible ancestor? or is the page itself invisible?
		var invisAncestorID = momo_getInvisPageAncestor(pageID);
		// if so, add the invisible css class.
		if ( invisAncestorID != null ) {
			jQuery(this).addClass('momo_invisible');
		} else {
			jQuery(this).removeClass('momo_invisible');
		}
	} );
}

// on the main admin/config page
// add momo_invisible css class to posts who won't be visible
function momo_markInvisPosts() {
	jQuery('div#momo_postsTable tr').each( function () {
		// if this isn't a row associated with a Page, do nothing
		if ( this.id.indexOf('post') == -1 ) {
			return;
		}
		var postID = this.id.replace('post','').replace('Row','');
		if ( !momo_postVisible(postID) ){
			jQuery(this).addClass('momo_invisible');
		} else {
			jQuery(this).removeClass('momo_invisible');
		}
	} );
}

// on the main admin/config page
// add momo_invisible css class to Cat rows who won't be visible
function momo_markInvisCats() {
	// go through all of the categories
	jQuery('div#momo_catsTable tr').each( function () {
		// if this isn't a row associated with a Category, do nothing
		if ( this.id.indexOf('cat') == -1 ) {
			return;
		}
		var catID = this.id.replace('cat','').replace('Row','');
		var invisAncestorID = momo_getInvisCatAncestor(catID);
		if ( invisAncestorID != null ) {
			jQuery(this).addClass('momo_invisible');
			// also mark the category names in the posts table!
			jQuery('span#cat'+catID).addClass('momo_invisible');
// if you want to completely DISABLE the checkboxes of invisible cats... uncomment the following line.
//			jQuery(this.id + ' input[type=checkbox] ').attr('disabled', true);
		} else {
			jQuery(this).removeClass('momo_invisible');
			jQuery('span#cat'+catID).removeClass('momo_invisible');
// but don't forget to uncomment this line if uncommenting the above line.
//			jQuery(this.id + ' input[type=checkbox] ').attr('disabled', false);
		}
		
	} );
}

// show a message to the user on the main admin/config page, then fade it out.
// give it a message and a div to dump the message into
function momo_showMessage( strMsg, divID, useFadeOut, isError, resizeDiv, timeOut, fadeTime  ) {

	if ( typeof(strMsg) == 'undefined' || strMsg.length == 0 ) {
		// no message to show?  don't do anything.
		return;
	}
	if ( typeof(divID) == 'undefined' ) {
		divID = 'momo_message';
	}
	if ( typeof(useFadeOut) == 'undefined' ) {
		useFadeOut = true;
	}
	if ( typeof(isError) == 'undefined' ) {
		isError = false;
	}
	if ( typeof(resizeDiv) == 'undefined' ) {
		resizeDiv = false;
	}
	if ( typeof(timeOut) == 'undefined' ) {
		timeOut = 5000;// wait for a default of 3 seconds.
	}
	if ( typeof(fadeTime) == 'undefined' ) {
		fadeTime = 1000; // fade for a default of 1 second
	}
	
	if ( isError ) {
		jQuery('div#'+divID).addClass('momo_error_message');
	} else {
		jQuery('div#'+divID).removeClass('momo_error_message');
	}
	// show the confirmation/message
	jQuery('div#'+divID).html(strMsg).show(); 
	// resize the container.
	if ( resizeDiv ) {
		jQuery('div#'+divID).parent().height(jQuery('div#'+divID).outerHeight());
	}
	// then maybe set a timeout to let the div disappear
	if ( useFadeOut ) {
		setTimeout( function(){ jQuery('div#'+divID).fadeOut(fadeTime); }, timeOut );
	}
}

// send an ajax request to a URL.
// first argument is the checkbox that was clicked
function momo_ajax ( url, data, msgDivID, loadingDivID, useFadeOut, resizeDiv ) {

	if ( typeof(msgDivID) == 'undefined' ) {
		msgDivID = 'momo_message';
	}
	if ( typeof(loadingDivID) == 'undefined' ) {
		loadingDivID = 'momo_loading';
	}
	if ( typeof(useFadeOut) == 'undefined' ) {
		useFadeOut = true;
	}
	if ( typeof(resizeDiv) == 'undefined' ) {
		resizeDiv = false;
	}

	// hide any existing messages
	jQuery('div#'+msgDivID).hide();
	// post to the url specified
	jQuery.post( url , data,
					function ( strMsg ){  
						// upon completion/callback hide the spinning/loading animation
						jQuery('div#'+loadingDivID).hide();
						// is there an error?
						isError = strMsg.toLowerCase().indexOf( 'error:' ) != -1 ;
						if ( isError ) useFadeOut = false;
						// and show the message returned.
						momo_showMessage( strMsg, msgDivID, useFadeOut, isError, resizeDiv );
					} );
	// show the spinning/loading animation while we wait.
	jQuery('div#'+loadingDivID).show();
}

// fill the mobile phone preview window
var momo_currentPreviewURL = null;
function momo_preview ( url ) {

	if ( typeof(url) == 'undefined' ) {
		return; // nothing to do without a URL
	}
	// just in case, let's sync the global variables and the checkboxes
	momo_updateAllCats();
	momo_updateAllPosts();
	momo_updateAllPages();
	// now, send the (maybe) updated data off.
	// and show the html output in the preview window
	momo_ajax( url,
				{	"momo_visibleCats" :  jQuery.toJSON(momo_visibleCats), 
					"momo_visiblePages" : jQuery.toJSON(momo_visiblePages),
					"momo_visiblePosts" : jQuery.toJSON(momo_visiblePosts) },
				'momo_previewWindow',
				'momo_previewLoading',
				false
				);
	momo_currentPreviewURL = url;
}

// allow the user to to choose an image with the standard wordpress image chooser (media-upload.php)
// slightly changing how the javascript works to achieve this - change where on the page and what
// the iframe inserts

function momo_getMediaImage( targetFieldID, targetImgID ) {
	tb_show.apply(null, new Array('', 'media-upload.php?type=image&amp;TB_iframe=true&amp;')); 
	var iframe = jQuery('#TB_iframeContent');
	iframe.load(function(){
		var iframeWin = iframe[0].contentWindow;
		var iframeDoc = iframe[0].contentWindow.document;
		var iframeJQuery = iframe[0].contentWindow.jQuery;

		// do not do the editing of the form in the media library tab
		if ( iframe[0].contentWindow.location.href.indexOf('tab=library') != -1 ) {
			iframeJQuery('td.savesend').find('input.button').val('Use image for Mobile Header');
			return;
		}
		// inserts the url of the image into the opening window's target field
		// and closes the thickbox iframe.
		var insertAndClose = function( eventObj ){
				// this parent business is in case the user uploads two or more images
				// and we need to make sure we insert the one in the table where the user 
				// clicked.
				var imgurl = iframeJQuery(eventObj.target).parent().parent().parent().find('.urlfield').val();
				jQuery( '#'+targetFieldID ).val(imgurl);
				jQuery( '#'+targetImgID ).attr('src',imgurl);
				tb_remove();
			};

			// inserts a button into a media container
		var insertButton = function( containers ) {
			var button = iframeDoc.createElement('input');
			button.type = 'submit';
			iframeJQuery(button).addClass('button').val('Use image for Mobile Header');
				
			// Click event of some description
			iframeJQuery(button).click(insertAndClose);
			containers.addClass('hasInsertButton').prepend(button);
		}		

		// hijack the wordpress wp_updateMediaForm function call the original WP version,
		// then to do some manipulation after it finished.
		// this is to accomodate the flash upload method.
		var wp_updateMediaForm = iframeWin.updateMediaForm;
		iframeWin.updateMediaForm = function () {
				wp_updateMediaForm();
				buttonContainers = iframeJQuery('td.savesend').not('.hasInsertButton');
				if (buttonContainers) {
					insertButton(buttonContainers);
				}	
			};

		// This section is for the browser upload method, and assumes the
		// page has just been loaded with the info about the uploaded file
		// displayed.
		var buttonContainer = iframeJQuery('td.savesend');
		if (buttonContainer){
			insertButton(buttonContainer);
		}

	});

	// override default image uploader 'add to post/send to editor' action 
	// this covers the media library selection method
	// this might also be all that needs be done in the future if WP fixes the bug
	// where "insert into post" doesn't show up.
	window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		jQuery( '#'+targetFieldID ).val(imgurl);
		jQuery( '#'+targetImgID ).attr('src',imgurl);
		tb_remove();
	}
}

// set the field containing the url of the header image to blank, and also the preview URL to blank
function momo_removeHeaderImage( targetFieldID, targetImgID, blankImgURL ) {
	jQuery( '#'+targetFieldID ).val('');
	jQuery( '#'+targetImgID ).attr('src',blankImgURL);
}


/*
*	add some query string variables to all the links under a certain DOM element
*/
function momo_addQueryStringVars( elementID, qs ) {
	jQuery( 'a', elementID ).attr( 'href', function () {
			// if there's isnt already a ? in the current href, we need one to pass a query string
			// otherwise, just append that query string on with an &
			if ( this.href.indexOf('?') == -1 ) {
				return this.href + '?' + qs;
			} else {
				return this.href + '&' +qs;
			}
		} );
}
