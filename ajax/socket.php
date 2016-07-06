<?php
$fp = fsockopen('shopupdater.mybet.com', 6868);

$content = "shopId=123|posId=432";

fwrite($fp, "POST /reposter.php HTTP/1.1\r\n");
fwrite($fp, "Host: example.com\r\n");
fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
fwrite($fp, "Content-Length: ".strlen($content)."\r\n");
fwrite($fp, "Connection: close\r\n");
fwrite($fp, "\r\n");

fwrite($fp, $content);

header('Content-type: text/plain');
while (!feof($fp)) {
    echo fgets($fp, 1024);
}

?>