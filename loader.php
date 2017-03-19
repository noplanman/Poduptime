<?php

define('PODUPTIME', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

function unitTesting()
{
  return defined('PHPUNIT_TEST') && PHPUNIT_TEST === true;
}

if (!unitTesting()) {
  $e = new \Dotenv\Dotenv(__DIR__);
  $e->overload();
  $e->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
  $e->required('DB_PASS');

  // Load any custom config.
  if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
  }
}

// Set up global DB connection.
\RedBeanPHP\R::setup(sprintf(
  'pgsql:host=%s;dbname=%s;',
  getenv('DB_HOST'),
  getenv('DB_NAME')),
  getenv('DB_USER'),
  getenv('DB_PASS'),
  true
);

if (!\RedBeanPHP\R::testConnection()) {
  throw new \RedBeanPHP\RedException('Error in DB connection');
}
