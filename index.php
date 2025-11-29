<?php
require 'config.php';
session_start();

$error = "";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter a Username and Password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect Username or Password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Milk Palace Login</title>
<style>
/* --- Dark Mode Only Styles --- */
body {
    margin: 0;
    padding: 0;
    font-family: system-ui, sans-serif;
    background: #0f0f0f;
    color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    transition: background 0.3s, color 0.3s;
}

/* Card Container */
.card {
    background: #1a1a1a;
    padding: 30px 20px;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.4);
    width: 100%;
    max-width: 360px;
    text-align: center;
    box-sizing: border-box;
}

/* Title */
.card h2 {
    margin-top: 0;
    font-size: 24px;
    color: #fff;
}

/* Input Fields */
.card input {
    width: 100%;
    padding: 14px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #333;
    background: #111;
    color: #eee;
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s, background 0.3s;
}

.card input:focus {
    outline: none;
    border-color: #6c63ff; /* purple highlight */
    background: #181818;
}

/* Login Button */
.card button {
    width: 100%;
    padding: 14px;
    margin-top: 15px;
    background: #6c63ff;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s, transform 0.2s;
}

.card button:hover {
    background: #7b73ff;
    transform: scale(1.02);
}

/* Error Message */
.error {
    color: #fca5a5;
    background: rgba(239, 68, 68, 0.1);
    padding: 8px;
    border-radius: 6px;
    margin-top: 12px;
    font-size: 14px;
}

/* Shake animation */
.shake { animation: shake 0.3s; }
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    50% { transform: translateX(6px); }
    75% { transform: translateX(-6px); }
    100% { transform: translateX(0); }
}
</style>
</head>
<body>
    <div class="card <?php if($error) echo 'shake'; ?>">
        <h2>Welcome to Milk Palace</h2>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" 
                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                   autocomplete="username">
            <input type="password" name="password" placeholder="Password" autocomplete="current-password">
            <button type="submit">Log In</button>
        </form>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>