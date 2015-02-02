<?php
 include('config.php');
if (!$_GET['domain']){
  echo "no pod domain given";
 die;
}
if (!$_GET['token']){
  echo "no token given";
 die;
}
if (strlen($_GET['token']) < 6){
  echo "bad token";
 die;
}
$domain = $_GET['domain'];
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
     if (!$dbh) {
         die("Error in connection: " . pg_last_error());
     }
 $sql = "SELECT domain,email,token,tokenexpire,pingdomurl,weight FROM pods WHERE domain = '$domain'";
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }
 while ($row = pg_fetch_array($result)) {
if ($row["token"] <> $_GET['token']) {
echo "token not a match";die;
}
if ($row["tokenexpire"] < date("Y-m-d H:i:s", time()))  {
echo "token expired";die;
}

     
echo "Authorized to edit <b>" . $domain . "</b> until " .$row["tokenexpire"] . "<br>";
echo "Stats URL <input type=text size=100 value=" .$row["pingdomurl"] . "><br>"; 
echo "Email <input type=text size=40 value=" .$row["email"] . "><br>";

echo "Weight <input type=text size=2 value=" .$row["weight"] . "> This lets you weight your pod lower on the list if you have too much trafic coming in<br>";
echo "save button goes here<br><br><br>";

echo "delete button with big warning its forever<br>";
}
if ($sfsdthis == 1) {
$expire = date("Y-m-d H:i:s", time() + 7000);
     $sql = "UPDATE pods SET token=$1, tokenexpire=$2 WHERE domain = '$domain'";
     $result = pg_query_params($dbh, $sql, array($uuid,$expire));
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     }
     $to = $_POST["email"];
     $subject = "Temporary edit key for poduptime ";
     $message = "Link: https://podupti.me/db/edit.php?domain=" . $_POST["domain"] . "&token=" . $uuid . " Expires: " . $expire . "\n\n";
     $headers = "From: support@diasp.org\r\n";
     @mail( $to, $subject, $message, $headers );    

     echo "Link sent to your email";
     pg_free_result($result);
     pg_close($dbh);
}
?>
