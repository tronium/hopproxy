<?php
/*
Configuration file example (for PHPPoxyImproved proxy)

Author: Ori Novanda (cargmax-at-gmail.com)
*/

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
   header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden\n", true, 403);
   die("Error");
}

$myProto = 'http'; // "http", "https", or "auto"
$myHost = $_SERVER['HTTP_HOST'];
$myPage = $_SERVER['SCRIPT_NAME'];

$pxProto = 'http';
$pxHost = $_SERVER['HTTP_HOST'];
$pxPage = '/phpproxyimproved/index.php';

$replaceHeader = array (
		"$pxProto://$pxHost$pxPage" => "$myProto://$myHost$myPage" // redirect
		,"$pxProto://$pxHost" => "?HP=" // redirect (other page)
		,$pxHost => $myHost// cookie
	);

$replaceContent = array (
		"$pxProto://$pxHost$pxPage" => "" // shorter link
		,"$pxProto://$pxHost" . dirname($pxPage) . "/" => "$myProto://$myHost" . dirname($myPage)  . "/" // proxy's parent page
		,"$pxProto://$pxHost" => "?HP=" // proxy's link
		,"action=\"$pxPage" => "action=\"$myPage" // initial address form
		,"href=\"include/style.css\"" => "href=\"?HP=include/style.css\"" // proxy's css
	);

$extraForwardHeader = "X-Forwarded-By: HopProxy";
$extraReturnHeader = "X-Powered-By: HopProxy";

$excludedHeader = "X-Forwarded-For";
$excludedHeaderEcho = "X-Forwarded-From";

include_once "hopproxy.lib.php";
?>