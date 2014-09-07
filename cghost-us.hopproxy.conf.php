<?php
/*
Configuration file example (for cyberghostvpn's proxy)

Author: Ori Novanda (cargmax-at-gmail.com)
*/

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden\n", true, 403);
	die("Error");
}

$myProto = 'http'; // "http", "https", or "auto"
$myHost = $_SERVER['HTTP_HOST'];
$myPage = $_SERVER['SCRIPT_NAME'];

$pxProto = 'https';
$pxHost = 'us-free-proxy.cyberghostvpn.com';
$pxPage = '/go/browse.php';

$replaceHeader = array (
		"$pxProto://$pxHost$pxPage" => "$myProto://$myHost$myPage" // redirect
		,"$pxProto://$pxHost" => "?HP=" // redirect (other page)
		,$pxHost => $myHost// cookie
	);

$replaceContent = array (
		"/go/browse.php?" => "?" // proxied link
		,"$pxProto://$pxHost" => "?HP=" // proxy's menu
		," action=\"includes/process.php?" => " action=\"?HP=includes/process.php?" // initial address form
		,"themes/cg/" => "?HP=themes/cg/" // proxy's asset
	);

$extraForwardHeader = "X-Forwarded-By: HopProxy";
$extraReturnHeader = "X-Powered-By: HopProxy";

$excludedHeader = "";
$excludedHeaderEcho = "";

include_once "hopproxy.lib.php";
?>