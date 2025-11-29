<?php
header('Content-Type: text/html; charset=utf-8');
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Language setup
if(!isset($_SESSION['lang'])) $_SESSION['lang']='en';
$lang = $_SESSION['lang'];

// Translation array
$trans = [
  'en'=>[
      'dashboard'=>'Dashboard',
      'leaderboard'=>'Leaderboard',
      'edit_profile'=>'Edit Profile',
      'logout'=>'Logout',
      'my_profile'=>'My Current Profile',
      'settings'=>'Settings',
      'privacy'=>'Privacy',
      'dark_mode'=>'Dark Mode',
      'language'=>'Language',
      'quick_links'=>'Quick Links',
      'help'=>'Help',
      'reservations'=>'Get Reservations',
      'information'=>'Information',
      'news'=>'News',
      'admin_panel'=>'Access Admin Panel',
      'stats'=>'Stats',
      'total_users'=>'Total Users',
      'last_added_user'=>'Last Added User',
      'users_row'=>'Users →',
      'give_feedback'=>'Give Feedback'
  ],
  'th'=>[
      'dashboard'=>'แผงควบคุม',
      'leaderboard'=>'กระดานผู้นำ',
      'edit_profile'=>'แก้ไขโปรไฟล์',
      'logout'=>'ออกจากระบบ',
      'my_profile'=>'โปรไฟล์ของฉัน',
      'settings'=>'การตั้งค่า',
      'privacy'=>'ความเป็นส่วนตัว',
      'dark_mode'=>'โหมดมืด',
      'language'=>'ภาษา',
      'quick_links'=>'ลิงก์ด่วน',
      'help'=>'ความช่วยเหลือ',
      'reservations'=>'จองบริการ',
      'information'=>'ข้อมูล',
      'news'=>'ข่าวสาร',
      'admin_panel'=>'แผงควบคุมผู้ดูแลระบบ',
      'stats'=>'สถิติ',
      'total_users'=>'จำนวนผู้ใช้ทั้งหมด',
      'last_added_user'=>'ผู้ใช้ล่าสุด',
      'users_row'=>'ผู้ใช้ →',
      'give_feedback'=>'ให้ข้อเสนอแนะ'
  ]
];

// Handle toggles
if(isset($_POST['privacy_toggle'])){
    $privacy = isset($_POST['privacy']) ? 1 : 0;
    $stmt=$pdo->prepare("UPDATE users SET privacy=? WHERE id=?");
    $stmt->execute([$privacy,$_SESSION['user_id']]);
    header("Location: dashboard.php"); exit;
}
if(isset($_POST['dark_mode_toggle'])){
    $dark = isset($_POST['dark_mode']) ? 1 : 0;
    $stmt=$pdo->prepare("UPDATE users SET dark_mode=? WHERE id=?");
    $stmt->execute([$dark,$_SESSION['user_id']]);
    header("Location: dashboard.php"); exit;
}
if(isset($_POST['language'])){
    $_SESSION['lang']=$_POST['language'];
    $lang=$_POST['language'];
}

// Fetch current user
$stmt=$pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$current_user=$stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all users for leaderboard and stats
$stmt=$pdo->query("SELECT * FROM users ORDER BY id ASC");
$users=$stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 5 public users for Users row
$stmt=$pdo->query("SELECT * FROM users WHERE privacy=0 ORDER BY id ASC LIMIT 5");
$public_users=$stmt->fetchAll(PDO::FETCH_ASSOC);

// Dark mode & birthday
$dark_mode = $current_user['dark_mode'] ? true : false;
$today_day=date('j'); $today_month=date('n');
$is_birthday=($current_user['birth_day']==$today_day && $current_user['birth_month']==$today_month);
$greeting_text=$is_birthday?"HAPPY BIRTHDAY ".strtoupper(htmlspecialchars($current_user['username']))."1!1!1":"Hello, ".htmlspecialchars($current_user['username'])."!";

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $trans[$lang]['dashboard'] ?></title>
<style>
body { background: <?= $dark_mode?'#121212':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; font-family:system-ui; padding:20px;}
.card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.2); max-width:800px; margin:auto; margin-bottom:20px;}
table { width:100%; border-collapse:collapse; }
th, td { border:1px solid <?= $dark_mode?'#333':'#ccc' ?>; padding:8px; text-align:left;}
th { background: <?= $dark_mode?'#333':'#eee' ?>;}
a { text-decoration:none; }
button { background:#000; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; margin-top:5px; display:inline-block; }
button:hover { background:#222; }
.greeting { display:flex; align-items:center; margin-bottom:10px; font-size:18px; }
.greeting img { width:40px;height:40px;border-radius:50%;margin-right:10px;object-fit:cover;border:2px solid #ccc; }
.profile-card img { width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #ccc;margin-bottom:10px; }
.leaderboard-wrapper { max-height:250px; overflow-y:auto; border:1px solid <?= $dark_mode?'#333':'#ccc' ?>; border-radius:0; margin-bottom:10px; }
.leaderboard-wrapper::-webkit-scrollbar{ width:6px; } .leaderboard-wrapper::-webkit-scrollbar-thumb{ background:<?= $dark_mode?'#555':'#ccc' ?>; border-radius:3px; }

.users-row { display:flex; overflow-x:auto; gap:15px; padding:10px 0; }
.users-row::-webkit-scrollbar { height:6px; }
.users-row::-webkit-scrollbar-thumb { background:<?= $dark_mode?'#555':'#ccc' ?>; border-radius:3px; }
.user-card { text-align:center; flex:0 0 auto; }
.user-card img { width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #ccc; display:block; margin:auto; }
.user-card span { display:block; margin-top:5px; font-weight:bold; }

.quick-links { display:flex; flex-direction:column; gap:5px; margin-top:10px; }
.quick-links button { width:100%; text-align:center; }
.switch { position: relative; display:inline-block; width:50px; height:24px;}
.switch input { display:none;}
.slider{ position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#ccc; transition:.4s; border-radius:24px;}
.slider:before{ position:absolute; content:""; height:18px;width:18px; left:3px; bottom:3px; background:white; transition:.4s; border-radius:50%;}
input:checked + .slider { background:#00ff88;}
input:checked + .slider:before { transform:translateX(26px);}
</style>
</head>
<body>

<!-- Leaderboard Card -->
<div class="card">
  <div class="greeting">
      <img src="uploads/<?= htmlspecialchars($current_user['profile_pic']) ?>" alt="Profile Picture">
      <?= $greeting_text ?>
  </div>
  <h2><?= $trans[$lang]['leaderboard'] ?></h2>
  <div class="leaderboard-wrapper">
    <table>
      <tr><th>ID</th><th><?= $trans[$lang]['edit_profile'] ?></th><th>Rank</th></tr>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td>
        <?php if($u['privacy'] && $current_user['username']!=='admin') {
            echo htmlspecialchars($u['username']);
        } else {
            echo '<a href="profile.php?id='.$u['id'].'" style="color:inherit;">'.htmlspecialchars($u['username']).'</a>';
        } ?>
        </td>
        <td><?= ($u['privacy'] && $current_user['username']!=='admin')?'':htmlspecialchars($u['rank']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <p style="margin-top:5px;"><a href="users.php" style="color:blue; text-decoration:none;">View All Users</a></p>
  <a href="edit_profile.php"><button><?= $trans[$lang]['edit_profile'] ?></button></a>
  <a href="logout.php"><button><?= $trans[$lang]['logout'] ?></button></a>
</div>

<!-- Current Profile Card -->
<div class="card profile-card">
  <h2><?= $trans[$lang]['my_profile'] ?></h2>
  <img src="uploads/<?= htmlspecialchars($current_user['profile_pic']) ?>" alt="Profile Picture"><br>
  <strong><?= htmlspecialchars($current_user['username']) ?></strong><br>
  Rank: <?= htmlspecialchars($current_user['rank']) ?><br>
  <?php if(!empty($current_user['bio'])): ?>
    <p style="margin-top:10px;"><?= nl2br(htmlspecialchars($current_user['bio'])) ?></p>
  <?php endif; ?>
  <?php if(!empty($current_user['birth_day']) && !empty($current_user['birth_month'])): ?>
    <p>🎂 Birthday: <?= $current_user['birth_day'] ?> / <?= $current_user['birth_month'] ?></p>
  <?php endif; ?>
</div>

<!-- Friends Card -->
<div class="card">
  <h2>Friends</h2>
  <a href="friends.php"><button style="margin-top:10px;">Manage Friends</button></a>
</div>

<!-- Users Row (public users) -->
<div class="card">
  <h2><?= $trans[$lang]['users_row'] ?></h2>
  <div class="users-row">
    <?php foreach($public_users as $pu): ?>
      <div class="user-card">
        <a href="profile.php?id=<?= $pu['id'] ?>" style="color:<?= htmlspecialchars($pu['color']??'#000') ?>; text-decoration:none;">
          <img src="uploads/<?= htmlspecialchars($pu['profile_pic']) ?>" alt="<?= htmlspecialchars($pu['username']) ?>">
          <span style="color:<?= htmlspecialchars($pu['color']??'#000') ?>;"><?= htmlspecialchars($pu['username']) ?></span>
        </a>
      </div>
    <?php endforeach; ?>
    <div class="user-card">
      <a href="users.php" style="color:#fff; text-decoration:none;">
        <span>Users →</span>
      </a>
    </div>
  </div>
</div>
<!-- Quick Links -->
<div class="card">
  <h2><?= $trans[$lang]['quick_links'] ?></h2>
  <div class="quick-links">
    <a href="[GetHelp]"><button><?= $trans[$lang]['help'] ?></button></a>
    <a href="[GetReservations]"><button><?= $trans[$lang]['reservations'] ?></button></a>
    <a href="[VerfiedInformation]"><button><?= $trans[$lang]['information'] ?></button></a>
    <a href="[News]"><button><?= $trans[$lang]['news'] ?></button></a>
  </div>
</div>

<!-- Settings Card -->
<div class="card">
  <h2><?= $trans[$lang]['settings'] ?></h2>

  <!-- Privacy -->
  <h3 style="margin-top:5px; margin-bottom:5px;"><?= $trans[$lang]['privacy'] ?></h3>
  <form method="post" style="display:inline;">
    <input type="hidden" name="privacy_toggle" value="1">
    <label class="switch">
      <input type="checkbox" name="privacy" onchange="this.form.submit()" <?= $current_user['privacy']?'checked':'' ?>>
      <span class="slider"></span>
    </label>
  </form>

  <!-- Dark Mode -->
  <h3 style="margin-top:10px; margin-bottom:5px;"><?= $trans[$lang]['dark_mode'] ?></h3>
  <form method="post" style="display:inline;">
    <input type="hidden" name="dark_mode_toggle" value="1">
    <label class="switch">
      <input type="checkbox" name="dark_mode" onchange="this.form.submit()" <?= $current_user['dark_mode']?'checked':'' ?>>
      <span class="slider"></span>
    </label>
  </form>

  <!-- Language -->
  <h3 style="margin-top:10px; margin-bottom:5px;"><?= $trans[$lang]['language'] ?></h3>
  <form method="post">
    <select name="language" onchange="this.form.submit()">
      <option value="en" <?= $lang==='en'?'selected':'' ?>>English</option>
      <option value="th" <?= $lang==='th'?'selected':'' ?>>ไทย</option>
    </select>
  </form>
</div>

<!-- Stats Card -->
<div class="card">
  <h2><?= $trans[$lang]['stats'] ?></h2>
  <p style="font-size:18px; margin:10px 0;"><strong><?= $trans[$lang]['total_users'] ?>:</strong> <?= count($users) ?></p>
  <p style="font-size:18px; margin:10px 0;"><strong><?= $trans[$lang]['last_added_user'] ?>:</strong> <?= htmlspecialchars(end($users)['username']) ?></p>
</div>

<!-- Admin Panel (only for admin) -->
<?php if($current_user['username']==='admin'): ?>
<div class="card">
  <h2><?= $trans[$lang]['admin_panel'] ?></h2>
  <div class="quick-links">
    <a href="admin.php"><button style="width:100%;"><?= $trans[$lang]['admin_panel'] ?></button></a>
  </div>
</div>
<?php endif; ?>

<!-- Give Feedback Button -->
<div style="text-align:center; margin:30px 0;">
  <a href="feedback.php"><button style="font-size:18px; padding:12px 24px;"><?= $trans[$lang]['give_feedback'] ?></button></a>
</div>

</body>
</html>