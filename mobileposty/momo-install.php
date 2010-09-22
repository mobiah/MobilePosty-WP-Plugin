<?php
/*
*	MobilePosty installation functions
*	mostly covers installing the daily reporting home-called
*	and copying the mobile theme files into the themes folder
*	both are done on activation
*/

// for registering activation/deactivation functions, we need to pass in the main plugin file, which in our case,
// is the "includer" of this file.
$backtrace = debug_backtrace();
$mainFile = $backtrace[0]['file'];

// a couple of hooks to set up scheduled reporting routines (or remove them if deactivating)
// see momo-hooks.php
register_activation_hook( $mainFile, 'momo_addReporting' );
register_deactivation_hook( $mainFile, 'momo_removeReporting');
/*
*	Makes sure that there is an event which does the home-calling
*	This function can be called at activation, and potentially on every page load
*	to make sure it's there.
*/
function momo_addReporting() {
	// If there is no next scheduled reporting time, then stick it in the schedule (daily)
	if ( !wp_next_scheduled(MOMO_REPORTING_ACTION) ) {
		wp_schedule_event( time(), 'daily', MOMO_REPORTING_ACTION ); // hourly, daily and twicedaily
	} 
}
function momo_removeReporting() {
	wp_clear_scheduled_hook(MOMO_REPORTING_ACTION);
}
// this call makes the momo_addReporting get called as soon as all plugins are loaded.
// this happens on every page load.  if that's undesirable (as it already is supposed to get
// added on plugin activation, then delete this line.
add_action( 'plugins_loaded', 'momo_addReporting');



// one hook to install the theme when activated
// (this hook requires the main plugin filename to work correctly)
register_activation_hook( $mainFile, 'momo_themeInstall' );
/*
*	Installs mobile theme and any other "on-activation" needs
*	momo_themeInstall is called when the user activates this plugin
*/
function momo_themeInstall() {

	$wpThemeDir = get_theme_root();
	$permissions = fileperms($wpThemeDir);

	// the location of the mobile theme within the plugin files
	$copyFrom = MOMO_PATH."/mobileposty-theme";
	// where to put the theme files
	$copyTo = $wpThemeDir.'/'.MOMO_DEFAULT_MOBILE_THEME;
	
	// only create the folder and subfolders of the theme folder if the main theme folder isn't there at all.
	if ( !is_dir( $wpThemeDir.'/'.MOMO_DEFAULT_MOBILE_THEME ) ) {
		momo_smartCopy($copyFrom,$copyTo);
	}	
}

/*
*	Recursive file copy function
*/
function momo_smartCopy($source, $dest, $options=array('folderPermission'=>0775,'filePermission'=>0775))
{
	$result=false;
   
	if (is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if (!file_exists($dest)) {
				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
			}
			$__dest=$dest."/".basename($source);
		} else {
			$__dest=$dest;
		}
		$result=copy($source, $__dest);
		chmod($__dest,$options['filePermission']);
	   
	} elseif(is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				@mkdir($dest);
				chmod($dest,$options['filePermission']);
			}
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			}
		}

		$dirHandle=opendir($source);
		while($file=readdir($dirHandle))
		{
			if($file!="." && $file!="..")
			{
				 if(!is_dir($source."/".$file)) {
					$__dest=$dest."/".$file;
				} else {
					$__dest=$dest."/".$file;
				}
				//echo "$source/$file ||| $__dest<br />";
				$result=smartCopy($source."/".$file, $__dest, $options);
			}
		}
		closedir($dirHandle);
	   
	} else {
		$result=false;
	}
	return $result;
} 
?>