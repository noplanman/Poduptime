<?php

if (php_sapi_name() == "cli") {
  $json = json_decode(file_get_contents('https://the-federation.info/pods.json'));
  if ($json) {
    foreach ($json->pods ?? [] as $poddata) {
      echo exec("php-cgi add.php domain={$poddata->host}") . "\r\n";
    }
  }
} else {
header('HTTP/1.0 403 Forbidden');
}
