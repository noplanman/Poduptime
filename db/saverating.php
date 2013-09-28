<?php
 include('config.php');
if (!$_POST['username']){
  echo "no username given";
 die;
}
if (!$_POST['userurl']){
  echo "no userurl given";
 die;
}
if (!$_POST['domain']){
  echo "no pod domain given";
 die;
}
if (!$_POST['comment']){
  echo "no comment";
 die;
}
if (!$_POST['rating']){
  echo "no rating given";
 die;
}

$domain = pg_escape_string($_POST['domain']);
$comment = pg_escape_string($_POST['comment']);
$rating = pg_escape_string($_POST['rating']);
$username = pg_escape_string($_POST['username']);
$userurl = pg_escape_string($_POST['userurl']);
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
     if (!$dbh) {
         die("Error in connection: " . pg_last_error());
     }
     $sql = "INSERT INTO rating_comments (domain, comment, rating, username, userurl) VALUES('$domain', '$comment', '$rating', '$username', '$userurl')";
     $result = pg_query($dbh, $sql);
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     }
     $to = $adminemail;
     $subject = "New rating added to poduptime ";
     $message = "Pod:" . $_POST["domain"] . "\n\n";
     $headers = "From: ".$_POST["email"]."\r\n";
     @mail( $to, $subject, $message, $headers );    

     echo "Comment posted!";
     pg_free_result($result);
     pg_close($dbh);

?>
