<?php
require_once 'config/config.php';

try {
    $pdo->exec("ALTER TABLE posts ADD COLUMN trip_date VARCHAR(7) AFTER location;");
    echo "✅ trip_date カラムを追加しました！";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}