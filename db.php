<?php
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect(YOURUNI_DB_HOST, YOURUNI_DB_USER, YOURUNI_DB_PASS, YOURUNI_DB_NAME, YOURUNI_DB_PORT);
if (!$conn) {
    http_response_code(500);
    error_log('YourUni MySQLi connection failed: ' . mysqli_connect_error());
    exit('YourUni could not connect to its database. Check MySQL is running and follow the database setup instructions in YOURUNI_README.txt.');
}
mysqli_set_charset($conn, 'utf8mb4');
