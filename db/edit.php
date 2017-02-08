<?php
// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no pod domain given');
($_token = $_GET['token'] ?? null) || die('no token given');
strlen($_token) > 6 || die('bad token');

// Other parameters.
$_action           = $_GET['action'] ?? '';
$_weight           = $_GET['weight'] ?? '';
$_email            = $_GET['email'] ?? '';
$_podmin_statement = $_GET['podmin_statement'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT domain,email,token,tokenexpire,weight,podmin_statement FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  $row['token'] === $_token || die('token mismatch');
  $row['tokenexpire'] >= date('Y-m-d H:i:s') || die('token expired');

  // Delete and exit.
  if ('delete' === $_action) {
    $sql    = 'DELETE FROM pods WHERE domain = $1';
    $result = pg_query_params($dbh, $sql, [$_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

    die('pod removed from DB');
  }

  // Save and exit
  if ('save' === $_action) {
    $_weight <= 10 || die('10 is max weight');

    $sql    = 'UPDATE pods SET email = $1, weight = $2, podmin_statement = $3, podmin_notify = $4 WHERE domain = $5';
    $result = pg_query_params($dbh, $sql, [$_email, $_weight, $_podmin_statement, $_podmin_notify, $_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

    $to      = $_email;
    $headers = ['From: ' . $adminemail, 'Cc: ' . $row['email'], 'Bcc: ' . $adminemail];
    $subject = 'Edit notice from poduptime';
    $message = 'Data for ' . $_domain . ' updated. If it was not you reply and let me know!';
    @mail($to, $subject, $message, implode("\r\n", $headers));

    die('Data saved. Will go into effect on next hourly change');
  }

  // Forms.
  ?>
  Authorized to edit <b><?php echo $_domain; ?></b> until <?php echo $row['tokenexpire']; ?><br>
  <form action="edit.php" method="get">
    <input type="hidden" name="domain" value="<?php echo $_domain; ?>">
    <input type="hidden" name="token" value="<?php echo $_token; ?>">
    <label>Email <input type="text" size="20" name="email" value="<?php echo $row['email']; ?>"></label><br>
    <label>Podmin Statement (You can include links to your terms and policies and information about your pod you wish to share with users.) <br><textarea cols="100" rows="7" name="podmin_statement"><?php echo $row['podmin_statement']; ?></textarea></label><br>
    <label>Weight <input type="text" size="2" name="weight" value="<?php echo $row['weight']; ?>"> This lets you weight your pod lower on the list if you have too much traffic coming in, 10 is the norm use lower to move down the list.</label><br>
    <label>Notify if pod falls to hidden status? <input type="checkbox" name="podmin_notify" <?php $row['podmin_notify'] ?? 'CHECKED' ?> ></label><br>
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
}
