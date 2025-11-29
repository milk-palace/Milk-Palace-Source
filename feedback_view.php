<?php
// --- feedback_view.php with pagination --- //
header('Content-Type: text/html; charset=utf-8');
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Language setup
if(!isset($_SESSION['lang'])) $_SESSION['lang']='en';
$lang = $_SESSION['lang'];

// Translation array
$trans = [
    'en'=>['feedback'=>'Feedback','dashboard'=>'Dashboard','rating'=>'Rating','description'=>'Description','user'=>'User','date'=>'Date','back'=>'← Back to Dashboard','no_feedback'=>'No feedback yet'],
    'th'=>['feedback'=>'คำติชม','dashboard'=>'แผงควบคุม','rating'=>'คะแนน','description'=>'คำอธิบาย','user'=>'ผู้ใช้','date'=>'วันที่','back'=>'← กลับไปยังแผงควบคุม','no_feedback'=>'ยังไม่มีคำติชม']
];

// Fetch current user
$stmt=$pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$current_user=$stmt->fetch(PDO::FETCH_ASSOC);
$dark_mode = $current_user['dark_mode'] ? true : false;

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page-1)*$per_page;

// Count total feedback
$total_stmt = $pdo->query("SELECT COUNT(*) FROM feedback");
$total_feedback = $total_stmt->fetchColumn();
$total_pages = ceil($total_feedback / $per_page);

// Fetch feedback with limit
$stmt = $pdo->prepare("SELECT f.*, u.username, u.profile_pic FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.id DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $trans[$lang]['feedback'] ?></title>
<style>
body { background: <?= $dark_mode?'#121212':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; font-family:system-ui; padding:20px;}
.card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.2); max-width:800px; margin:auto; margin-bottom:20px;}
.feedback-entry { display:flex; align-items:flex-start; margin-bottom:15px; border-bottom:1px solid <?= $dark_mode?'#333':'#ccc' ?>; padding-bottom:10px;}
.feedback-entry img { width:50px; height:50px; border-radius:50%; object-fit:cover; margin-right:10px; border:2px solid #ccc;}
.feedback-entry div { flex:1;}
.feedback-stars { color: gold; }
.pagination { text-align:center; margin-top:20px;}
.pagination a { margin:0 5px; color:<?= $dark_mode?'#0af':'blue' ?>; text-decoration:none; font-weight:bold;}
.pagination a.current { text-decoration:underline; }
</style>
</head>
<body>

<h1 style="text-align:center;"><?= $trans[$lang]['feedback'] ?></h1>

<div class="card">
<?php if(!empty($feedbacks)): ?>
    <?php foreach($feedbacks as $f): ?>
        <div class="feedback-entry">
            <img src="uploads/<?= htmlspecialchars($f['profile_pic']) ?>" alt="<?= htmlspecialchars($f['username']) ?>">
            <div>
                <strong><?= htmlspecialchars($f['username']) ?></strong> | <small><?= htmlspecialchars($f['date_submitted']) ?></small>
                <div class="feedback-stars"><?= str_repeat('★', (int)$f['rating']) . str_repeat('☆', 5-(int)$f['rating']) ?></div>
                <p><?= nl2br(htmlspecialchars($f['description'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p><?= $trans[$lang]['no_feedback'] ?></p>
<?php endif; ?>
</div>

<!-- Pagination -->
<div class="pagination">
<?php if($total_pages>1): ?>
    <?php for($i=1;$i<=$total_pages;$i++): ?>
        <a href="?page=<?= $i ?>" class="<?= $i==$page?'current':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
<?php endif; ?>
</div>

<!-- Back Button -->
<div class="card" style="text-align:center;">
    <a href="dashboard.php"><button style="padding:10px 20px; font-size:16px;"><?= $trans[$lang]['back'] ?></button></a>
</div>

</body>
</html>