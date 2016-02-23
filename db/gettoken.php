<?php
 include('config.php');
$systemTimeZone = system('date +%Z');
if (!$_POST['domain']){
  echo "no pod domain given";
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
     die("Error in SQL query: " . pg_last_error());
 }
$rows = pg_num_rows($result);
if ($rows <= 0) {
echo "domain not found";die;
}

 while ($row = pg_fetch_array($result)) {

if ($_POST['email']){
 if ($row["email"] <> $_POST['email']) {
  echo "email not a match";die;
 }
 
     
$uuid = md5(uniqid($domain, true));
$expire = date("Y-m-d H:i:s", time() + 2700);
     $sql = "UPDATE pods SET token=$1, tokenexpire=$2 WHERE domain = '$domain'";
     $result = pg_query_params($dbh, $sql, array($uuid,$expire));
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     }
     $to = $_POST["email"];
     $subject = "Temporary edit key for podupti.me";
     $message = "Link: https://podupti.me/db/edit.php?domain=" . $_POST["domain"] . "&token=" . $uuid . " Expires: " . $expire . " " . $systemTimeZone ."\n\n";
     $headers = "From: support@diasp.org\r\nBcc: support@diasp.org\r\n";
     @mail( $to, $subject, $message, $headers );    

     echo "Link sent to your email";

} elseif (!$_POST['email']){

$uuid = md5(uniqid($domain, true));
$expire = date("Y-m-d H:i:s", time() + 9700);
     $sql = "UPDATE pods SET token=$1, tokenexpire=$2 WHERE domain = '$domain'";
     $result = pg_query_params($dbh, $sql, array($uuid,$expire));
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     }
     $to = "support@diasp.org";
     $subject = "FORWARD REQUEST: Temporary edit key for podupti.me";
     $message = "User trying to edit pod without email address. Email found: " . $row["email"] . " Link: https://podupti.me/db/edit.php?domain=" . $_POST["domain"] . "&token=" . $uuid . " Expires: " . $expire . " " . $systemTimeZone ."\n\n";
     $headers = "From: support@diasp.org\r\nBcc: support@diasp.org\r\n";
     @mail( $to, $subject, $message, $headers );

     echo "Link sent to administrator to review and verify, if approved they will forward the edit key to you.";
}

     pg_free_result($result);
     pg_close($dbh);
}

?>
