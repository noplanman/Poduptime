<?php
include('config.php');
if (!$_POST['domain']){
  echo "no pod domain given";
  die;
}
if (!$_POST['adminkey']){
  echo "no token given";
  die;
}
if (!$_POST['action']){
  echo "no action selected";
  die;
}

$domain = $_POST['domain'];
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}
$sql = "SELECT email FROM pods WHERE domain = '$domain'";
$result = pg_query($dbh, $sql);
if (!$result) {
  die("one Error in SQL query: " . pg_last_error());
}
while ($row = pg_fetch_array($result)) {
  if ($adminkey <> $_POST['adminkey']) {
    echo "admin key fail";die;
  }
  //save and exit
    if ($_POST['action'] == "delete") {
      $sql = "DELETE from pods WHERE domain = $1";
      $result = pg_query_params($dbh, $sql, array($domain));
      if (!$result) {
         die("two Error in SQL query: " . pg_last_error());
      }
      if ($row["email"]) {
        $to = $row["email"];
        $subject = "Pod deleted from poduptime ";
        $message = "Pod " . $_POST["domain"] . " was deleted from podupti.me as it was dead on the list. " . $_POST["comments"] . " Feel free to add back at any time. \n\n";
        $headers = "From: support@diasp.org\r\nCc:support@diasp.org,". $row["email"] ."\r\n";
        @mail( $to, $subject, $message, $headers );
      }
      pg_free_result($result);
      pg_close($dbh);
      } elseif ($_POST['action'] == "warn") {
      if ($row["email"]) {
        $to = $row["email"];
        $subject = "Pod removal warning from poduptime ";
        $message = "Pod " . $_POST["domain"] . " is on the list to be deleted now because:  " . $_POST["comments"] . ". \n\n Please let me know if you need help fixing before it is removed. \n\n";
        $headers = "From: support@diasp.org\r\nCc:support@diasp.org,". $row["email"] ."\r\n";
        @mail( $to, $subject, $message, $headers );
      }
  }
  echo $result;
}
?>
