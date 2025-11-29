<?php
// friends_action.php - robust handler for friends actions
// IMPORTANT: keep config.php as the single place that starts session
require 'config.php';

// If config.php doesn't start session, uncomment the next line:
// session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = (int)$_SESSION['user_id'];

// Basic validation
if(!isset($_POST['action'], $_POST['friend_id'])){
    // optional: set a flash message so user sees something
    $_SESSION['flash_error'] = "Invalid request.";
    header("Location: friends.php");
    exit;
}

$action = $_POST['action'];
$raw_id = $_POST['friend_id'];
$fid = (int)$raw_id;

try {
    // Helper: check if $fid is a friends table id
    $stmt = $pdo->prepare("SELECT * FROM friends WHERE id = ?");
    $stmt->execute([$fid]);
    $friendRow = $stmt->fetch(PDO::FETCH_ASSOC); // may be false

    if($action === 'send_request'){
        // here friend_id should be a user id (target)
        $target = $fid;
        if($target === $user_id){
            $_SESSION['flash_error'] = "Can't send request to yourself.";
            header("Location: friends.php"); exit;
        }

        // Check existing any-direction
        $stmt = $pdo->prepare("SELECT * FROM friends WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)");
        $stmt->execute([$user_id, $target, $target, $user_id]);
        if($stmt->rowCount() === 0){
            $stmt = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $target]);
        } // else already exists -> nothing
    }

    elseif($action === 'accept' || $action === 'reject'){
        // Two cases:
        // 1) friend_id was friends.id (pending row id) --> friendRow exists
        // 2) friend_id is sender user id --> friendRow false, treat as sender id

        if($friendRow){
            // Ensure the current user is the receiver of this row
            if((int)$friendRow['receiver_id'] !== $user_id){
                $_SESSION['flash_error'] = "You don't have permission to accept/reject this request.";
                header("Location: friends.php"); exit;
            }

            if($action === 'accept'){
                $stmt = $pdo->prepare("UPDATE friends SET status='accepted' WHERE id=? AND status='pending'");
                $stmt->execute([$friendRow['id']]);
            } else { // reject
                // remove the pending request
                $stmt = $pdo->prepare("DELETE FROM friends WHERE id=? AND status='pending'");
                $stmt->execute([$friendRow['id']]);
            }
        } else {
            // treat $fid as sender user id
            $sender = $fid;
            if($action === 'accept'){
                $stmt = $pdo->prepare("UPDATE friends SET status='accepted' WHERE sender_id=? AND receiver_id=? AND status='pending'");
                $stmt->execute([$sender, $user_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM friends WHERE sender_id=? AND receiver_id=? AND status='pending'");
                $stmt->execute([$sender, $user_id]);
            }
        }
    }

    elseif($action === 'unfriend'){
        // friend_id should be the other user's id (not friends.id)
        // allow either direction, delete any accepted row between the two
        $other = $fid;
        if($other === $user_id){
            $_SESSION['flash_error'] = "Invalid operation.";
            header("Location: friends.php"); exit;
        }
        // Delete accepted friendships in either direction (use grouping)
        $stmt = $pdo->prepare("
            DELETE FROM friends 
            WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?))
              AND status='accepted'
        ");
        $stmt->execute([$user_id, $other, $other, $user_id]);
        // Also delete pending invites user sent to the other (optional)
        // $stmt = $pdo->prepare("DELETE FROM friends WHERE sender_id=? AND receiver_id=? AND status='pending'"); $stmt->execute([$user_id,$other]);
    }

    else {
        // unknown action - ignore quietly or set flash
        $_SESSION['flash_error'] = "Unknown action.";
    }
}
catch(PDOException $e){
    // Log for debugging
    $msg = date('c')." | ".$e->getMessage()." | action={$action} friend_id={$raw_id} user={$user_id}\n";
    file_put_contents(__DIR__.'/friend_errors.log', $msg, FILE_APPEND);
    $_SESSION['flash_error'] = "Something went wrong. Please try again.";
}

// Redirect back to friends page (you can include anchor if you want)
header("Location: friends.php");
exit;