<?php
/**
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file.
 * 
 * @todo Add mysql backup support
 * @todo Add backup to cronjob
 */

require_once 'config.inc.php';

if (BACKUP) {
	$keep = (60 * 60 * 12) * 3; 
	$dump_date = date("Ymd_Hs");
	$file_name = BACKUPDIR . "/dump_" . $dump_date . ".sql";
	
	if (DB_DRIVER == 'pgsql') {
		system("export PGPASSWORD=".DB_PASSWORD." && $pg_dump_dir/pg_dump --username=".DB_USER." ".DB_NAME." >> $file_name");
		echo "Backup of ".DB_NAME." made";
		
	}

	// Check if there are any Backups to deleted
	$dirh = dir($backup_dir);
	while($entry = $dirh->read()) {
		$old_file_time = (date("U") - $keep);
		$file_created = filectime(BACKUPDIR."/$entry");
		if ($file_created < $old_file_time && !is_dir($entry)) {
			if(unlink(BACKUPDIR."/$entry")) {
				echo "Cleaned up old backups";
			}
		}
	}
}
?>
