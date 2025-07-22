<!DOCTYPE html>
<html>
<head>
    <title>Blog Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Blog Database Setup</h1>
    
<?php
echo "<p class='info'>Starting setup process...</p>";

try {
    include 'config.php';
    echo "<p class='success'>✓ Database connection successful!</p>";

    // Create posts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME NOT NULL
    )");
    echo "<p class='success'>✓ Posts table created!</p>";

    // Create comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )");
    echo "<p class='success'>✓ Comments table created!</p>";

    // Check if we need sample posts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts");
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "<p class='info'>Adding sample posts...</p>";
        
        // Create uploads directory if it doesn't exist
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        $sample_posts = [
            [
                'title' => 'Welcome to My Blog!',
                'content' => 'This is my first blog post. I\'m excited to share my thoughts and experiences with you. This blog is built with PHP, MySQL, HTML, CSS, and JavaScript.',
                'image' => 'uploads/687cc916578a9.jpg'
            ],
            [
                'title' => 'Getting Started with Web Development',
                'content' => 'Web development is an exciting field that combines creativity with technical skills. In this post, I\'ll share some tips for beginners who want to start their journey in web development.',
                'image' => 'uploads/687cc965783db.jpg'
            ],
            [
                'title' => 'The Power of PHP and MySQL',
                'content' => 'PHP and MySQL make a powerful combination for building dynamic websites. They\'re easy to learn, widely supported, and perfect for creating database-driven applications like this blog.',
                'image' => 'uploads/687cca184364f.jpg'
            ]
        ];

        foreach ($sample_posts as $post) {
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$post['title'], $post['content'], $post['image']]);
        }
        echo "<p class='success'>✓ Sample posts added!</p>";
    } else {
        echo "<p class='info'>Posts already exist ($count posts found)</p>";
    }

    echo "<h2 class='success'>Setup Complete!</h2>";
    echo "<p><strong>Your blog is ready to use:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php'>View Blog</a></li>";
    echo "<li><a href='admin.php'>Admin Panel</a> (password: <strong>admin123</strong>)</li>";
    echo "</ul>";

} catch(PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Make sure XAMPP is running and MySQL is started.</p>";
}
?>

</body>
</html>