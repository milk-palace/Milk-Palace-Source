<?php
require "config.php";
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Fetch user
$stmt=$pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);

$dark_mode = $user['dark_mode'] ? true : false;

$msg = "";
if(isset($_POST['send_feedback'])){
    $rating = intval($_POST['rating'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    if($rating>=1 && $rating<=5 && !empty($description)){
        $stmt=$pdo->prepare("INSERT INTO feedback (user_id, username, rating, description) VALUES (?,?,?,?)");
        $stmt->execute([$user['id'], $user['username'], $rating, $description]);
        $msg = "<span style='color:#0f0;'>Feedback sent!</span>";
    } else {
        $msg = "<span style='color:#f00;'>Please fill all fields correctly.</span>";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Feedback</title>
<style>
body { font-family:system-ui; background: <?= $dark_mode?'#121212':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; margin:0; padding:20px; }
.feedback-card { background: <?= $dark_mode?'#1e1e1e':'#fff' ?>; border-radius:12px; padding:20px; max-width:400px; margin:40px 0; margin-left:auto; text-align:right; box-shadow:0 6px 20px rgba(0,0,0,0.2);}
.feedback-card h2 { margin-top:0; }
textarea { width:100%; padding:8px; margin:8px 0; border-radius:6px; border:1px solid <?= $dark_mode?'#555':'#ccc' ?>; background: <?= $dark_mode?'#2a2a2a':'#fff' ?>; color: <?= $dark_mode?'#eee':'#000' ?>; box-sizing:border-box; }
button { background:#000; color:#fff; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; margin-top:8px; }
button:hover{ background:#222; }
.star-rating { display:flex; justify-content:flex-end; gap:5px; direction: rtl; } /* rtl so 5 star is first on the right */
.star-rating input { display:none; }
.star-rating label { font-size:24px; color:#ccc; cursor:pointer; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color:#ff0; }
</style>
</head>
<body>

<div class="feedback-card">
    <h2>Feedback</h2>
    <?php if($msg) echo $msg; ?>
    <form method="post">
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
            <input type="radio" id="star1" name="rating" value="1" checked><label for="star1">★</label>
        </div>
        <textarea name="description" placeholder="Describe your feedback" required></textarea>
        <button type="submit" name="send_feedback">Send Feedback</button>
    </form>
    <a href="dashboard.php"><button>Dashboard</button></a>
</div>

</body>
</html>