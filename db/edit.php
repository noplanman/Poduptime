<?php
$_GET['domain'] || die('no pod domain given');
$_GET['token'] || die('no token given');
strlen($_GET['token']) < 6 || die('bad token');

$domain = $_GET['domain'];

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = "SELECT domain,email,token,tokenexpire,pingdomurl,weight FROM pods WHERE domain = '$domain'";
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  if ($row['token'] <> $_GET['token']) {
    die('token not a match');
  }
  if ($row['tokenexpire'] < date('Y-m-d H:i:s', time())) {
    die('token expired');
  }
  //delete pod
  if ($_GET['delete'] == $row['token']) {
    $sql    = "DELETE FROM pods WHERE domain = $1";
    $result = pg_query_params($dbh, $sql, [$_GET['domain']]);
    if (!$result) {
      die('Error in SQL query: ' . pg_last_error());
    } else {
      echo 'pod removed from DB';
    }
  }
  //save and exit
  if ($_GET['save'] == $row['token']) {
    if ($_GET['weight'] > 10) {
      die('10 is max weight');
    }
    $sql    = "UPDATE pods SET email=$1, pingdomurl=$2, weight=$3 WHERE domain = $4";
    $result = pg_query_params($dbh, $sql, [$_GET['email'], $_GET['pingdomurl'], $_GET['weight'], $_GET['domain']]);
    if (!$result) {
      die('Error in SQL query: ' . pg_last_error());
    }
    $to      = $_GET['email'];
    $subject = 'Edit notice from poduptime ';
    $message = 'Data for ' . $_GET['domain'] . " Updated. If it was not you reply and let me know! \n\n";
    $headers = "From: support@diasp.org\r\nCc:support@diasp.org," . $_GET['oldemail'] . "\r\n";
    @mail($to, $subject, $message, $headers);
    pg_free_result($result);
    pg_close($dbh);
    die('Data saved. Will go into effect on next hourly change');
  }

  //form     
  echo 'Authorized to edit <b>' . $domain . '</b> until ' . $row['tokenexpire'] . '<br>';
  echo '<form action="" method="get">';
  echo '<input type="hidden" name="oldemail" value="' . $row['email'] . '">';
  echo '<input type="hidden" name="save" value="' . $_GET['token'] . '">';
  echo '<input type="hidden" name="token" value="' . $_GET['token'] . '">';
  echo '<input type="hidden" name="domain" value="' . $_GET['domain'] . '">';
  echo 'Stats Key <input type="text" size="50" name="pingdomurl" value="' . $row['pingdomurl'] . '"">Uptimerobot API key for this monitor<br>';
  echo 'Email <input type="text" size="20" name="email" value="' . $row['email'] . '"><br>';
  echo 'Weight <input type="text" size="2" name="weight" value="' . $row['weight'] . '"> This lets you weight your pod lower on the list if you have too much trafic coming in, 10 is the norm use lower to move down the list.<br>';
  echo '<input type="submit" name="submit">';
  echo '</form><br><br><br>';

  echo '<form action="" method="get">';
  echo '<input type="hidden" name="delete" value="' . $_GET['token'] . '">';
  echo '<input type="hidden" name="token" value="' . $_GET['token'] . '">';
  echo '<input type="hidden" name="domain" value="' . $_GET['domain'] . '">';
  echo 'WARNING: This can not be undone, you will need to add your pod again if you want back on list: <input type="submit" name="submit" value="delete">';
  echo '</form><br><br><br>';
}
