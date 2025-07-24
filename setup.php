<?php
require_once 'config/config.php';

try {
    echo "<h2>utf8mb4対応を開始...</h2>";

    // データベース全体をutf8mb4に変更
    $pdo->exec("ALTER DATABASE `tb270275db` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;");
    echo "データベースをutf8mb4に変更しました<br>";

    // 各テーブルをutf8mb4に変換
    $tables = ['users', 'posts', 'comments'];
    foreach ($tables as $table) {
        $pdo->exec("ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        echo "テーブル $table をutf8mb4に変換しました<br>";
    }

    // postsテーブルのカラムを強制utf8mb4
    $pdo->exec("ALTER TABLE posts MODIFY title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("ALTER TABLE posts MODIFY location VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("ALTER TABLE posts MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "postsテーブルのカラム(title, location, content)をutf8mb4に変更しました<br>";

    // commentsテーブルのカラムを強制utf8mb4
    $pdo->exec("ALTER TABLE comments MODIFY comment_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "commentsテーブルのカラム(comment_text)をutf8mb4に変更しました<br>";

    echo "<h2>utf8mb4対応が完全に完了しました！（絵文字OK）</h2>";

} catch (PDOException $e) {
    echo "❌ エラー発生: " . $e->getMessage();
}