<?php
// includes/db.php
// Database configuration — change these to match your server
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'arts_store');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#e74c3c;">Database Connection Failed</h2>
        <p>Please check your database settings in <code>includes/db.php</code></p>
        <p style="color:#888;font-size:13px;">' . $conn->connect_error . '</p>
    </div>');
}

$conn->set_charset("utf8");

// Helper: sanitize input
function clean($conn, $str) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($str))));
}
?>
