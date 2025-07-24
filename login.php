<?php
session_start();
require_once 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Tripnote</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap');
body { font-family: 'Roboto', sans-serif; background: #fff; color: #222; margin: 0; padding: 0; }
header { display: flex; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid #eaeaea; }
header h1 { font-size: 22px; font-weight: 400; }
nav a { margin-left: 20px; font-size: 14px; color: #333; text-decoration: none; }
main { max-width: 400px; margin: 40px auto; padding: 0 15px; }
form { background: #fff; border: 1px solid #eaeaea; border-radius: 8px; padding: 20px; }
input[type="email"], input[type="password"] {
    width: 100%; padding: 10px; margin-bottom: 15px;
    border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
}
button { background: #000; color: #fff; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #333; }
footer { text-align: center; padding: 20px; font-size: 13px; color: #888; border-top: 1px solid #eaeaea; margin-top: 40px; }
</style>
</head>
<body>
<header>
    <h1>Tripnote</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
    </nav>
</header>
<main>
<h2>Login</h2>
<?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<p><a href="register.php">Create an account</a></p>
</main>
<footer>© 2025 Tripnote | Travel & Hobby Sharing Platform</footer>
</body>
</html>
