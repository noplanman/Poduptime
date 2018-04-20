<?php

$json = json_decode(file_get_contents('https://the-federation.info/pods.json'));
foreach($json->pods as $poddata) {
  echo exec("php-cgi db/add.php domain={$poddata->host}") . "\r\n";
}
