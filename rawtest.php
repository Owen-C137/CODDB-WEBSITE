<?php
header('Content-Type: text/plain');
echo "Raw test successful";

// Log IP and user-agent
file_put_contents(
    "access_log.txt",
    date('c') . " | " . ($_SERVER['REMOTE_ADDR'] ?? '-') . " | " . ($_SERVER['HTTP_USER_AGENT'] ?? '-') . "\n",
    FILE_APPEND
);