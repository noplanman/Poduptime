<?php
/**
 * Copyright (c) 2011, David Morley.
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYRIGHT file.
 */

require_once 'config.inc.php';
require_once 'db.class.php';

if (!$_POST['username']) {
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

$dbConnection = DB::connectDB();

if (!$dbConnection) {
    die("Error in connection: " . $dbConnection->errorInfo()[2]);
}

$sql = "INSERT INTO rating_comments (domain, comment, rating, username, userurl)"
    . " VALUES(".$dbConnection->quote($_POST['domain']).", ".$dbConnection->quote($_POST['comment']).", ".$dbConnection->quote($_POST['rating']). ","
    . " ".$dbConnection->quote($_POST['username']).", ".$dbConnection->quote($_POST['userurl']).")";

$result = $dbConnection->query($sql);

if (!$result) {
    die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
}

$subject = "New rating added to poduptime ";
$message = "Pod:" . $_POST["domain"] . "\n\n";
$headers = "From: ".$_POST["email"]."\r\n";
@mail( ADMIN_EMAIL, $subject, $message, $headers );

echo "Comment posted!";
?>
