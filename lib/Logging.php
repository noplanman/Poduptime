<?php

/**
 * Logging class:
 * - contains lfile, lwrite and lclose public methods
 * - lfile sets path and name of log file
 * - lwrite writes message to the log file (and implicitly opens log file)
 * - lclose closes log file
 * - first call of lwrite method will open log file implicitly
 * - message is written with the following format: [d/M/Y:H:i:s] (script name) message
 * - http://www.redips.net/php/write-to-log-file/
 */

declare(strict_types=1);

namespace Poduptime;

class Logging
{
    private $fp;
    private $log_file;

    public function lfile($path): void
    {
        $this->log_file = $path;
    }

    public function lwrite($message): void
    {
        if (!\is_resource($this->fp)) {
            $this->lopen();
        }
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $time        = @date('[d/M/Y:H:i:s]');
        fwrite($this->fp, "$time ($script_name) $message" . PHP_EOL);
    }

    public function lclose(): void
    {
        fclose($this->fp);
    }

    private function lopen(): void
    {
        $log_file_default = '/tmp/logfile.txt';
        if (0 === stripos(PHP_OS, 'WIN')) {
            $log_file_default = 'c:/php/logfile.txt';
        }
        $lfile = $this->log_file ?: $log_file_default;
        $this->fp = fopen($lfile, 'ab') or exit("Can't open {$lfile}!");
    }
}
