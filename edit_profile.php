<?php
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Set default language
if(!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
$lang = $_SESSION['lang'];

// Translation array
$trans = [
    'en'=>[
        'edit_profile'=>'Edit Profile',
        'bio'=>'Bio',
        'new_password'=>'New password (leave blank to keep)',
        'username_color'=>'Username Color:',
        'bio_color'=>'Bio Color:',
        'save_changes'=>'Save Changes',
        'back'=>'Back'
    ],
    'th'=>[
        'edit_profile'=>'แก้ไขโปรไฟล์',
        'bio'=>'ประวัติ',
        'new_password'=>'รหัสผ่านใหม่ (เว้นว่างเพื่อเก็บไว้)',
        'username_color'=>'สีชื่อผู้ใช้:',
        'bio_color'=>'สีประวัติ:',
        'save_changes'=>'บันทึกการเปลี่ยนแปลง',
        'back'=>'ย้อนกลับ'
    ]
];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$dark_mode = $user['dark_mode'] ? true : false;

// Handle form submission
if(isset($_POST['save'])){
    $bio = $_POST['bio'];
    $profile_pic = $_POST['profile_pic'];
    $username_color = $_POST['username_color'] ?? '#000000';
    $bio_color = $_POST['bio_color'] ?? '#000000';

    if(!empty($_POST['password'])){
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET bio=?, profile_pic=?, password=?, username_color=?, bio_color=? WHERE id=?");
        $stmt->execute([$bio, $profile_pic, $pass_hash, $username_color, $bio_color, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET bio=?, profile_pic=?, username_color=?, bio_color=? WHERE id=?");
        $stmt->execute([$bio, $profile_pic, $username_color, $bio_color, $_SESSION['user_id']]);
    }
    header("Location: dashboard.php");
    exit;
}

// Automatically read all images in uploads/ folder
$avatars = glob("uploads/*.{png,jpg,jpeg}", GLOB_BRACE);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $trans[$lang]['edit_profile'] ?></title>
<style>
body {
    font-family:system-ui;
    background: <?= $dark_mode?'#121212':'#fff' ?>;
    color: <?= $dark_mode?'#eee':'#000' ?>;
    padding:20px;
}
.card {
    background: <?= $dark_mode?'#1e1e1e':'#fff' ?>;
    border-radius:12px;
    padding:20px;
    box-shadow:0 6px 20px rgba(0,0,0,0.2);
    max-width:600px;
    margin:auto;
    text-align:center;
}
textarea, input[type="password"], input[type="color"] {
    width:90%;
    padding:10px;
    margin:5px 0;
    border-radius:6px;
    border:1px solid <?= $dark_mode?'#555':'#ccc' ?>;
    background: <?= $dark_mode?'#2a2a2a':'#fff' ?>;
    color: <?= $dark_mode?'#eee':'#000' ?>;
}
button {
    background:#000;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:6px;
    cursor:pointer;
    margin-top:10px;
}
button:hover{background:#222;}
.profile-gallery {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: center;
  margin-top: 15px;
  max-height: 300px;
  overflow-y: auto;
  padding-right: 5px;
}
.profile-gallery label {
  cursor:pointer;
  display:inline-block;
  border-radius:12px;
  overflow:hidden;
  transition:transform 0.2s ease, box-shadow 0.2s ease;
  background:<?= $dark_mode?'#2a2a2a':'#fff' ?>;
  padding:5px;
  box-shadow:0 2px 6px rgba(0,0,0,0.15);
}
.profile-gallery img {
  width:80px;
  height:80px;
  border-radius:10px;
  border:2px solid <?= $dark_mode?'#555':'#ccc' ?>;
  display:block;
}
.profile-gallery input[type="radio"] { display:none; }
.profile-gallery input[type="radio"]:checked + img {
  border:2px solid #00ff88;
  box-shadow:0 0 10px #00ff88;
}
.profile-gallery label:hover { transform:scale(1.05); }
.form-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 15px;
}
@media (max-width: 600px) {
  .profile-gallery img { width:60px; height:60px; }
  textarea, input[type="password"], input[type="color"] { width:100%; }
}
</style>
</head>
<body>
<div class="card">
<h2><?= $trans[$lang]['edit_profile'] ?></h2>
<form method="post">
<textarea name="bio" rows="3" placeholder="<?= $trans[$lang]['bio'] ?>"><?= htmlspecialchars($user['bio']) ?></textarea><br>
<input type="password" name="password" placeholder="<?= $trans[$lang]['new_password'] ?>"><br>

<label><?= $trans[$lang]['username_color'] ?></label>
<input type="color" name="username_color" value="<?= htmlspecialchars($user['username_color'] ?? '#000000') ?>">

<label><?= $trans[$lang]['bio_color'] ?></label>
<input type="color" name="bio_color" value="<?= htmlspecialchars($user['bio_color'] ?? '#000000') ?>">

<div class="profile-gallery">
<?php foreach($avatars as $a): ?>
<label>
  <input type="radio" name="profile_pic" value="<?= basename($a) ?>" <?= $user['profile_pic']==basename($a)?'checked':'' ?>>
  <img src="<?= $a ?>" alt="<?= basename($a) ?>">
</label>
<?php endforeach; ?>
</div>

<div class="form-buttons">
  <button type="submit" name="save"><?= $trans[$lang]['save_changes'] ?></button>
  <a href="dashboard.php"><button type="button"><?= $trans[$lang]['back'] ?></button></a>
</div>
</form>
</div>
</body>
</html>