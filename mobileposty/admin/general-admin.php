<?php
/*
*	Functions needed around the plugin
*
*
*/

add_action( 'admin_menu', 'momo_addMenus' );
function momo_addMenus() {
	if ( function_exists('add_menu_page') )
		add_menu_page(__('MobilePosty Settings'), __('MobilePosty'), 'manage_options', MOMO_PLUGIN_FILE, 'momo_configDisplay');
	add_submenu_page( MOMO_PLUGIN_FILE, __('Mobile Settings'), __('Mobile Settings'), 'manage_options', MOMO_PLUGIN_FILE, 'momo_configDisplay');
	add_submenu_page( MOMO_PLUGIN_FILE, __('Mobile Content'), __('Mobile Content'), 'manage_options', 'momo_content', 'momo_contentDisplay');
}

add_action( 'admin_init', 'momo_adminInit' );
function momo_adminInit () {

	// only show the CSS and JS if we're on an admin page which involves the mobiah plugin.
	if ( isMoMoAdminPage() ) {
		// make sure we've got thickbox
		add_thickbox();
		
		// include the general CSS for admin pages
		$stylesheetURL = MOMO_ADMIN_URL . '/momo-admin.css';
		wp_enqueue_style('momo-admin',$stylesheetURL,array(),false);
		
		// include the javascript which defines which cats/pages/posts are visible (in JSON)
		$globalsURL = get_bloginfo('url').'/wp-admin/?momo_globalJSVars';
		wp_enqueue_script('momo-globals-js',$globalsURL,array(),false);

		$functionsURL = MOMO_ADMIN_URL . '/js/momo-functions.js';
		wp_enqueue_script('momo-functions-js',$functionsURL,array(),false);

		$jQ2JSONURL = MOMO_ADMIN_URL . '/js/jquery.json-2.2.js';
		wp_enqueue_script('momo-jQ2JSON-js',$jQ2JSONURL,array(),false);
	}
	
	// Save a single page or post's visibility elements
	add_action('save_post', 'momo_savePostVisibilty');

}


/*
*	AJAX test.  This function does nothing but help confirm that an AJAX request can be
*	successfully handled.  
*/
add_action('wp_ajax_momo_ajaxTest', 'momo_ajaxTest');
function momo_ajaxTest() {
	echo "true";
	die();
}

/*
*	Test results.  If everything works well in the momo_envTest function at the top of the admin config/content
*	pages, then it will post to this function, causing it to save the "success" status.
*/
add_action('wp_ajax_momo_envTestSuccess', 'momo_envTestSuccess');
function momo_envTestSuccess() {
	global $momo_envTest, $momo_options;
	$momo_envTest = 'success';
	$momo_options['momo_envTest'] = 'success';
	update_option( MOMO_OPTIONS, $momo_options );
	die();
}

/*
*	tells us if we're on a mobiah mobile page - this function will always be a work in progress
*
*/
function isMoMoAdminPage() {
	if ( strpos( $_SERVER["SCRIPT_NAME"], '/wp-admin' ) === false ){
		// if the URL somehow doesn't have /wp-admin, this is not a momo admin page
		return false;
	}
	return ( strpos( $_SERVER["SCRIPT_NAME"], 'page.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'page-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["QUERY_STRING"], 'page=momo_config' ) !== false 
		|| strpos( $_SERVER["QUERY_STRING"], 'page=momo_content' ) !== false 
		|| strpos( $_SERVER["QUERY_STRING"], 'mobileposty.php' ) !== false 
		) ;
}

/*
* Saves the status of a post's mobile visibility
*
*/
function momo_savePostVisibilty($postID) {

	// if this is a page/post revision, or autosave, don't worry about it.
	if (wp_is_post_revision($postID) || wp_is_post_autosave($postID)) {
			return;
	}
	// if the visibility settings haven't been submitted, then don't try to save anything.
	if ( !array_key_exists( 'momo_checkVisibility', $_POST ) || $_POST['momo_checkVisibility'] != $postID ) {
		return;
	}
	
	$newVisibility = array();
	
	// set the elements of each post - page/author/comments/images
	if ( array_key_exists( 'momo_postVisible', $_POST ) && $_POST['momo_postVisible'] == 'TRUE') {
		$newVisibility['post'] = true;
	} else {
		$newVisibility['post'] = false;
	}
	if ( array_key_exists( 'momo_authVisible', $_POST ) && $_POST['momo_authVisible'] == 'TRUE') {
		$newVisibility['auth'] = true;
	} else {
		$newVisibility['auth'] = false;
	}
	if ( array_key_exists( 'momo_commVisible', $_POST ) && $_POST['momo_commVisible'] == 'TRUE') {
		$newVisibility['comm'] = true;
	} else {
		$newVisibility['comm'] = false;
	}
	if ( array_key_exists( 'momo_imgVisible', $_POST ) && $_POST['momo_imgVisible'] == 'TRUE') {
		$newVisibility['img'] = true;
	} else {
		$newVisibility['img'] = false;
	}

	// get some information about this page or post
	$postObj = &get_post($postID); 

	global $momo_options;
	// we have to alter the correct variable in the options array
	if ($postObj->post_type == 'post') {
		global $momo_visiblePosts;
		$momo_visiblePosts[$postID] = $newVisibility;
		$momo_options['momo_visiblePosts'] = $momo_visiblePosts;
	} elseif ($postObj->post_type == 'page') {
		global $momo_visiblePages;
		$momo_visiblePages[$postID] = $newVisibility;
		$momo_options['momo_visiblePages'] = $momo_visiblePages;
	}
	// then update the value in the options table
	update_option( MOMO_OPTIONS, $momo_options);
	
}

/*
*	Server Environment Testing
*/
function momo_envTester() {
	global $momo_options, $momo_envTest;
	// if we're doing the test, it means that we've not done it before (or at least we have no record of it)
	// so now that we're doing it, we'll say that it failed, and let the following code correct it. 
	// we will assume that if everything goes well, the final ajax call will change it from failure to success
	$momo_options['momo_envTest'] = 'failure';
	update_option(MOMO_OPTIONS, $momo_options);
?>
	<div id="momo_envTest" class="momo_message" >
		<p><?_e('First, we need to run a couple of tests to confirm that your server can run MobilePosty. Don\'t worry, this won\'t hurt a bit.');?></p>
	<?php
	$momo_PHPTest = '';
	$momo_JSONTest = '';
	$momo_remoteAPITest = '';
	$momo_WPVersionTest = '';
	
	if (version_compare(phpversion(), "5.0", ">=")) $momo_PHPTest = 'class="momo_pass"';
	if (function_exists(json_decode)) $momo_JSONTest = 'class="momo_pass"';
	if (wp_remote_retrieve_response_code(wp_remote_get(MOMO_REPORTING_URL)) == '200') $momo_remoteAPITest = 'class="momo_pass"';
	if (version_compare(get_bloginfo( 'version' ), '2.7', '>=')) $momo_WPVersionTest = 'class="momo_pass"';
	
	?>
		<dl class="momo_tests">
			<dt>PHP Version</dt>
			<dd <?php echo $momo_PHPTest; ?>>... PHP version is 5.0 or greater? </dd>	
			<dt>WordPress Version</dt>
			<dd <?php echo $momo_WPVersionTest; ?> >... WordPress version is 2.7 or greater?</dd>
			<dt>json_decode</dt>
			<dd <?php echo $momo_JSONTest; ?>>...json_decode (php function) is available?</dd>
			<dt>External Request to API</dt>
			<dd <?php echo $momo_remoteAPITest; ?>>...Outbound request to API Server ?</dd>
			<dt>AJAX request</dt>
			<dd id="momo_AJAXTest" >...Submitting AJAX request</dd>
		</dl>
		<script type="text/javascript">
			var momo_PHPTest = <?=( $momo_PHPTest == '' ? 'false' : 'true' )?>;
			var momo_WPVersionTest = <?=( $momo_WPVersionTest == '' ? 'false' : 'true' )?>;
			var momo_JSONTest = <?=( $momo_JSONTest == '' ? 'false' : 'true' )?>;
			var momo_remoteAPITest = <?=( $momo_remoteAPITest == '' ? 'false' : 'true' )?>;
			var momo_AJAXTest = false;
			// submit an ajax request, to see what the return status is...
			// see earlier in this file for the ajax hooks which handle this.
			jQuery.ajax({	type: 'POST',
							url: ajaxurl, 
							data: { "action" : 'momo_ajaxTest' },
							complete: function ( reqObj, status ) {
										if (status == 'success' && reqObj.responseText == 'true'){
											// it worked.  let's show that it worked.
											jQuery('#momo_AJAXTest').addClass('momo_pass').html('...AJAX request submitted successfully.');
											momo_AJAXTest = true;
										}
										
										// at this point (and only this point), we know the results of all the tests
										if ( momo_PHPTest && momo_WPVersionTest && momo_JSONTest && momo_remoteAPITest && momo_AJAXTest ) {
											// if they all passed, send a message via AJAX ('cause we know it works!) to wordpress
											// to update the momo_envTest variable, and show a confirmation to the user before
											// fading away the whole test info area. 
											jQuery.post( ajaxurl, { action : 'momo_envTestSuccess' } ); 
											momo_showMessage('<?_e('Congratulations.  Your setup is totally capable of running MobilePosty.  Now closing the pesky box.');?>', 'momo_contentMessage', true, false, true, 5000, 2000);
											// give 'em a chance to read the message, then hide the box.
											setTimeout( function(){ jQuery("#momo_envTest").hide(2000); }, 5000 );
										} else {
											// if something failed, we need to show an error message.  and hide nothing.
											momo_showMessage('<?_e('Warning - your server configuration may prevent the normal function of MobilePosty.');?>','momo_contentMessage', false, true, true );
										}
							}
						}
						);
		</script>
		
		<div class="momo_success"></div>
	</div>
<?php
}

/*
*	Takes a long title, and shortens it, keeping the beginning and the end intact
*
*/
function momo_shortenText($text, $length=50, $separator = ' ... '){

	if ( strlen($text) <= $length ) {
		return $text;
	}
	$separatorlength = strlen($separator) ;
	$maxlength = $length - $separatorlength;
	$start = $maxlength / 2 ;
	$trunc =  strlen($text) - $maxlength;

	return substr_replace($text, $separator, $start, $trunc);

}

/*
*	JSON encoding and decoding functions for php versions less than 5.2.0
*
*/

if (!function_exists('json_encode')) {
function json_encode($a=false) {
	if (is_null($a)) return 'null';
	if ($a === false) return 'false';
	if ($a === true) return 'true';
	if (is_scalar($a))
	{
	  if (is_float($a))
	  {
		// Always use "." for floats.
		return floatval(str_replace(",", ".", strval($a)));
	  }

	  if (is_string($a))
	  {
		static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
		return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
	  }
	  else
		return $a;
	}
	$isList = true;
	for ($i = 0, reset($a); $i < count($a); $i++, next($a))
	{
	  if (key($a) !== $i)
	  {
		$isList = false;
		break;
	  }
	}
	$result = array();
	if ($isList)
	{
	  foreach ($a as $v) $result[] = mmjson_encode($v);
	  return '[' . join(',', $result) . ']';
	}
	else
	{
	  foreach ($a as $k => $v) $result[] = mmjson_encode($k).':'.mmjson_encode($v);
	  return '{' . join(',', $result) . '}';
	}
}
}

if ( !function_exists('json_decode') ){
function json_decode($json, $assoc = false)
{ 
	// at the moment, the $assoc = false argument does nothing but make this function
	// have the same number of arguments as the original json_decode
    // Author: walidator.info 2009
    $comment = false;
	$x = NULL;
    $out = '$x=';
   
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if ($json[$i] == '{')        $out .= ' array(';
            else if ($json[$i] == '}')    $out .= ')';
            else if ($json[$i] == ':')    $out .= '=>';
            else                         $out .= $json[$i];           
        }
        else $out .= $json[$i];
        if ($json[$i] == '"')    $comment = !$comment;
    }
    eval($out . ';');
    return $x;
} 
} 
function momo_jsonDecode($json, $assoc = false)
{ 
    $comment = false;
    $out = '$x=';
   
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if ($json[$i] == '{')        $out .= ' array(';
            else if ($json[$i] == '}')    $out .= ')';
            else if ($json[$i] == ':')    $out .= '=>';
            else                         $out .= $json[$i];           
        }
        else $out .= $json[$i];
        if ($json[$i] == '"')    $comment = !$comment;
    }
//	pre($out);
    eval($out . ';');
    return $x;
}
/*
*	Gathers info about the theme files, and returns an array with those theme files which are
*	valid page templates.
*/

function momo_getTemplates() {
	$pageTemplates = array();
	$templates = glob( MOMO_THEME_PATH.'/*.php' );

	if ( is_array( $templates ) ) {
		foreach ( $templates as $template ) {
			$basename = str_replace( MOMO_THEME_PATH.'/', '', $template);

			// don't allow template files in subdirectories
			if ( false !== strpos($basename, '/') )
				continue;

			$template_data = implode( '', file( $template ));

			$name = '';
			if ( preg_match( '|Template Name:(.*)$|mi', $template_data, $name ) )
				$name = ( function_exists( '_cleanup_header_comment' ) ? _cleanup_header_comment($name[1]) : trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $name[1])) );

			if ( !empty( $name ) ) {
				$pageTemplates[trim( $name )] = $basename;
			}
		}
	}
	
	return $pageTemplates;
}
?>