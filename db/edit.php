<?php

use RedBeanPHP\R;

// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no pod domain given');
($_token = $_GET['token'] ?? null) || die('no token given');
strlen($_token) > 6 || die('bad token');

// Other parameters.
$_action           = $_GET['action'] ?? '';
$_weight           = $_GET['weight'] ?? 10;
$_email            = $_GET['email'] ?? '';
$_podmin_statement = $_GET['podmin_statement'] ?? '';
$_podmin_notify    = $_GET['podmin_notify'] ?? 0;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');

try {
  $pod = R::findOne('pods', 'domain = ?', [$_domain]);
  $pod || die('domain not found');
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

$pod['token'] === $_token || die('token mismatch');
$pod['tokenexpire'] >= date('Y-m-d H:i:s') || die('token expired');

// Delete and exit.
if ('delete' === $_action) {
  R::trash($pod);
  die('pod removed from DB');
}

// Save and exit.
if ('save' === $_action) {
  $_weight <= 10 || die('10 is max weight');

  try {
    $pod['email']            = $_email;
    $pod['weight']           = $_weight;
    $pod['podmin_statement'] = $_podmin_statement;
    $pod['podmin_notify']    = $_podmin_notify;
    R::store($pod);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  $to      = $_email;
  $headers = ['From: ' . $adminemail, 'Cc: ' . $pod['email'], 'Bcc: ' . $adminemail];
  $subject = 'Edit notice from poduptime';
  $message = 'Data for ' . $_domain . ' updated. If it was not you reply and let me know!';
  @mail($to, $subject, $message, implode("\r\n", $headers));

  die('Data saved. Will go into effect on next hourly change');
}

// Forms.
?>
  Authorized to edit <b><?php echo $_domain; ?></b> until <?php echo $pod['tokenexpire']; ?><br>
  <form action="edit.php" method="get">
    <input type="hidden" name="domain" value="<?php echo $_domain; ?>">
    <input type="hidden" name="token" value="<?php echo $_token; ?>">
    <label>Email <input type="text" size="20" name="email" value="<?php echo $pod['email']; ?>"></label><br>
    <label>Podmin Statement (You can use HTML to include links to your terms and policies and information about your pod you wish to share with users.) <br><textarea cols="100" rows="7" name="podmin_statement"><?php echo $pod['podmin_statement']; ?></textarea></label><br>
    <label>Weight <input type="text" size="2" name="weight" value="<?php echo $pod['weight']; ?>"> This lets you weight your pod lower on the list if you have too much traffic coming in, 10 is the norm use lower to move down the list.</label><br>
    <label>Notify if pod falls to hidden status? <input type="checkbox" name="podmin_notify" <?php $pod['podmin_notify'] === 't' ?? 'CHECKED' ?> ></label><br>
    <input type="submit" name="action" value="save">
  </form>
  <br>
  <br>
  <br>
  <form action="edit.php" method="get">
    <input type="hidden" name="domain" value="<?php echo $_domain; ?>">
    <input type="hidden" name="token" value="<?php echo $_token; ?>">
    WARNING: This can not be undone, you will need to add your pod again if you want back on list: <input type="submit" name="action" value="delete">
  </form>
<?php
