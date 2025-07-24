<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['post_id'] ?? null;
if (!$post_id) {
    header('Location: index.php');
    exit;
}

$countries = [
 'AF'=>'🇦🇫 Afghanistan','AL'=>'🇦🇱 Albania','DZ'=>'🇩🇿 Algeria','AD'=>'🇦🇩 Andorra','AO'=>'🇦🇴 Angola',
 'AR'=>'🇦🇷 Argentina','AM'=>'🇦🇲 Armenia','AU'=>'🇦🇺 Australia','AT'=>'🇦🇹 Austria','AZ'=>'🇦🇿 Azerbaijan',
 'BH'=>'🇧🇭 Bahrain','BD'=>'🇧🇩 Bangladesh','BE'=>'🇧🇪 Belgium','BJ'=>'🇧🇯 Benin','BT'=>'🇧🇹 Bhutan',
 'BO'=>'🇧🇴 Bolivia','BA'=>'🇧🇦 Bosnia & Herzegovina','BW'=>'🇧🇼 Botswana','BR'=>'🇧🇷 Brazil','BN'=>'🇧🇳 Brunei',
 'BG'=>'🇧🇬 Bulgaria','BF'=>'🇧🇫 Burkina Faso','BI'=>'🇧🇮 Burundi','KH'=>'🇰🇭 Cambodia','CM'=>'🇨🇲 Cameroon',
 'CA'=>'🇨🇦 Canada','CL'=>'🇨🇱 Chile','CN'=>'🇨🇳 China','CO'=>'🇨🇴 Colombia','CR'=>'🇨🇷 Costa Rica',
 'HR'=>'🇭🇷 Croatia','CU'=>'🇨🇺 Cuba','CY'=>'🇨🇾 Cyprus','CZ'=>'🇨🇿 Czech Republic','DK'=>'🇩🇰 Denmark',
 'EG'=>'🇪🇬 Egypt','EE'=>'🇪🇪 Estonia','ET'=>'🇪🇹 Ethiopia','FI'=>'🇫🇮 Finland','FR'=>'🇫🇷 France',
 'DE'=>'🇩🇪 Germany','GR'=>'🇬🇷 Greece','HU'=>'🇭🇺 Hungary','IS'=>'🇮🇸 Iceland','IN'=>'🇮🇳 India',
 'ID'=>'🇮🇩 Indonesia','IR'=>'🇮🇷 Iran','IQ'=>'🇮🇶 Iraq','IE'=>'🇮🇪 Ireland','IL'=>'🇮🇱 Israel',
 'IT'=>'🇮🇹 Italy','JP'=>'🇯🇵 Japan','JO'=>'🇯🇴 Jordan','KZ'=>'🇰🇿 Kazakhstan','KE'=>'🇰🇪 Kenya',
 'KR'=>'🇰🇷 South Korea','KW'=>'🇰🇼 Kuwait','LA'=>'🇱🇦 Laos','LV'=>'🇱🇻 Latvia','LB'=>'🇱🇧 Lebanon',
 'LY'=>'🇱🇾 Libya','LT'=>'🇱🇹 Lithuania','LU'=>'🇱🇺 Luxembourg','MY'=>'🇲🇾 Malaysia','MV'=>'🇲🇻 Maldives',
 'ML'=>'🇲🇱 Mali','MT'=>'🇲🇹 Malta','MX'=>'🇲🇽 Mexico','MA'=>'🇲🇦 Morocco','NP'=>'🇳🇵 Nepal',
 'NL'=>'🇳🇱 Netherlands','NZ'=>'🇳🇿 New Zealand','NG'=>'🇳🇬 Nigeria','NO'=>'🇳🇴 Norway','OM'=>'🇴🇲 Oman',
 'PK'=>'🇵🇰 Pakistan','PA'=>'🇵🇦 Panama','PY'=>'🇵🇾 Paraguay','PE'=>'🇵🇪 Peru','PH'=>'🇵🇭 Philippines',
 'PL'=>'🇵🇱 Poland','PT'=>'🇵🇹 Portugal','QA'=>'🇶🇦 Qatar','RO'=>'🇷🇴 Romania','RU'=>'🇷🇺 Russia',
 'SA'=>'🇸🇦 Saudi Arabia','RS'=>'🇷🇸 Serbia','SG'=>'🇸🇬 Singapore','SK'=>'🇸🇰 Slovakia','SI'=>'🇸🇮 Slovenia',
 'ZA'=>'🇿🇦 South Africa','ES'=>'🇪🇸 Spain','LK'=>'🇱🇰 Sri Lanka','SE'=>'🇸🇪 Sweden','CH'=>'🇨🇭 Switzerland',
 'SY'=>'🇸🇾 Syria','TW'=>'🇹🇼 Taiwan','TH'=>'🇹🇭 Thailand','TR'=>'🇹🇷 Turkey','UA'=>'🇺🇦 Ukraine',
 'AE'=>'🇦🇪 United Arab Emirates','GB'=>'🇬🇧 United Kingdom','US'=>'🇺🇸 United States','UY'=>'🇺🇾 Uruguay',
 'VN'=>'🇻🇳 Vietnam','YE'=>'🇾🇪 Yemen','ZM'=>'🇿🇲 Zambia','ZW'=>'🇿🇼 Zimbabwe'
];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $location = $_POST['location'] ?? '';
    $trip_date = $_POST['trip_date'] ?? '';
    $country_code = $_POST['country_code'] ?? '';
    $image_path = $post['image_path'];

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $image_path = $fileName;
        }
    }

    if ($title && $content && $country_code && $trip_date) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, location = ?, trip_date = ?, country_code = ?, content = ?, image_path = ? WHERE post_id = ?");
        try {
            $stmt->execute([$title, $location, $trip_date, $country_code, $content, $image_path, $post_id]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = "Failed to update post: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Post - Tripnote</title>
<style>
body {
    font-family: 'Roboto', sans-serif;
    font-weight: 300;
    background: #fff;
    color: #222;
    margin: 0;
}
.container {
    max-width: 600px;
    margin: 100px auto;
    padding: 20px;
    border: 1px solid #eaeaea;
    border-radius: 8px;
}
h2 {
    font-weight: 300;
    margin-bottom: 20px;
}
form label {
    display: block;
    margin-top: 15px;
    font-size: 14px;
}
input[type="text"], input[type="month"], textarea, select {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}
textarea {
    height: 100px;
}
button {
    margin-top: 20px;
    padding: 10px 20px;
    border: 1px solid #ccc;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
    border-radius: 4px;
}
button:hover {
    background: #f5f5f5;
}
.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<div class="container">
    <h2>Edit Post</h2>
    <?php if (!empty($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <label for="location">Location (City):</label>
        <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($post['location']); ?>">

        <label for="trip_date">When did you go? (Year & Month):</label>
        <input type="month" name="trip_date" id="trip_date" value="<?php echo htmlspecialchars($post['trip_date']); ?>" required>
        
        <label for="country">Select Country:</label>
        <select name="country_code" id="country" required>
            <option value="">-- Select --</option>
            <?php foreach ($countries as $code => $name): ?>
                <option value="<?php echo $code; ?>" <?php if ($code == $post['country_code']) echo 'selected'; ?>>
                    <?php echo $name; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="content">Content:</label>
        <textarea name="content" id="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

        <label for="image">Change Image (optional):</label>
        <input type="file" name="image" id="image" accept="image/*">

        <button type="submit">Update</button>
    </form>
</div>
</body>
</html>