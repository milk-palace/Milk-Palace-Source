<?php  
require "config.php";  
if(!isset($_SESSION['user_id'])) header("Location: index.php");  

// Fetch logged-in user  
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");  
$stmt->execute([$_SESSION['user_id']]);  
$me = $stmt->fetch(PDO::FETCH_ASSOC);  

// Only admin can access  
if($me['username'] !== 'admin'){  
    echo "Access denied.";  
    exit;  
}  

$dark_mode = $me['dark_mode'] ? true : false;  

// Language handling  
$lang = $_SESSION['lang'] ?? 'en';  
if(isset($_POST['set_language'])){  
    $lang = $_POST['language'];  
    $_SESSION['lang'] = $lang;  
}  

// Translation array  
$T = [  
    'en'=>[  
        'admin_panel'=>'Admin Panel', 'add_new_user'=>'Add New User', 'existing_users'=>'Existing Users',
        'edit'=>'Edit', 'username'=>'Username', 'password'=>'Password', 'rank_job'=>'Rank / Job', 'bio'=>'Bio', 
        'new_password'=>'New password (leave blank to keep)','save'=>'Save', 'dashboard'=>'Dashboard', 
        'logout'=>'Logout', 'birthday'=>'Birthday', 'feedback'=>'Feedback', 'view_feedback'=>'View Feedback'
    ],  
    'th'=>[  
        'admin_panel'=>'แผงควบคุมผู้ดูแลระบบ', 'add_new_user'=>'เพิ่มผู้ใช้ใหม่', 'existing_users'=>'ผู้ใช้ที่มีอยู่',
        'edit'=>'แก้ไข', 'username'=>'ชื่อผู้ใช้', 'password'=>'รหัสผ่าน', 'rank_job'=>'ตำแหน่ง / งาน', 'bio'=>'ประวัติ', 
        'new_password'=>'รหัสผ่านใหม่ (เว้นว่างเพื่อเก็บเดิม)','save'=>'บันทึก', 'dashboard'=>'แดชบอร์ด', 
        'logout'=>'ออกจากระบบ', 'birthday'=>'วันเกิด', 'feedback'=>'ข้อเสนอแนะ', 'view_feedback'=>'ดูข้อเสนอแนะ'
    ]  
];  

// Month array  
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];

// Automatically read all images in uploads/ folder  
$avatars = glob("uploads/*.{png,jpg,jpeg}", GLOB_BRACE);  

// Stats
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'] ?? 0;

$stmt = $pdo->query("SELECT username FROM users ORDER BY id DESC LIMIT 1");
$last_user = $stmt->fetch(PDO::FETCH_ASSOC)['username'] ?? '-';

// Handle adding a new user  
if(isset($_POST['add'])){  
    $username = $_POST['username'];  
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  
    $rank = $_POST['rank'];  
    $bio = $_POST['bio'];  
    $profile_pic = $_POST['profile_pic'];  
    $birth_day = $_POST['birth_day'];  
    $birth_month = $_POST['birth_month'];  

    $stmt = $pdo->prepare("INSERT INTO users (username,password,rank,bio,profile_pic,birth_day,birth_month) VALUES (?,?,?,?,?,?,?)");  
    $stmt->execute([$username,$password,$rank,$bio,$profile_pic,$birth_day,$birth_month]);  
    $msg = $T[$lang]['add_new_user'].' success!';  
}  

// Handle editing an existing user  
if(isset($_POST['edit'])){  
    $id = $_POST['id'];  
    $username = $_POST['username'];  
    $rank = $_POST['rank'];  
    $bio = $_POST['bio'];  
    $profile_pic = $_POST['profile_pic'];  
    $birth_day = $_POST['birth_day'];  
    $birth_month = $_POST['birth_month'];  

    if(!empty($_POST['password'])){  
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  
        $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, rank=?, bio=?, profile_pic=?, birth_day=?, birth_month=? WHERE id=?");  
        $stmt->execute([$username, $password, $rank, $bio, $profile_pic, $birth_day, $birth_month, $id]);  
    } else {  
        $stmt = $pdo->prepare("UPDATE users SET username=?, rank=?, bio=?, profile_pic=?, birth_day=?, birth_month=? WHERE id=?");  
        $stmt->execute([$username, $rank, $bio, $profile_pic, $birth_day, $birth_month, $id]);  
    }  
    $msg = $T[$lang]['save'].'!';  
}  

// Fetch all users  
$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");  
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);  
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $T[$lang]['admin_panel'] ?></title>
<style>
body { font-family:system-ui; background: <?= $dark_mode?'#121212':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; padding:20px; }
.card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.2); max-width:700px; margin:auto; margin-bottom:20px; text-align:center; }
input, textarea, select { width:100%; padding:8px; margin:4px 0; border-radius:6px; border:1px solid <?= $dark_mode?'#555':'#ccc' ?>; background: <?= $dark_mode?'#2a2a2a':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; box-sizing:border-box; }
button { background:#000; color:#fff; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; margin-top:8px; }
button:hover{background:#222;}
.profile-gallery { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-top:10px; max-height:180px; overflow-y:auto; }
.profile-gallery label { cursor:pointer; display:inline-block; border-radius:12px; overflow:hidden; transition:transform 0.2s ease, box-shadow 0.2s ease; background: <?= $dark_mode?'#2a2a2a':'#fff' ?>; padding:5px; box-shadow:0 2px 6px rgba(0,0,0,0.15); }
.profile-gallery img { width:60px; height:60px; border-radius:10px; border:2px solid <?= $dark_mode?'#555':'#ccc' ?>; display:block; }
.profile-gallery input[type="radio"] { display:none; }
.profile-gallery input[type="radio"]:checked + img { border:2px solid #00ff88; box-shadow:0 0 10px #00ff88; }
.profile-gallery label:hover { transform:scale(1.05); }
.table-wrapper { width:100%; overflow:auto; max-height:400px; border:1px solid <?= $dark_mode?'#555':'#ccc' ?>; margin-top:10px; }
table { width:100%; border-collapse:collapse; min-width:600px; }
th, td { border:1px solid <?= $dark_mode?'#555':'#ccc' ?>; padding:8px; vertical-align:top; }
th { background: <?= $dark_mode?'#2a2a2a':'#eee' ?>; }
.stats-container { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-bottom:20px; }
.stats-card { flex:1; min-width:150px; padding:15px; border-radius:12px; background: <?= $dark_mode?'#2a2a2a':'#f7f7f7' ?>; box-shadow:0 4px 10px rgba(0,0,0,0.2); text-align:center; }
.stats-card h3 { margin:0; font-size:16px; }
.stats-card p { font-size:24px; font-weight:bold; margin:5px 0 0 0; }
@media (max-width:600px){ input, textarea, button, select { font-size:14px; } .profile-gallery img { width:50px; height:50px; } }
</style>
</head>
<body>

<div class="card">
<h2><?= $T[$lang]['admin_panel'] ?></h2>
<?php if(isset($msg)) echo "<p style='color:#0f0;'>$msg</p>"; ?>

<!-- Language Selector -->
<form method="post">
<select name="language" onchange="this.form.submit()">
    <option value="en" <?= $lang=='en'?'selected':'' ?>>English</option>
    <option value="th" <?= $lang=='th'?'selected':'' ?>>ไทย</option>
</select>
<input type="hidden" name="set_language" value="1">
</form>
</div>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stats-card">
        <h3>Total Users</h3>
        <p><?= $total_users ?></p>
    </div>
    <div class="stats-card">
        <h3>Last Added User</h3>
        <p><?= htmlspecialchars($last_user) ?></p>
    </div>
</div>

<!-- Add User Form -->
<div class="card">
<h3><?= $T[$lang]['add_new_user'] ?></h3>
<form method="post">
<input type="text" name="username" placeholder="<?= $T[$lang]['username'] ?>" required>
<input type="password" name="password" placeholder="<?= $T[$lang]['password'] ?>" required>
<input type="text" name="rank" placeholder="<?= $T[$lang]['rank_job'] ?>">
<textarea name="bio" rows="2" placeholder="<?= $T[$lang]['bio'] ?>"></textarea>

<!-- Birthday -->
<label><?= $T[$lang]['birthday'] ?>:</label>
<div style="display:flex; gap:5px;">
  <select name="birth_day">
    <?php for($d=1;$d<=31;$d++): ?>
      <option value="<?= $d ?>"><?= $d ?></option>
    <?php endfor; ?>
  </select>
  <select name="birth_month">
    <?php foreach($months as $num=>$name): ?>
      <option value="<?= $num ?>"><?= $T[$lang][$name] ?? $name ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="profile-gallery">
<?php foreach($avatars as $a): ?>
<label>
  <input type="radio" name="profile_pic" value="<?= basename($a) ?>" <?= basename($a)=='default.png'?'checked':'' ?>>
  <img src="<?= $a ?>" alt="<?= basename($a) ?>">
</label>
<?php endforeach; ?>
</div>

<button type="submit" name="add"><?= $T[$lang]['add_new_user'] ?></button>
</form>
</div>

<!-- Existing Users Table -->
<div class="card">
<h3><?= $T[$lang]['existing_users'] ?></h3>
<div class="table-wrapper">
<table>
<tr>
    <th>ID</th>
    <th><?= $T[$lang]['edit'] ?></th>
</tr>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td>
  <form method="post">
    <input type="hidden" name="id" value="<?= $u['id'] ?>">
    <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required placeholder="<?= $T[$lang]['username'] ?>">
    <input type="password" name="password" placeholder="<?= $T[$lang]['new_password'] ?>">
    <input type="text" name="rank" value="<?= htmlspecialchars($u['rank']) ?>" placeholder="<?= $T[$lang]['rank_job'] ?>">
    <textarea name="bio" rows="2" placeholder="<?= $T[$lang]['bio'] ?>"><?= htmlspecialchars($u['bio']) ?></textarea>

    <!-- Birthday -->
    <label><?= $T[$lang]['birthday'] ?>:</label>
    <div style="display:flex; gap:5px;">
      <select name="birth_day">
        <?php for($d=1;$d<=31;$d++): ?>
          <option value="<?= $d ?>" <?= $u['birth_day']==$d?'selected':'' ?>><?= $d ?></option>
        <?php endfor; ?>
      </select>
      <select name="birth_month">
        <?php foreach($months as $num=>$name): ?>
          <option value="<?= $num ?>" <?= $u['birth_month']==$num?'selected':'' ?>><?= $T[$lang][$name] ?? $name ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="profile-gallery">
      <?php foreach($avatars as $a): ?>
      <label>
        <input type="radio" name="profile_pic" value="<?= basename($a) ?>" <?= $u['profile_pic']==basename($a)?'checked':'' ?>>
        <img src="<?= $a ?>" alt="<?= basename($a) ?>">
      </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" name="edit"><?= $T[$lang]['save'] ?></button>
  </form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
<!-- Feedback Card -->
<div class="card">
  <h3>Feedback</h3>
  <a href="feedback_view.php"><button style="margin-top:10px;">View Feedback</button></a>
</div>

<!-- Bottom Navigation -->
<div class="card" style="text-align:center;">
  <a href="dashboard.php"><button><?= $T[$lang]['dashboard'] ?></button></a>
  <a href="logout.php"><button><?= $T[$lang]['logout'] ?></button></a>
</div>

</body>
</html>