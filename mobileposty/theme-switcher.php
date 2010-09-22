<?php

/*
*	functions related to choosing which template to use by detecting browser and using $_REQUEST vars.
*
*
*/

function momo_themeSwitcherInit() {
	global $momo_enabled;
	if ( $momo_enabled ) {
		add_action('wp_footer', 'momo_switcherLink');
	}
}
add_action('init', 'momo_themeSwitcherInit');
add_filter('template', 'momo_themeSwitcher');
add_filter('stylesheet', 'momo_stylesheetSwitcher');


/*
*	This function decides if we should show a mobile theme or not
*/
function momo_themeSwitcher ( $currentTemplate ) {
	global $momo_isMobile;
	if ($momo_isMobile) {
		// TO DO:  add an ability to choose which theme should be the mobile theme/
		// but check for existence of that theme.
		return MOMO_MOBILE_THEME;	
	}
	return $currentTemplate;
}

/*
*	This function decides if we should show a mobile stylesheet or not
*	( Until there appears to be a contradictory reason, stylesheet and template are the same.
*/
function momo_stylesheetSwitcher ( $currentStylesheet ) {
	global $momo_isMobile;
	if ($momo_isMobile) {
		// TO DO:  add an ability to choose which stylesheet should be the mobile stylesheet
		// ...but check for existence.
		return MOMO_MOBILE_STYLESHEET;	
	}
	return $currentStylesheet;
}

/*
*	Add a link to the footer of pages to allow the user to switch between mobile and regular theme
*
*/
function momo_switcherLink() {
	global $momo_isMobile, $momo_showSwitcherLink;
	
	// should we be showing the link at all?
	if ( $momo_showSwitcherLink == 'never' ) { // nope, never.
		return;
	}
	if ( $momo_isMobile 
		&& $momo_showSwitcherLink == 'standard'
		&& !( array_key_exists( MOMO_THEME_SWITCHER, $_COOKIE ) && $_COOKIE[MOMO_THEME_SWITCHER] == 'mobile' )
		&& !( array_key_exists( MOMO_THEME_SWITCHER, $_GET ) && $_GET[MOMO_THEME_SWITCHER] == 'mobile' ) ) {
		// if we're viewing the mobile site, and the user says only show on the standard site, don't show the link
		// EXCEPT when the user is viewing the mobile site because of their cookie or querystring!
		return;
	}
	if ( !$momo_isMobile 
		&& $momo_showSwitcherLink == 'mobile' 
		&& !( array_key_exists( MOMO_THEME_SWITCHER, $_COOKIE ) && $_COOKIE[MOMO_THEME_SWITCHER] == 'standard' )
		&& !( array_key_exists( MOMO_THEME_SWITCHER, $_GET ) && $_GET[MOMO_THEME_SWITCHER] == 'standard' ) ) { 
		// if we're viewing the standard site, and the user says only show on the mobile site, don't show the link
		// EXCEPT when the user is viewing the standard site because of their cookieor querystring!
		return;
	}
	// ok, decide what to show
	if ( $momo_isMobile ){
		$newArg = 'standard';
		$linkText = 'Switch to standard site';
	} else {
		$newArg = 'mobile';
		$linkText = 'Switch to mobile site';
	}
	?>
	<br />
	<span style="text-align: center;">
		<a rel="external" href="<?=add_query_arg( MOMO_THEME_SWITCHER, $newArg)?>"><?=$linkText?></a>
	</span>
<?php
} // end momo_switcherLink

/*
*	Tests the environment for variables which indicate which page to show
*
*/
function momo_testForMobile() {
	global $momo_enabled;
	// no checking needs to happen if the mobile plugin/site is not enabled!
	if (!$momo_enabled){
		return false;
	}
	// don't mobilize the admin panels
	if ( is_admin() ) {
		return false;
	}
	// by default, the mobile site should NOT be on.
	$isMobile = false;
	
	// first try this : uses user-agent, HTTP headers
	$browserIsMobile = browser_detection();
	$isMobile = $browserIsMobile; 

	// next, see if the cookie hold evidence of a previous manual selection of mobile or not
	if ( array_key_exists( MOMO_THEME_SWITCHER, $_COOKIE ) ) {
		$isMobile = ( $_COOKIE[ MOMO_THEME_SWITCHER ] == 'mobile' );
	}
	
	// lastly, check if the setting was in the querystring
	if ( array_key_exists( MOMO_THEME_SWITCHER, $_GET ) ) {
		$isMobile = ( $_GET[ MOMO_THEME_SWITCHER ] == 'mobile' );
	}
	
	// if we're only previewing from the backend, we ALWAYS want to show the mobile version,
	// and we never want to set a cookie. Maintain the user's chosen mobile/notmobile cookie state.
	if ( momo_isPreview() ) {
		return true;
	}
	
	// Cookie-setting logic
	
	// if we're in a standard browser, and they have clicked a link with momo_themeSwitcher = 'mobile'
	// then we want to set the cookie to mobile, if they clicked a link that says anything else
	// we should clear the cookie.
	if ( !$browserIsMobile 
		&& array_key_exists( MOMO_THEME_SWITCHER, $_GET ) 
		&& $_COOKIE[ MOMO_THEME_SWITCHER] != $_GET[ MOMO_THEME_SWITCHER ] ){
		if ( $_GET[ MOMO_THEME_SWITCHER ] == 'mobile' ) {
			setcookie( MOMO_THEME_SWITCHER, 'mobile' , time()+60*60*24*365, '/');
		} else {
			//clear the cookie
			setcookie( MOMO_THEME_SWITCHER, '' , 0 , '/');
		}
	}
	// if we're in a mobile browser, and they have clicked a link with momo_themeSwitcher == 'standard'
	// we want to set the cookie to standard, if they clicked a link that says anything else
	// we should clear the cookie, to let the browser detection work 
	if ( $browserIsMobile 
		&& array_key_exists( MOMO_THEME_SWITCHER, $_GET ) 
		&& $_COOKIE[ MOMO_THEME_SWITCHER] != $_GET[ MOMO_THEME_SWITCHER ] ){
		if ( $_GET[ MOMO_THEME_SWITCHER ] == 'standard' ) {
			setcookie( MOMO_THEME_SWITCHER, 'standard' , time()+60*60*24*365, '/');
		} else {
			//clear the cookie
			setcookie( MOMO_THEME_SWITCHER, '' , 0 , '/');
		}
	}
	
	return $isMobile;
}

/*
*	THE FOLLOWING CODE IS FROM
*	http://svn.wp-plugins.org/wordpress-mobile-pack/trunk/plugins/wpmp_switcher/browser_detection.php
*	H/T to the wordpress mobile pack team.
*/

function browser_detection() {
  if (isset($_SERVER['HTTP_X_WAP_PROFILE']) ||
      isset($_SERVER['HTTP_PROFILE'])) {
    return true;
  }
  $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
  if (in_array(substr($user_agent, 0, 4), browser_detection_ua_prefixes())) {
    return true;
  }
  $accept = strtolower($_SERVER['HTTP_ACCEPT']);
  if (strpos($accept, 'wap') !== false) {
    return true;
  }
  if (preg_match("/(" . browser_detection_ua_contains() . ")/i", $user_agent)) {
    return true;
  }
  if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
    return true;
  }
  return false;
}

function browser_detection_ua_prefixes() {
  return array(
    'w3c ',
    'w3c-',
    'acs-',
    'alav',
    'alca',
    'amoi',
    'audi',
    'avan',
    'benq',
    'bird',
    'blac',
    'blaz',
    'brew',
    'cell',
    'cldc',
    'cmd-',
    'dang',
    'doco',
    'eric',
    'hipt',
    'htc_',
    'inno',
    'ipaq',
    'ipod',
    'jigs',
    'kddi',
    'keji',
    'leno',
    'lg-c',
    'lg-d',
    'lg-g',
    'lge-',
    'lg/u',
    'maui',
    'maxo',
    'midp',
    'mits',
    'mmef',
    'mobi',
    'mot-',
    'moto',
    'mwbp',
    'nec-',
    'newt',
    'noki',
    'palm',
    'pana',
    'pant',
    'phil',
    'play',
    'port',
    'prox',
    'qwap',
    'sage',
    'sams',
    'sany',
    'sch-',
    'sec-',
    'send',
    'seri',
    'sgh-',
    'shar',
    'sie-',
    'siem',
    'smal',
    'smar',
    'sony',
    'sph-',
    'symb',
    't-mo',
    'teli',
    'tim-',
    'tosh',
    'tsm-',
    'upg1',
    'upsi',
    'vk-v',
    'voda',
    'wap-',
    'wapa',
    'wapi',
    'wapp',
    'wapr',
    'webc',
    'winw',
    'winw',
    'xda ',
    'xda-',
  );
}

function browser_detection_ua_contains() {
  return implode("|", array(
    'android',
    'blackberry',
    'hiptop',
    'ipod',
    'lge vx',
    'midp',
    'maemo',
    'mmp',
    'netfront',
    'nintendo DS',
    'novarra',
    'openweb',
    'opera mobi',
    'opera mini',
    'palm',
    'psp',
    'phone',
    'smartphone',
    'symbian',
    'up.browser',
    'up.link',
    'wap',
    'windows ce',
	'webos',
  ));
}

?>