<?php
$php= htmlspecialchars($_GET['id']);
$t= htmlspecialchars($_GET['ideee']);
$stream=m3u82('http://cdntvnet.com/'.$php.'.php',$t);
$streamurl=search($stream,'file:"','"});');
$response = makeRequest($streamurl);
$rawResponseHeaders = $response["headers"];
$responseBody = $response["body"];
$responseInfo = $response["responseInfo"];
$header_blacklist_pattern = "/^Content-Length|^Transfer-Encoding|^Content-Encoding.*gzip/i";
$responseHeaderBlocks = array_filter(explode("\r\n\r\n", $rawResponseHeaders));
$lastHeaderBlock = end($responseHeaderBlocks);
$headerLines = explode("\r\n", $lastHeaderBlock);
foreach ($headerLines as $header) {
  $header = trim($header);
  if (!preg_match($header_blacklist_pattern, $header)) {
    header($header);
  }
}
header('X-Robots-Tag: noindex, nofollow');
$contentType = "";
header("Content-Length: " . strlen($responseBody));
echo $responseBody;

function m3u82($url2,$ideee)
{
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
'Accept-Encoding: gzip, deflate',
'Accept-Language: es-ES,es;q=0.9,fr;q=0.8',
'Connection: keep-alive',
'Host: cdntvnet.com',
'Referer: http://hochu.tv/'.$ideee.'.html',
'Upgrade-Insecure-Requests: 1',
'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36'

    ));
$server_output = urldecode(curl_exec($ch));
curl_close ($ch);
return $server_output;

}
function search($string, $start, $end){
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);   
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}

function getHostnamePattern($hostname) {
  $escapedHostname = str_replace(".", "\.", $hostname);
  return "@^https?://([a-z0-9-]+\.)*" . $escapedHostname . "@i";
}
function removeKeys(&$assoc, $keys2remove) {
  $keys = array_keys($assoc);
  $map = array();
  foreach ($keys as $key) {
     $map[strtolower($key)] = $key;
  }
  foreach ($keys2remove as $key) {
    $key = strtolower($key);
    if (isset($map[$key])) {
       unset($assoc[$map[$key]]);
    }
  }
}
if (!function_exists("getallheaders")) {
  function getallheaders() {
    $result = array();
    foreach($_SERVER as $key => $value) {
      if (substr($key, 0, 5) == "HTTP_") {
        $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
        $result[$key] = $value;
      }
    }
    return $result;
  }
}
define("PROXY_PREFIX", "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] . ($_SERVER["SERVER_PORT"] != 80 ? ":" . $_SERVER["SERVER_PORT"] : "") . $_SERVER["SCRIPT_NAME"] . "/");
function makeRequest($url) {
  $user_agent = "vsaClient/1.0.6 (Linux;Android 5.1.1) ExoPlayerLib/1.5.14";
  if (empty($user_agent)) {
    $user_agent = "Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 4 rev: 1812 Mobile Safari/533.3";
  }
  $ch4 = curl_init();
  curl_setopt($ch4, CURLOPT_USERAGENT, $user_agent);
  $browserRequestHeaders = getallheaders();
  removeKeys($browserRequestHeaders, array(
    "Host",
    "Content-Length",
    "Accept-Encoding"
  ));
  curl_setopt($ch4, CURLOPT_ENCODING, "");
  $curlRequestHeaders = array();
  foreach ($browserRequestHeaders as $name => $value) {
    $curlRequestHeaders[$name] = $value;
  }
  curl_setopt($ch4, CURLOPT_HTTPHEADER, $curlRequestHeaders);
  switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
      curl_setopt($ch4, CURLOPT_POST, true);
      curl_setopt($ch4, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
    break;
    case "PUT":
      curl_setopt($ch4, CURLOPT_PUT, true);
      curl_setopt($ch4, CURLOPT_INFILE, fopen('php://input', 'r'));
    break;
  }
  curl_setopt($ch4, CURLOPT_HEADER, true);
  curl_setopt($ch4, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
  curl_setopt ($ch4, CURLOPT_FAILONERROR, true);
  curl_setopt($ch4, CURLOPT_URL, $url);
  $response = curl_exec($ch4);
  $responseInfo = curl_getinfo($ch4);
  $headerSize = curl_getinfo($ch4, CURLINFO_HEADER_SIZE);
  curl_close($ch4);
  $responseHeaders = substr($response, 0, $headerSize);
  $responseBody = substr($response, $headerSize);
  return array("headers" => $responseHeaders, "body" => $responseBody, "responseInfo" => $responseInfo);
}
?>
