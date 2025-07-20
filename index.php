<?php
session_start();
include 'config.php';

// Get all posts
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>My Blog</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="admin.php">Admin</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <article class="post">
                    <?php if (!empty($post['image'])): ?>
                    <div class="post-image" style="margin-bottom: 15px;">
                        <a href="post.php?id=<?= $post['id'] ?>">
                            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="max-width: 100%; max-height: 200px; display: block;">
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                    <div class="meta">
                        <span class="date"><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                    </div>
                    <div class="excerpt">
                        <?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...
                    </div>
                    <a href="post.php?id=<?= $post['id'] ?>" class="read-more">Read More</a>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>