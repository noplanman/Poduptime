<?php

/**
 * Check status of previously pulled data.
 */

declare(strict_types=1);

$dur = (time() - filemtime('last.data'));
echo $dur;
if ($dur > 4500) {
    http_response_code(500);
} else {
    http_response_code(200);
} 
