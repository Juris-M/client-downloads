<?php
require __DIR__ . '/lib/bootstrap.inc.php';


if (empty($_GET['platform'])) {
	http_response_code(400);
	exit;
}

$cv = new \Zotero\ClientDownloads([
	'manifestsDir' => ROOT_DIR . "/manifests"
]);

$platform = $_GET['platform'];
$channel = !empty($_GET['channel']) ? $_GET['channel'] : 'release';
$from = !empty($_GET['from']) ? $_GET['from'] : null;
$version = !empty($_GET['version']) ? $_GET['version'] : null;

switch ($channel) {
case 'release':
case 'beta':
case 'dev':
	break;
default:
	http_response_code(400);
	exit;
}

if ($version) {
        error_log($version);
	if (!preg_match('/[\d]+\.[\d]+\.[\d]+m[\d]+/', $version)) {
		http_response_code(400);
		exit;
	}
}
else {
	$build = $cv->getBuildOverride($platform, $from, false);
	if ($build) {
		$version = $build['version'];
	}
	if (!isset($version)) {
		$version = $cv->getBuildVersion($channel, $platform);
	}
}

if (!$version) {
	http_response_code(400);
	exit;
}

switch ($platform) {
case 'mac':
	$filename = "Jurism-$version.dmg";
	break;

case 'linux-i686':
case 'linux-x86_64':
	$filename = "Jurism-{$version}_$platform.tar.bz2";
	break;

case 'win32':
	$filename = "Jurism-{$version}_setup.exe";
	break;

case 'win32-zip':
	$filename = "Jurism-{$version}_win32.zip";
	break;

default:
	http_response_code(400);
	exit;
}

if (!empty($_GET['fn'])) {
	echo $filename;
	exit;
}

$version = urlencode($version);
$filename = urlencode($filename);

if (isset($statsd)) {
	$statsd->increment("downloads.client.$channel.$platform");
}
header("Location: $HOST/client/$channel/$version/$filename");
