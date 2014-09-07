<?php
/*
The main workhorse

Author: Ori Novanda (cargmax-at-gmail.com)

Usage: Multiple proxies with different configurations can be provided by including this file in each proxy configuration file.
*/

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden\n", true, 403);
	die("Error");
}

function prepareHeader() {
	global $myUrl, $pxUrl, $excludedHeader, $excludedHeaderEcho;

	$h0 = array();
	$excludedHeader = strtolower($excludedHeader);
	foreach(getallheaders() as $k0=>$v) {
		$k = strtolower($k0);
		if($k == 'host') continue;
		else if($k == "connection") continue;
		else if($k == "referer") {
			$v = str_replace($myUrl, $pxUrl, $v);
		}
		else if($k == $excludedHeader) {
			if(!empty($excludedHeaderEcho)) {
				header("$excludedHeaderEcho: $v\n");
				continue;
			}
		}
		$h0[$k0] = $v;
	}
	$h0['Connection'] = 'close'; // force close

	return $h0;
}

function createStreamContext($method, array $data = null, array $headers = null) {
	global $extraForwardHeader;

	$proto = 'http';

	$params = array(
		$proto => array(
			'method' => $method
			,'follow_location' => 0 // don't follow redirection
		)
	);

	if ($method =="POST") {
		$params[$proto]['content'] = http_build_query($data);
	}

	$params[$proto]['header'] = '';
	if (!is_null($headers)) {
		foreach ($headers as $k => $v) {
			$params[$proto]['header'] .= "$k: $v\n";
		}
	}
	if(!empty($extraForwardHeader)) $params[$proto]['header'] .= "$extraForwardHeader\n";

	return stream_context_create($params);
}

if($myProto == "auto") {
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $myProto = 'https';
	else $myProto = 'http';
}

$myUrl = "$myProto://$myHost$myPage";
$pxUrl = "$pxProto://$pxHost$pxPage";

if(!empty($extraReturnHeader)) header($extraReturnHeader . "\n", false);

$h0 = prepareHeader();
$ctx = createStreamContext($_SERVER['REQUEST_METHOD'], $_POST, $h0);
$HP = $_GET['HP'];
if(empty($HP)) {
	if(substr($_SERVER['REQUEST_URI'], 0, strlen($myPage)) == $myPage) {
		$url = $pxUrl . substr($_SERVER['REQUEST_URI'], strlen($myPage));
	}
	else {
		$url = $pxUrl . substr($_SERVER['REQUEST_URI'], strlen(dirname($myPage))+1);
	}
}
else {
	if(substr($HP,0,strlen($myPage)) == $myPage) {
		$url = "$pxUrl" . substr($HP, strlen($myPage));
	}
	else {
		$url = "$pxProto://$pxHost";
		if (substr($HP,0,1) == '/') $url .= $HP;
		else $url .= dirname($pxPage) . "/$HP";
	}
}
$fp = @fopen($url, 'rb', false, $ctx);

if ($fp) {
	$zip = 0;
	foreach($http_response_header as $v) {
		if($v == "Content-Encoding: gzip") {$zip = 1;}
		foreach($replaceHeader as $from=>$to) {
			$v = str_replace($from,$to, $v);
		}
		header($v . "\n", false);
	}

	$r = @stream_get_contents($fp);

	if($zip == 1) $r = gzdecode($r);
	foreach($replaceContent as $from=>$to) {
		$r = str_replace($from,$to, $r);
	}
	if($zip == 1) $r = gzencode($r);
	header("Content-Length: " . strlen($r) . "\n");

	echo $r;
	fclose($fp);
}
else {
	print("Error loading remote page");
}
?>