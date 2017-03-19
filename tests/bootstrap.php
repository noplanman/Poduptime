<?php

// Set error reporting to the max level.
error_reporting(-1);

// Set UTC timezone.
date_default_timezone_set('UTC');

// Load testing environment variables.
(new \Dotenv\Dotenv(__DIR__))->load();

// Load internals.
require_once __DIR__ . '/../loader.php';

// For admin actions, set proper cookie.
$_COOKIE['adminkey'] = getenv('ADMIN_KEY');
