<?php
header('Content-Type: text/html; charset=utf-8');
require "config.php";

if(!isset($_SESSION['user_id'])) header("Location: index.php");

// --- language + translations ---
if(!isset($_SESSION['lang'])) $_SESSION['lang']='en';
$lang=$_SESSION['lang'];
$trans=[
'en'=>[
    'friends'=>'Friends',
    'current_friends'=>'My Current Friends',
    'send_request'=>'Send Friend Request',
    'pending'=>'Pending',
    'no_friends'=>'You have no friends yet 😢',
    'no_users'=>'No users available to send requests.',
    'unfriend'=>'Unfriend',
    'accept'=>'Accept',
    'reject'=>'Reject',
    'pending_requests'=>'Pending Friend Requests',
],
'th'=>[
    'friends'=>'เพื่อน',
    'current_friends'=>'เพื่อนของฉัน',
    'send_request'=>'ส่งคำขอเป็นเพื่อน',
    'pending'=>'รอดำเนินการ',
    'no_friends'=>'คุณยังไม่มีเพื่อน 😢',
    'no_users'=>'ไม่มีผู้ใช้ให้ส่งคำขอ',
    'unfriend'=>'เลิกเป็นเพื่อน',
    'accept'=>'ยอมรับ',
    'reject'=>'ปฏิเสธ',
    'pending_requests'=>'คำขอเป็นเพื่อนที่รอดำเนินการ',
]
];

// Fetch current user
$stmt=$pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$current_user=$stmt->fetch(PDO::FETCH_ASSOC);
$dark_mode=$current_user['dark_mode']?true:false;

// Current friends
$stmt=$pdo->prepare("
    SELECT u.id,u.username,u.profile_pic,u.username_color
    FROM users u
    JOIN friends f ON (u.id=f.sender_id OR u.id=f.receiver_id)
    WHERE f.status='accepted'
      AND (f.sender_id=? OR f.receiver_id=?)
      AND u.id!=?
");
$stmt->execute([$_SESSION['user_id'],$_SESSION['user_id'],$_SESSION['user_id']]);
$friends=$stmt->fetchAll(PDO::FETCH_ASSOC);

// Pending requests received
$stmt=$pdo->prepare("
    SELECT f.id as fid, u.id, u.username, u.profile_pic, u.username_color
    FROM users u
    JOIN friends f ON f.sender_id=u.id
    WHERE f.receiver_id=? AND f.status='pending'
");
$stmt->execute([$_SESSION['user_id']]);
$pending_requests=$stmt->fetchAll(PDO::FETCH_ASSOC);

// Potential friends
$stmt=$pdo->prepare("
    SELECT u.id,u.username,u.profile_pic,u.username_color
    FROM users u
    WHERE u.id!=?
      AND u.id NOT IN (
        SELECT CASE WHEN sender_id=? THEN receiver_id ELSE sender_id END
        FROM friends
        WHERE status IN ('accepted','pending') AND (sender_id=? OR receiver_id=?)
      )
    ORDER BY u.id ASC
");
$stmt->execute([$current_user['id'],$current_user['id'],$current_user['id'],$current_user['id']]);
$potential_friends=$stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $trans[$lang]['friends'] ?></title>
<style>
body {
    background: <?= $dark_mode?'#121212':'#f5f5f5' ?>;
    color: <?= $dark_mode?'#eee':'#000' ?>;
    font-family: system-ui; padding:20px;
}
.card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.2); max-width:900px; margin:auto; margin-bottom:20px; }
h2,h3{margin:5px 0;}

.friends-row{display:flex;overflow-x:auto;gap:15px;padding:10px 0;}
.friends-row::-webkit-scrollbar{height:6px;}
.friends-row::-webkit-scrollbar-thumb{background:<?= $dark_mode?'#555':'#ccc' ?>;border-radius:3px;}
.friend-card{text-align:center;flex:0 0 100px;background:<?= $dark_mode?'#181818':'#fafafa' ?>;border-radius:12px;padding:10px;transition:transform .2s;}
.friend-card:hover{transform:translateY(-4px);}
.friend-card img{width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #ccc;}
.friend-card span{display:block;margin-top:5px;font-weight:bold;color:#fff !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

.friends-column{display:flex;flex-direction:column;gap:10px;max-height:400px;overflow-y:auto;}
.friend-row{display:flex;justify-content:space-between;align-items:center;padding:10px;background:<?= $dark_mode?'#181818':'#fafafa' ?>;border-radius:12px;transition:transform .2s;}
.friend-row:hover{transform:translateY(-2px);}
.friend-row img{width:50px;height:50px;border-radius:50%;border:2px solid #ccc;}
.friend-row span{font-weight:bold;color:#fff !important;}

button{padding:6px 10px;border:none;border-radius:6px;cursor:pointer;}
.unfriend-btn{background:#c00;color:#fff;}
.unfriend-btn:hover{background:#a00;}
.send-btn{background:#0a0;color:#fff;}
.send-btn:hover{background:#0f0;}
.accept-btn{background:#008;color:#fff;}
.accept-btn:hover{background:#00f;}
.reject-btn{background:#c60;color:#fff;}
.reject-btn:hover{background:#a50;}
.disabled-btn{background:#555;color:#fff;cursor:not-allowed;}

</style>
</head>
<body>

<!-- Current Friends -->
<div class="card">
<h2><?= $trans[$lang]['current_friends'] ?></h2>
<?php if(count($friends)>0): ?>
<div class="friends-row">
<?php foreach($friends as $f): ?>
<div class="friend-card">
<img src="uploads/<?= htmlspecialchars($f['profile_pic']) ?>" alt="<?= htmlspecialchars($f['username']) ?>">
<span><?= htmlspecialchars($f['username']) ?></span>
<form method="post" action="friends_action.php" style="margin-top:5px;">
<input type="hidden" name="action" value="unfriend">
<input type="hidden" name="friend_id" value="<?= $f['id'] ?>">
<button class="unfriend-btn"><?= $trans[$lang]['unfriend'] ?></button>
</form>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="opacity:.7;"><?= $trans[$lang]['no_friends'] ?></p>
<?php endif; ?>
</div>

<!-- Pending Requests -->
<div class="card">
<h2><?= $trans[$lang]['pending_requests'] ?></h2>
<?php if(count($pending_requests)>0): ?>
<div class="friends-column">
<?php foreach($pending_requests as $pr): ?>
<div class="friend-row">
<div style="display:flex;align-items:center;gap:10px;">
<img src="uploads/<?= htmlspecialchars($pr['profile_pic']) ?>" alt="<?= htmlspecialchars($pr['username']) ?>">
<span><?= htmlspecialchars($pr['username']) ?></span>
</div>
<div>
<form method="post" action="friends_action.php" style="display:inline;">
<input type="hidden" name="action" value="accept">
<input type="hidden" name="friend_id" value="<?= $pr['fid'] ?>">
<button class="accept-btn">✅ <?= $trans[$lang]['accept'] ?></button>
</form>
<form method="post" action="friends_action.php" style="display:inline;">
<input type="hidden" name="action" value="reject">
<input type="hidden" name="friend_id" value="<?= $pr['fid'] ?>">
<button class="reject-btn">❌ <?= $trans[$lang]['reject'] ?></button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="opacity:.7;"><?= $trans[$lang]['no_users'] ?></p>
<?php endif; ?>
</div>

<!-- Potential Friends -->
<div class="card">
<h2><?= $trans[$lang]['send_request'] ?></h2>
<?php if(count($potential_friends)>0): ?>
<div class="friends-column">
<?php foreach($potential_friends as $pf): ?>
<div class="friend-row">
<div style="display:flex;align-items:center;gap:10px;">
<img src="uploads/<?= htmlspecialchars($pf['profile_pic']) ?>" alt="<?= htmlspecialchars($pf['username']) ?>">
<span><?= htmlspecialchars($pf['username']) ?></span>
</div>
<div>
<form method="post" action="friends_action.php">
<input type="hidden" name="action" value="send_request">
<input type="hidden" name="friend_id" value="<?= $pf['id'] ?>">
<button class="send-btn"><?= $trans[$lang]['send_request'] ?></button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="opacity:.7;"><?= $trans[$lang]['no_users'] ?></p>
<?php endif; ?>
</div>

<!-- Back Button -->
<div class="card" style="text-align:center;">
<a href="dashboard.php"><button style="width:200px;">← Back to Dashboard</button></a>
</div>

</body>
</html>