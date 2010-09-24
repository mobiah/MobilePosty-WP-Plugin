<?php
/*
 * admin/categories.php
 * This file modifies the category management area of the wordpress backend
 * in 2 ways:
 * 	1.  adding a checkbox under the category description
 *	2.	adding a column in the list view of categories, indicating the viewable status
 *  2a. allows bulk-save of visibility in the list view of categories
 *	
 *	UPDATE:  2 and 2a only work for wordpress versions less than 3.0
*/


// all the filters/actions/etc.. which only need to happen when loading an admin page
function momo_categoryAdminInit () {
	// choose the best place to put the checkbox - if we're in the edit page, then use the nicer one.
	if ($_REQUEST['action']=='edit') {
		add_action ( 'edit_category_form_fields', 'momo_catCheckBox');
	} else {
		add_action ( 'edit_category_form', 'momo_newCatCheckBox');
	}
	
	// saving category visibility from category pages
	add_action('edit_category','momo_saveCatVisibilty');
	add_action('create_category','momo_saveCatVisibilty');

	// add new columns to the category list view page
	add_action('manage_categories_columns', 'momo_catColumn');
	add_filter("manage_category_custom_column", 'momo_catColumnValue', 10, 3); // >= WPv3.0
	add_filter('manage_categories_custom_column', 'momo_catColumnValue', 10, 3); // < WPv3.0

	// save multiple categories' visibility settings
	// then redirect to get rid of a lingering query string
	if ( array_key_exists( 'momo_catList', $_GET ) && is_array($_GET['momo_catList'])){ 
		momo_catBulkSave();
		wp_redirect($_SERVER['PHP_SELF']);
		exit();
	}
}
add_action( 'admin_init', 'momo_categoryAdminInit' );



/*
* Add the checkbox in the category input fields
*/
function momo_catCheckBox($catObject) {
	global $momo_visibleCats; 

	$catSetVisible = momo_catSetVisible($catObject->term_id);

	// see if there are any invisible ancestors
	$invisAncestorObj = NULL;
	$invisAncestorID = momo_getInvisCatAncestor($catObject->parent);
	if ( !is_null($invisAncestorID) ) {
		$invisAncestorObj = &get_category( $invisAncestorID );
	}
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="momo_catMobile"><?php _e('Include in mobile site?') ?></label></th>
		<td>
			<input type="checkbox" name='momo_catMobile' id='momo_catMobile' value="TRUE" <?=( $catSetVisible ? "checked" : '' )?> style="width:20px;"/>
			<br />
		<span class="description"><?php _e('Include this category in the mobile version of this site?'); ?></span><br />
		<span style="font-weight: bold;"><?=( !is_null($invisAncestorID) ? _e('WARNING: One of the ancestor categories is currently not included in the mobile site. <br /> Edit "').'<a href="edit-tags.php?action=edit&taxonomy=category&tag_ID='.$invisAncestorID.'">'.$invisAncestorObj->name.'</a>"' : '' )?></span>
		</td>
	</tr> 
	<?php
}


/*
* Add the checkbox in the add NEW category input fields on the main categories page
* (this is necessary, because wp-admin/categories.php does not call the edit_category_form_fields action when
* showing the "new category" form)
*/
function momo_newCatCheckBox($catObject) {
	?>
	<label for="momo_catMobile"><?php _e('Include in mobile site?') ?></label><input type="checkbox" name='momo_catMobile' id='momo_catMobile' value="TRUE" checked style="width:20px;"/>
	<span class="description" style="clear:all;"><?php _e('Include this category in the mobile version of this site?'); ?></span>
	<?php
}

/*
* Saves the status of the category's mobile visibility
*
*/
function momo_saveCatVisibilty($catID) {
	global $momo_visibleCats, $momo_options; 
	if ( array_key_exists( 'momo_catMobile', $_POST ) && $_POST['momo_catMobile'] == 'TRUE') {
		$momo_visibleCats[$catID] = true;
	} else {
		$momo_visibleCats[$catID] = false;
	}
	
	// then update the value in the options table
	$momo_options['momo_visibleCats'] = $momo_visibleCats;
	update_option(MOMO_OPTIONS, $momo_options);
}


/*
*  Add a new column to the category list table
*/
// Add the new column to the columns array
function momo_catColumn($cols) {
	$cols['momo_visibility'] = 'Mobile';
	return $cols;
}

// create a function to return the visibility of a given category
function momo_catColumnValue($value, $column_name, $id) {
	if ($column_name == 'momo_visibility') {
		global $momo_visibleCats;
		$checked = '';
		if ( momo_catSetVisible($id) ){
			$checked = 'checked';
		}
		return "<input type=\"checkbox\" name=\"momo_visible[]\" value=\"$id\" $checked />
				<input type=\"hidden\" name=\"momo_catList[]\" value=\"$id\" />";
	}
	return $value;
}

// save multiple category settings
function momo_catBulkSave(){
	global $momo_visibleCats, $momo_options;
	// go through all the submitted categories, and see if they checked the box, updating our array of visible categories.
	foreach ( $_GET['momo_catList'] as $catID ){ 
		if ( is_array($_GET['momo_visible']) && in_array($catID, $_GET['momo_visible']) ) {
			$momo_visibleCats[$catID] = true;
		} else {
			$momo_visibleCats[$catID] = false;
		}
	}
	// then update the value in the options table
	$momo_options['momo_visibleCats'] = $momo_visibleCats;
	update_option(MOMO_OPTIONS, $momo_options);
}



?>