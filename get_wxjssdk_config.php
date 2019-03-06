<?php


include_once 'api_ticket_util.php';

$url =  ApiTicketUtil::filter($_GET['url']);

$ret = strpos($url, '#');
if ($ret) {
    $url = substr($url, 0, $ret);
}
if (! ApiTicketUtil::checkUrl($url)) {
    echo ApiTicketUtil::jsonExit(100, "无效的URL");die();
}

// $callback = ApiTicketUtil::filter($_GET['callback']);
// if (empty($callback)) {
//     $callback = "callback";
// }
// if (! preg_match("/^[a-z0-9_A-Z]{3,100}$/", $callback)) {
//     echo "callback name error";
// }

$noncestr = ApiTicketUtil::getRandomString(10, "", true);

$gz_id = ApiTicketUtil::SANGRIA_GZID;
$app_id = ApiTicketUtil::WECHAT_APPID;

$jsapi_ticket = ApiTicketUtil::jsapi_ticket($gz_id);

$timestamp = time();
$data = array(
    "jsapi_ticket=" . $jsapi_ticket,
    "noncestr=" . $noncestr,
    "timestamp=" . $timestamp,
    "url=" . $url
);

$string1 = implode('&', $data);
$sign = sha1($string1);

// $str = "{$callback}(" . ApiTicketUtil::jsonData(0, 'success', array(
//     "appId" => $app_id,
//     "noncestr" => $noncestr,
//     "timestamp" => time(),
//     "signature" => $sign
// )) . ")";

$str = json_encode(array(
    "appId" => $app_id,
    "nonceStr" => $noncestr,
    "timestamp" => $timestamp,
    "signature" => $sign
));

echo $str;
die;
