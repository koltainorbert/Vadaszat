<?php
$conn = new mysqli('localhost', 'root', 'root', 'local');
$conn->set_charset('utf8mb4');
$res = $conn->query("SELECT option_name, option_value FROM wp_options WHERE option_name LIKE 'va_%' ORDER BY option_name");
while ($row = $res->fetch_assoc()) {
    echo $row['option_name'] . ' = ' . substr($row['option_value'], 0, 300) . "\n";
}
