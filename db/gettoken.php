<?php
 include('config.php');
if (!$_POST['domain']){
  echo "no pod domain given";
 die;
}
if (!$_POST['email']){
  echo "no email given";
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
 while ($row = pg_fetch_array($result)) {
if ($row["email"] <> $_POST['email']) {
echo "email not a match";die;
}
 }
     
$uuid = md5(uniqid($domain, true));
$expire = date("Y-m-d H:i:s", time() + 700);
     $sql = "UPDATE pods SET token=$1, tokenexpire=$2 WHERE domain = '$domain'";
     $result = pg_query_params($dbh, $sql, array($uuid,$expire));
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     }
     $to = $_POST["email"];
     $subject = "Temporary edit key for podupti.me";
     $message = "Link: https://podupti.me/db/edit.php?domain=" . $_POST["domain"] . "&token=" . $uuid . " Expires: " . $expire . "\n\n";
     $headers = "From: support@diasp.org\r\nBcc: support@diasp.org\r\n";
     @mail( $to, $subject, $message, $headers );    

     echo "Link sent to your email";
     pg_free_result($result);
     pg_close($dbh);

?>
