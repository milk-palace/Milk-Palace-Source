<?php
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Set default language
if(!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
$lang = $_SESSION['lang'];

// Translation array
$trans = [
    'en'=>[
        'users_page'=>'Users',
        'dashboard'=>'Dashboard',
        'search'=>'Search users...'
    ],
    'th'=>[
        'users_page'=>'ผู้ใช้',
        'dashboard'=>'แผงควบคุม',
        'search'=>'ค้นหาผู้ใช้...'
    ]
];

// Fetch all public users (privacy=0)
$stmt = $pdo->query("SELECT * FROM users WHERE privacy=0 ORDER BY id ASC");
$public_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current user for dark mode
$stmt = $pdo->prepare("SELECT dark_mode FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);
$dark_mode = $current_user['dark_mode'] ? true : false;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $trans[$lang]['users_page'] ?></title>
<style>
body {
    background: <?= $dark_mode?'#121212':'#fff' ?>;
    color: <?= $dark_mode?'#eee':'#000' ?>;
    font-family: system-ui;
    padding:20px;
}
.card {
    background: <?= $dark_mode?'#1e1e1e':'#fff' ?>;
    border-radius:12px;
    padding:20px;
    box-shadow:0 6px 20px rgba(0,0,0,0.2);
    max-width:1000px;
    margin:auto;
    margin-bottom:20px;
}
h2 {
    margin-bottom:15px;
}
.search-input {
    width:100%;
    max-width:400px;
    padding:8px 12px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid <?= $dark_mode?'#333':'#ccc' ?>;
    background: <?= $dark_mode?'#121212':'#f9f9f9' ?>;
    color: <?= $dark_mode?'#eee':'#000' ?>;
    font-size:14px;
}
.user-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap:15px;
    max-height:500px;
    overflow-y:auto;
    padding:10px;
    border:1px solid <?= $dark_mode?'#333':'#ccc' ?>;
    border-radius:8px;
}
.user-grid::-webkit-scrollbar { width:6px; }
.user-grid::-webkit-scrollbar-thumb { background:<?= $dark_mode?'#555':'#ccc' ?>; border-radius:3px; }

.user-card {
    text-align:center;
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius:10px;
    padding:5px;
}
.user-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.user-card img {
    width:60px;
    height:60px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #ccc;
    display:block;
    margin:auto;
}
.user-card span {
    display:block;
    margin-top:5px;
    font-weight:bold;
    color:#fff;
    text-decoration:none; /* No underline */
}
a {
    text-decoration:none;
}
.dashboard-btn {
    display:block;
    margin:30px auto 0 auto;
    padding:12px 20px;
    background:#000;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    text-align:center;
    max-width:200px;
}
.dashboard-btn:hover { background:#222; }
</style>
</head>
<body>

<div class="card">
    <h2><?= $trans[$lang]['users_page'] ?></h2>
    <input type="text" id="searchInput" class="search-input" placeholder="<?= $trans[$lang]['search'] ?>">

    <div class="user-grid" id="userGrid">
        <?php foreach($public_users as $u): ?>
        <div class="user-card">
            <a href="profile.php?id=<?= $u['id'] ?>" style="color:<?= htmlspecialchars($u['color']) ?>;">
                <img src="uploads/<?= htmlspecialchars($u['profile_pic']) ?>" alt="<?= htmlspecialchars($u['username']) ?>">
                <span style="color:<?= htmlspecialchars($u['color']) ?>; text-decoration:none;"><?= htmlspecialchars($u['username']) ?></span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
<!-- Dashboard Button -->
    <a href="dashboard.php"><button class="dashboard-btn"><?= $trans[$lang]['dashboard'] ?></button></a>
</div>

<script>
// Live search filter
const searchInput = document.getElementById('searchInput');
const userGrid = document.getElementById('userGrid');
const userCards = userGrid.getElementsByClassName('user-card');

searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    Array.from(userCards).forEach(card => {
        const username = card.querySelector('span').textContent.toLowerCase();
        if(username.includes(filter)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>