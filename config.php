<?php
// YourUni database configuration. For hosting, set the YOURUNI_DB_* environment variables.
// Local XAMPP defaults are intentionally development-only.
if (!defined('YOURUNI_DB_HOST')) define('YOURUNI_DB_HOST', getenv('YOURUNI_DB_HOST') ?: 'localhost');
if (!defined('YOURUNI_DB_PORT')) define('YOURUNI_DB_PORT', (int)(getenv('YOURUNI_DB_PORT') ?: 3306));
if (!defined('YOURUNI_DB_NAME')) define('YOURUNI_DB_NAME', getenv('YOURUNI_DB_NAME') ?: 'youruni_db');
if (!defined('YOURUNI_DB_USER')) define('YOURUNI_DB_USER', getenv('YOURUNI_DB_USER') ?: 'root');
if (!defined('YOURUNI_DB_PASS')) define('YOURUNI_DB_PASS', getenv('YOURUNI_DB_PASS') !== false ? getenv('YOURUNI_DB_PASS') : '');

// On public hosting, set this one-time key before creating the first admin account.
if (!defined('YOURUNI_SETUP_KEY')) define('YOURUNI_SETUP_KEY', getenv('YOURUNI_SETUP_KEY') ?: '');
