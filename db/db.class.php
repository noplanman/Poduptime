<?php

/**
 * Copyright (c) 2011, Johannes Brunswicker.
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYRIGHT file.
 */

require_once "config.inc.php";

/**
 * Connects to database and returns the PDO Object or false
 * @author J. Brunswicker
 * @return Ambigous <boolean, PDO>
 *
 */
class DB {
	
	/**
	 * Connects to the DB
	 * @return PDO|boolean
	 */
	public static function connectDB() { 
		$dsn = DB_DRIVER.":dbname=".DB_NAME.";host=".DB_HOST;
	
		if (DB_DRIVER == 'mysql') {
			$dsn .= ";charset=UTF8";
		}
	
		try {
			$connection = new PDO($dsn, DB_USER, DB_PASSWORD);
			return $connection;
		} catch (PDOException $e) {
			if (DEBUG) {
				echo ("User: ".DB_USER."<br />");
				echo ('Connection to database with dsn '.$dsn.' failed: ' . $e->getMessage().'<br />');
			}
			return false;
		}
	}
	
}
?>