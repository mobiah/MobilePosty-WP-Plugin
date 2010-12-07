<?php
/*
*	MobilePosty home-calling functions
*	
*/

/*
*	Gathers data and uses wordpress's post function to submit all data to a server
*	This includes: plugin name, plugin version, admin-selected options, server environment info, api key, and domain
*/

function momo_report() {
	global $momo_options;
	if(DEBUG) { error_log("momo_report cron job"); }
	try {
	$postData = array();
	$postData['timeout'] = 10;
	$postData['body'] = array();
	$postData['body']['domain'] = $_SERVER['HTTP_HOST'];
	$postData['body']['serveradmin'] = $_SERVER['SERVER_ADMIN'];
	$postData['body']['version'] = MOMO_VERSION;
	if(DEBUG) { error_log("momo_report cron postData=" . var_export($postData,true) . '\n'); }
	$postData['body']['environment'] = serialize(momo_environmentInfo()); 
	if(DEBUG) { error_log("momo_report cron final postData=" . var_export($postData,true) . '\n'); }
	$doReport = wp_remote_post(MOMO_REPORTING_URL, $postData);
	if(DEBUG) { error_log("momo_report cron doReport=" . var_export($doReport,true) . '\n'); }
	return $doReport;
	}
	catch(Exception $ex) { 
		if(DEBUG) { error_log("momo_report ex=$ex"); }
	}
}

/*
function momo_report() {
	global $momo_options, $momo_key;
	$postData = array();
	$postData['timeout'] = 1;
	$postData['body'] = array();
	$postData['body']['plugin_name'] = 'mobileposty';
	$postData['body']['version'] = MOMO_VERSION;
	$postData['body']['options'] = $momo_options;
	$postData['body']['environment'] = momo_environmentInfo();
	$postData['body']['key'] = $momo_key;
	$postData['body']['domain'] = $_SERVER['HTTP_HOST'];
	$doReport = wp_remote_post(MOMO_REPORTING_URL, $postData);
	return $doReport;

}
*/
// always bind this function to the plugin-global reporting action
add_action(MOMO_REPORTING_ACTION, 'momo_report');

/*
*	Gather info about the Server Environment
*/
function momo_environmentInfo() {
	$serverInfo = array();
	$serverInfo['wp_version'] = get_bloginfo( 'version' );
	$serverInfo['wp_charset'] = get_bloginfo( 'charset' );
	$serverInfo['phpversion'] = phpversion();
	//$serverInfo['phpsettings'] = ini_get_all();
	//$serverInfo['phpextensions'] = get_loaded_extensions();
	//$serverInfo['_SERVER'] = $_SERVER;
	return $serverInfo;
}

?>
