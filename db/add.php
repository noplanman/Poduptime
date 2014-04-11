<?php
/**
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file. 
 */

require_once 'config.inc.php';
require_once 'db.class.php';
require_once 'pull.class.php';

$valid = 0;
if (! $_POST ['url']) {
	echo "no url given";
	die ();
}
if (! $_POST ['email']) {
	echo "no email given";
	die ();
}
if (! $_POST ['domain']) {
	echo "no pod domain given";
	die ();
}
if (! $_POST ['url']) {
	echo "no API key for your stats";
	die ();
}
if (strlen ( $_POST ['url'] ) < 14) {
	echo "API key bad needs to be like m58978-80abdb799f6ccf15e3e3787ee";
	die ();
}

$dbConnection = DB::connectDB();
if (!$dbConnection) {
	die ( "Error in connection: " . $dbConnection->errorInfo()[2] );
}
$sql = "SELECT domain,pingdomurl FROM pods";

$result = $dbConnection->query($sql);
if (! $result) {
	die ( "Error in SQL query: " . $dbConnection->errorInfo()[2] );
}

foreach ($result->fetchAll() as $row) {
	if ($row ["domain"] == $_POST ['domain']) {
		echo "domain already exists";
		die ();
	}
	if ($row ["pingdomurl"] == $_POST ['url']) {
		echo "API key already exists";
		die ();
	}
}

// curl the header of pod with and without https
$outputssl = Pull::getCurlResult("https://" . $_POST ['domain'] . "/users/sign_in");
$output = Pull::getCurlResult("http://" . $_POST ['domain'] . "/users/sign_in");

if (stristr ( $outputssl, 'Set-Cookie: _diaspora_session=' )) {
	echo "Your pod has ssl and is valid<br>";
	$valid = 1;
}
if (stristr ( $output, 'Set-Cookie: _diaspora_session=' )) {
	echo "Your pod does not have ssl but is a valid pod<br>";
	$valid = 1;
}

if ($valid == "1") {
	$sql = "INSERT INTO pods (domain, pingdomurl, email) VALUES(".$dbConnection->quote($_POST['domain']).", ".$dbConnection->quote($_POST['url']).", ".$dbConnection->quote($_POST['email']).")";
	$result = $dbConnection->query($sql);
	if (! $result) {
		die ( "Error in SQL query: " . $dbConnection->errorInfo()[2]);
	}
	$subject = "New pod added to poduptime ";
	$message = "http://podupti.me\n\n Pingdom Url:" . $_POST ["url"] . "\n\n Pod:" . $_POST ["domain"] . "\n\n";
	$headers = "From: " . $_POST ["email"] . "\r\nReply-To: " . $_POST ["email"] . "\r\n";
	@mail ( ADMIN_EMAIL, $subject, $message, $headers );
	
	echo "Data successfully inserted! Your pod will be reviewed and live on the list soon! You will get a support ticket, no need to do anything if your pod is listed in the next few hours.";
	
} else {
	echo "Could not validate your pod on http or https, check your setup!";
}

?>
