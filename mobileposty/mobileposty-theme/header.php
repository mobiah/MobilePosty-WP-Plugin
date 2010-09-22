<?php
global $momo_themeStyle;

header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
header('Vary: user-agent, accept');
header('Cache-Control: no-cache, no-transform');
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head profile="http://gmpg.org/xfn/11">  
	<? wp_head(); ?>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript"> google.load("jquery", "1.3.2"); </script>
	<link href="<?php bloginfo('template_url'); ?>/css/<?=$momo_themeStyle?>.css" rel="stylesheet" type="text/css" />

  </head>
  <body>  

   