<html><head>
  <style type="text/css">
    #slider { margin: 10px;width:250px;display:inline-block; }
    #rating { height: 30px;width:30px; }
  </style>
  <script>
  $(document).ready(function() {
  $('#addrating').click(function() {
    $('#commentform').show('slow'); $('#ratings').hide('slow');
  });
$('#submitrating').click(function() {
<?php
echo "var domain = \"{$_GET['domain']}\";";
?>
$.ajax({
   type: "POST",
   url: "db/saverating.php",
   data: "username="+$('#username').val()+"&userurl="+$('#userurl').val()+"&comment="+$('#comment').val()+"&rating="+$('#rating').val()+"&domain="+domain,
   success: function(msg){
          if (msg == 1) {
	 $("#commentform").replaceWith("<h3>Your comment was saved, Thank You!</h3>");
   } else {$('#errortext').html(msg);$('#error').slideDown(633).delay(2500).slideUp(633);} 
          }
 });
});

    $("#slider").slider({ animate: true, max: 10, min: 1, step: 1, value: 10, stop: function(event, ui) { 
var value = $( "#slider" ).slider( "option", "value" );
$("#rating").prop( "value", value )

} });
  });
  </script>
</head>
<body>
<div style="height:500px;width:900px;">
<?php
 include('db/config.php');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
 if (!$dbh) {
     die("Error in connection: " . pg_last_error());
 }  
 if ($_GET['domain']) {
 $domain = $_GET['domain'];
 $sql = "SELECT * FROM rating_comments WHERE domain = '$domain'";
 } 
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }   
 $numrows = pg_num_rows($result); 
echo "<input id='addrating' class='btn primary' style='float:right;margin-right:15px;' type='submit' value='Add a Rating'><h3>Podupti.me ratings for ".$_GET["domain"]." pod (beta feature)</h3><div id='ratings'><hr>"; 
if (!$numrows) {echo "<b>This pod has no rating yet!</b>";}
 while ($row = pg_fetch_array($result)) {
 if ($row["admin"] == 1) {
echo "Poduptime Approved Comment - User: <b>".$row["username"]."</b> Url: <a href='".$row["userurl"]."'>".$row["userurl"]."</a> Rating: <b>".$row["rating"]."</b> <br>";
echo "<i>".$row["comment"]."</i><span class='label' title='id: ".$row["id"]."' style='float:right;margin-right:115px;'>".$row["date"]."</span><hr>";
 } elseif ($row["admin"] == 0) {
echo "User Comment - User: <b>".$row["username"]."</b> Url: <a href='".$row["userurl"]."'>".$row["userurl"]."</a> Rating: <b>".$row["rating"]."</b> <br>";
echo "<i>".$row["comment"]."</i><span class='label' title='id: ".$row["id"]."' style='float:right;margin-right:115px;'>".$row["date"]."</span><hr style='margin-top:0;margin-bottom:15px;'>";
 }
}
echo <<<EOF
</div>
<div id="commentform" style="display:none">
Would you like to add a comment? (diaspora login comming)<br>
Your Name (or Diaspora handle)?<br><input id="username" name="username"><br>
Your Profile URL?<br><input id="userurl" name="userurl"><br>
Comment<br><textarea id="comment" name="comment"></textarea><br>
Rating (1-10 scale, 10 high)<br><div id="slider"></div><input class="disabled" disabled="" id="rating" name="rating" value="10">
<input class="btn primary" id="submitrating" type="submit" value="Submit your Rating">
<div class="alert-message warning" id="error" style="display:none"><span id="errortext">Some Error</span></div>
</div>
EOF;

 
 pg_free_result($result);       
 pg_close($dbh);
?>
</div>
