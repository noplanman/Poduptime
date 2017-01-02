<?php
// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no pod domain given');
($_token = $_GET['token'] ?? null) || die('no token given');
strlen($_token) > 6 || die('bad token');

// Other parameters.
$_save       = $_GET['save'] ?? '';
$_delete     = $_GET['delete'] ?? '';
$_weight     = $_GET['weight'] ?? '';
$_email      = $_GET['email'] ?? '';
$_oldemail   = $_GET['oldemail'] ?? '';
$_pingdomurl = $_GET['pingdomurl'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT domain,email,token,tokenexpire,pingdomurl,weight FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  $row['token'] === $_token || die('token not a match');
  $row['tokenexpire'] >= date('Y-m-d H:i:s', time()) || die('token expired');

  //delete pod
  if ($_delete === $row['token']) {
    $sql    = 'DELETE FROM pods WHERE domain = $1';
    $result = pg_query_params($dbh, $sql, [$_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

    die('pod removed from DB');
  }

  //save and exit
  if ($_save === $row['token']) {
    $_weight <= 10 || die('10 is max weight');

    $sql    = 'UPDATE pods SET email = $1, pingdomurl = $2, weight = $3 WHERE domain = $4';
    $result = pg_query_params($dbh, $sql, [$_email, $_pingdomurl, $_weight, $_domain]);
    if (!$result) {
      die('Error in SQL query: ' . pg_last_error());
    }
    $to      = $_email;
    $subject = 'Edit notice from poduptime ';
    $message = 'Data for ' . $_domain . " Updated. If it was not you reply and let me know! \n\n";
    $headers = "From: support@diasp.org\r\nCc:support@diasp.org," . $_oldemail . "\r\n";
    @mail($to, $subject, $message, $headers);
    pg_free_result($result);
    pg_close($dbh);
    die('Data saved. Will go into effect on next hourly change');
  }

  //form     
  echo 'Authorized to edit <b>' . $_domain . '</b> until ' . $row['tokenexpire'] . '<br>';
  echo '<form action="" method="get">';
  echo '<input type="hidden" name="oldemail" value="' . $row['email'] . '">';
  echo '<input type="hidden" name="save" value="' . $_token . '">';
  echo '<input type="hidden" name="token" value="' . $_token . '">';
  echo '<input type="hidden" name="domain" value="' . $_domain . '">';
  echo 'Stats Key <input type="text" size="50" name="pingdomurl" value="' . $row['pingdomurl'] . '"">Uptimerobot API key for this monitor<br>';
  echo 'Email <input type="text" size="20" name="email" value="' . $row['email'] . '"><br>';
  echo 'Weight <input type="text" size="2" name="weight" value="' . $row['weight'] . '"> This lets you weight your pod lower on the list if you have too much trafic coming in, 10 is the norm use lower to move down the list.<br>';
  echo '<input type="submit" name="submit">';
  echo '</form><br><br><br>';

  echo '<form action="" method="get">';
  echo '<input type="hidden" name="delete" value="' . $_token . '">';
  echo '<input type="hidden" name="token" value="' . $_token . '">';
  echo '<input type="hidden" name="domain" value="' . $_domain . '">';
  echo 'WARNING: This can not be undone, you will need to add your pod again if you want back on list: <input type="submit" name="submit" value="delete">';
  echo '</form><br><br><br>';
}
