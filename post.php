<?php
session_start();
include 'config.php';

$post_id = $_GET['id'] ?? 0;

// Get the post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

// Handle comment submission
$comment_message = '';
if (isset($_POST['submit_comment'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, name, email, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$post_id, $name, $email, $comment]);
            
            if ($result) {
                $comment_message = '<p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">✓ Comment posted successfully!</p>';
                // Refresh to show new comment
                header("Location: post.php?id=$post_id#comments");
                exit;
            } else {
                $comment_message = '<p style="color: red; background: #f8d7da; padding: 10px; border-radius: 4px;">✗ Failed to post comment.</p>';
            }
        } catch(Exception $e) {
            $comment_message = '<p style="color: red; background: #f8d7da; padding: 10px; border-radius: 4px;">✗ Error: ' . $e->getMessage() . '</p>';
        }
    } else {
        $comment_message = '<p style="color: red; background: #f8d7da; padding: 10px; border-radius: 4px;">✗ Please fill in all fields.</p>';
    }
}

// Get comments for this post
$stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: white; text-decoration: none;">My Blog</a></h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="admin.php">Admin</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <article class="post">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="meta">
                <span class="date"><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
            </div>
            
            <?php if (!empty($post['image'])): ?>
            <div class="post-image" style="margin: 20px 0;">
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="max-width: 100%; height: auto; display: block;">
            </div>
            <?php endif; ?>
            
            <div class="content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </article>

        <div class="comments" id="comments">
            <h3>Comments (<?= count($comments) ?>)</h3>
            
            <?= $comment_message ?>
            
            <form method="POST" style="margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="comment">Comment:</label>
                    <textarea id="comment" name="comment" required><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                </div>
                <button type="submit" name="submit_comment" class="btn">Post Comment</button>
            </form>

            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-meta">
                        <strong><?= htmlspecialchars($comment['name']) ?></strong> - 
                        <?= date('F j, Y g:i A', strtotime($comment['created_at'])) ?>
                    </div>
                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>