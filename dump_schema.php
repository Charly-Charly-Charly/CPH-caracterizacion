<?php
$pdo = new PDO('sqlite:database.sqlite');
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table'");
while ($row = $stmt->fetch()) {
    echo $row['sql'] . "\n\n";
}
