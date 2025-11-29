<?php
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Determine which profile to show
$profile_id = $_GET['id'] ?? $_SESSION['user_id']; // default to self if no id in URL

// Fetch the selected user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    echo "User not found.";
    exit;
}

// Dark mode based on logged-in user
$stmt = $pdo->prepare("SELECT dark_mode FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);
$dark_mode = $me['dark_mode'] ? true : false;

// Language
$lang = $_SESSION['lang'] ?? 'en';

// Translation array
$T = [
    'en'=>[
        'profile'=>'Profile',
        'username'=>'Username',
        'rank_job'=>'Rank / Job',
        'bio'=>'Bio',
        'birthday'=>'Birthday',
        'January'=>'January','February'=>'February','March'=>'March','April'=>'April','May'=>'May','June'=>'June',
        'July'=>'July','August'=>'August','September'=>'September','October'=>'October','November'=>'November','December'=>'December'
    ],
    'th'=>[
        'profile'=>'โปรไฟล์',
        'username'=>'ชื่อผู้ใช้',
        'rank_job'=>'ตำแหน่ง / งาน',
        'bio'=>'ประวัติ',
        'birthday'=>'วันเกิด',
        'January'=>'มกราคม','February'=>'กุมภาพันธ์','March'=>'มีนาคม','April'=>'เมษายน','May'=>'พฤษภาคม','June'=>'มิถุนายน',
        'July'=>'กรกฎาคม','August'=>'สิงหาคม','September'=>'กันยายน','October'=>'ตุลาคม','November'=>'พฤศจิกายน','December'=>'ธันวาคม'
    ]
];

// Month array
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $T[$lang]['profile'] ?></title>
<style>
body { font-family:system-ui; background: <?= $dark_mode?'#121212':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; padding:20px; }
.card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.2); max-width:700px; margin:auto; text-align:center; }
img.profile-pic { width:120px; height:120px; border-radius:15px; border:2px solid <?= $dark_mode?'#555':'#ccc' ?>; margin-bottom:15px; }
p { margin:8px 0; }
button { background:#000; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; margin:5px; }
button:hover{background:#222;}
</style>
</head>
<body>
<div class="card">
<h2><?= $T[$lang]['profile'] ?></h2>
<img class="profile-pic" src="uploads/<?= htmlspecialchars($user['profile_pic'] ?? 'default.png') ?>" alt="Profile Picture">
<p><strong><?= $T[$lang]['username'] ?>:</strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong><?= $T[$lang]['rank_job'] ?>:</strong> <?= htmlspecialchars($user['rank']) ?></p>
<p><strong><?= $T[$lang]['bio'] ?>:</strong> <?= nl2br(htmlspecialchars($user['bio'])) ?></p>
<p><strong><?= $T[$lang]['birthday'] ?>:</strong> <?= $user['birth_day'] ? $user['birth_day'].' '.$T[$lang][$months[$user['birth_month']]] : '—' ?></p>

<a href="dashboard.php"><button>Dashboard</button></a>
<a href="logout.php"><button>Logout</button></a>
</div>
</body>
</html>