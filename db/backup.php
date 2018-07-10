<?php

/**
 * Backup / dump PostgreSQL database.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

require_once __DIR__ . '/../config.php';

$keep      = (60 * 60 * 6) * 1;
$dump_date = date('Ymd_Hs');
$file_name = $backup_dir . '/dump_' . $dump_date . '.sql';
system("export PGPASSWORD=$pgpass && $pg_dump_dir/pg_dump --username=$pguser $pgdb >> $file_name");
echo "pg backup of $pgdb made";
$dirh = dir($backup_dir);
while ($entry = $dirh->read()) {
    $old_file_time = (date('U') - $keep);
    $file_created  = filectime( "$backup_dir/$entry");
    if ($file_created < $old_file_time && !is_dir($entry)) {
        if (unlink( "$backup_dir/$entry")) {
            echo 'Cleaned up old backups';
        }
    }
}
