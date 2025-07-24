<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['post_id'];

// 投稿取得（本人のみ）
$stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if ($post) {
    // 画像削除
    if ($post['image_path'] && file_exists("uploads/" . $post['image_path'])) {
        unlink("uploads/" . $post['image_path']);
    }

    // 投稿削除
    $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
}

header("Location: index.php");
exit();
?>
