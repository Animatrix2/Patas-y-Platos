<?php
$filename = 'esp32_status.txt';
$timestamp = time();
$status = "connected";

file_put_contents($filename, $status . ' ' . $timestamp);

echo "Ping received";
?>