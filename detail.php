<?php
session_start();
require_once 'config/config.php';

$post_id = $_GET['post_id'] ?? null;
if (!$post_id) {
    header('Location: index.php');
    exit;
}

// 投稿データ取得
$stmt = $pdo->prepare("SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.user_id WHERE post_id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header('Location: index.php');
    exit;
}

// コメント一覧取得
$stmt = $pdo->prepare("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id = users.user_id WHERE post_id = ? ORDER BY created_at ASC");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// コメント投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment_text = $_POST['comment_text'] ?? '';
    if ($comment_text) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $comment_text]);
        header("Location: detail.php?post_id=" . $post_id);
        exit;
    }
}

// 国旗データ（主要国＋世界対応）
$flags = [
 'AF'=>'🇦🇫','AL'=>'🇦🇱','DZ'=>'🇩🇿','AD'=>'🇦🇩','AO'=>'🇦🇴','AR'=>'🇦🇷','AM'=>'🇦🇲','AU'=>'🇦🇺','AT'=>'🇦🇹','AZ'=>'🇦🇿',
 'BH'=>'🇧🇭','BD'=>'🇧🇩','BE'=>'🇧🇪','BJ'=>'🇧🇯','BT'=>'🇧🇹','BO'=>'🇧🇴','BA'=>'🇧🇦','BW'=>'🇧🇼','BR'=>'🇧🇷','BN'=>'🇧🇳',
 'BG'=>'🇧🇬','BF'=>'🇧🇫','BI'=>'🇧🇮','KH'=>'🇰🇭','CM'=>'🇨🇲','CA'=>'🇨🇦','CL'=>'🇨🇱','CN'=>'🇨🇳','CO'=>'🇨🇴','CR'=>'🇨🇷',
 'HR'=>'🇭🇷','CU'=>'🇨🇺','CY'=>'🇨🇾','CZ'=>'🇨🇿','DK'=>'🇩🇰','EG'=>'🇪🇬','EE'=>'🇪🇪','ET'=>'🇪🇹','FI'=>'🇫🇮','FR'=>'🇫🇷',
 'DE'=>'🇩🇪','GR'=>'🇬🇷','HU'=>'🇭🇺','IS'=>'🇮🇸','IN'=>'🇮🇳','ID'=>'🇮🇩','IR'=>'🇮🇷','IQ'=>'🇮🇶','IE'=>'🇮🇪','IL'=>'🇮🇱',
 'IT'=>'🇮🇹','JP'=>'🇯🇵','JO'=>'🇯🇴','KZ'=>'🇰🇿','KE'=>'🇰🇪','KR'=>'🇰🇷','KW'=>'🇰🇼','LA'=>'🇱🇦','LV'=>'🇱🇻','LB'=>'🇱🇧',
 'LY'=>'🇱🇾','LT'=>'🇱🇹','LU'=>'🇱🇺','MY'=>'🇲🇾','MV'=>'🇲🇻','ML'=>'🇲🇱','MT'=>'🇲🇹','MX'=>'🇲🇽','MA'=>'🇲🇦','NP'=>'🇳🇵',
 'NL'=>'🇳🇱','NZ'=>'🇳🇿','NG'=>'🇳🇬','NO'=>'🇳🇴','OM'=>'🇴🇲','PK'=>'🇵🇰','PA'=>'🇵🇦','PY'=>'🇵🇾','PE'=>'🇵🇪','PH'=>'🇵🇭',
 'PL'=>'🇵🇱','PT'=>'🇵🇹','QA'=>'🇶🇦','RO'=>'🇷🇴','RU'=>'🇷🇺','SA'=>'🇸🇦','RS'=>'🇷🇸','SG'=>'🇸🇬','SK'=>'🇸🇰','SI'=>'🇸🇮',
 'ZA'=>'🇿🇦','ES'=>'🇪🇸','LK'=>'🇱🇰','SE'=>'🇸🇪','CH'=>'🇨🇭','SY'=>'🇸🇾','TW'=>'🇹🇼','TH'=>'🇹🇭','TR'=>'🇹🇷','UA'=>'🇺🇦',
 'AE'=>'🇦🇪','GB'=>'🇬🇧','US'=>'🇺🇸','UY'=>'🇺🇾','VN'=>'🇻🇳','YE'=>'🇾🇪','ZM'=>'🇿🇲','ZW'=>'🇿🇼'
];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($post['title']); ?> - Tripnote</title>
<style>
body {
    font-family: 'Roboto', sans-serif;
    font-weight: 300;
    background: #fff;
    color: #222;
    margin: 0;
}
.container {
    max-width: 800px;
    margin: 100px auto;
    padding: 20px;
}
h2 {
    font-weight: 400;
    font-size: 24px;
    margin-bottom: 10px;
}
.post-meta {
    font-size: 14px;
    color: #555;
    margin-bottom: 20px;
}
.post-image img {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 20px;
}
.post-content {
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 30px;
}
.comment-section {
    margin-top: 40px;
}
.comment {
    border-top: 1px solid #eee;
    padding: 10px 0;
}
.comment strong {
    font-weight: 500;
}
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 10px;
}
button {
    margin-top: 10px;
    padding: 8px 16px;
    border: 1px solid #ccc;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
    border-radius: 4px;
}
button:hover {
    background: #f5f5f5;
}
</style>
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
    <p class="post-meta">
        <strong>Visited:</strong> <?php echo htmlspecialchars($post['trip_date']); ?><br>
        <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?>
        <?php echo $flags[$post['country_code']] ?? ''; ?><br>
        <strong>By:</strong> <?php echo htmlspecialchars($post['name']); ?>
    </p>

    <?php if ($post['image_path']): ?>
        <div class="post-image">
            <img src="uploads/<?php echo htmlspecialchars($post['image_path']); ?>">
        </div>
    <?php endif; ?>

    <div class="post-content">
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>

    <div class="comment-section">
        <h3>Comments</h3>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($comment['name']); ?>:</strong>
                <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST">
                <textarea name="comment_text" placeholder="Add a comment..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Login</a> to comment</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>