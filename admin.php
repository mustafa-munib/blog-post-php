<!DOCTYPE html>
<html>
<head>
    <title>Simple Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Simple Admin Panel</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="admin.php">Admin</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        session_start();
        
        // Database connection
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=blog_db", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(Exception $e) {
            die("Database error: " . $e->getMessage());
        }
        
        // Simple login
        if (isset($_POST['login'])) {
            if ($_POST['password'] === 'admin123') {
                $_SESSION['logged_in'] = true;
                echo "<p style='color: green;'>✓ Login successful!</p>";
            } else {
                echo "<p style='color: red;'>✗ Wrong password!</p>";
            }
        }
        
        // Logout
        if (isset($_GET['logout'])) {
            unset($_SESSION['logged_in']);
            echo "<p style='color: blue;'>Logged out.</p>";
        }
        
        // Create uploads directory if it doesn't exist
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // Create post with image
        if (isset($_POST['create']) && isset($_SESSION['logged_in'])) {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $image_path = null;
            
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $target_dir = "uploads/";
                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Check file size (2MB max)
                if ($_FILES["image"]["size"] > 2000000) {
                    echo "<p style='color: red;'>✗ Image is too large (max 2MB)</p>";
                } 
                // Check file type
                elseif (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo "<p style='color: red;'>✗ Only JPG, JPEG, PNG & GIF files are allowed</p>";
                }
                // Try to upload
                elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                    echo "<p style='color: green;'>✓ Image uploaded successfully</p>";
                } else {
                    echo "<p style='color: red;'>✗ Error uploading image</p>";
                }
            }
            
            if (!empty($title) && !empty($content)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, created_at) VALUES (?, ?, ?, NOW())");
                    $result = $stmt->execute([$title, $content, $image_path]);
                    
                    if ($result) {
                        echo "<p style='color: green; background: #d4edda; padding: 10px; border-radius: 4px;'>✓ Post created successfully!</p>";
                    } else {
                        echo "<p style='color: red;'>✗ Failed to create post</p>";
                    }
                } catch(Exception $e) {
                    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Please fill in both title and content</p>";
            }
        }
        
        // Delete post
        if (isset($_GET['delete']) && isset($_SESSION['logged_in'])) {
            $id = $_GET['delete'];
            try {
                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $result = $stmt->execute([$id]);
                if ($result) {
                    echo "<p style='color: green;'>✓ Post deleted!</p>";
                }
            } catch(Exception $e) {
                echo "<p style='color: red;'>✗ Delete error: " . $e->getMessage() . "</p>";
            }
        }
        
        // Get post for editing
        $edit_post = null;
        if (isset($_GET['edit']) && isset($_SESSION['logged_in'])) {
            $id = $_GET['edit'];
            try {
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$id]);
                $edit_post = $stmt->fetch();
                if (!$edit_post) {
                    echo "<p style='color: red;'>✗ Post not found</p>";
                }
            } catch(Exception $e) {
                echo "<p style='color: red;'>✗ Error loading post: " . $e->getMessage() . "</p>";
            }
        }
        
        // Update post
        if (isset($_POST['update']) && isset($_SESSION['logged_in'])) {
            $id = $_POST['post_id'];
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $keep_image = isset($_POST['keep_image']) ? $_POST['keep_image'] : '';
            $image_path = $keep_image;
            
            // Handle new image upload
            if (!empty($_FILES['image']['name'])) {
                $target_dir = "uploads/";
                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Check file size (2MB max)
                if ($_FILES["image"]["size"] > 2000000) {
                    echo "<p style='color: red;'>✗ Image is too large (max 2MB)</p>";
                } 
                // Check file type
                elseif (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo "<p style='color: red;'>✗ Only JPG, JPEG, PNG & GIF files are allowed</p>";
                }
                // Try to upload
                elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                    echo "<p style='color: green;'>✓ New image uploaded successfully</p>";
                } else {
                    echo "<p style='color: red;'>✗ Error uploading image</p>";
                }
            }
            
            if (!empty($title) && !empty($content)) {
                try {
                    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
                    $result = $stmt->execute([$title, $content, $image_path, $id]);
                    
                    if ($result) {
                        echo "<p style='color: green; background: #d4edda; padding: 10px; border-radius: 4px;'>✓ Post updated successfully!</p>";
                        // Redirect to admin page to avoid resubmission
                        header("Location: admin.php");
                        exit;
                    } else {
                        echo "<p style='color: red;'>✗ Failed to update post</p>";
                    }
                } catch(Exception $e) {
                    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Please fill in both title and content</p>";
            }
        }
        
        // Show login or admin panel
        if (!isset($_SESSION['logged_in'])):
        ?>
        
        <div class="post">
            <h2>Admin Login</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <p><small>Password: admin123</small></p>
        </div>
        
        <?php else: ?>
        
        <p style="text-align: right;"><a href="?logout=1">Logout</a></p>
        
        <?php if ($edit_post): ?>
        <!-- Edit Post Form -->
        <div class="post">
            <h2>Edit Post</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="post_id" value="<?= $edit_post['id'] ?>">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($edit_post['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" required style="height: 250px;"><?= htmlspecialchars($edit_post['content']) ?></textarea>
                </div>
                
                <?php if (!empty($edit_post['image'])): ?>
                <div class="form-group">
                    <label>Current Image:</label>
                    <div style="margin: 10px 0;">
                        <img src="<?= htmlspecialchars($edit_post['image']) ?>" style="max-width: 300px; max-height: 200px;">
                    </div>
                    <label>
                        <input type="checkbox" name="keep_image" value="<?= htmlspecialchars($edit_post['image']) ?>" checked>
                        Keep current image
                    </label>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>New Image (optional):</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="display: block; margin-top: 5px; color: #666;">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" name="update" class="btn" style="background: #f39c12;">Update Post</button>
                    <a href="admin.php" class="btn" style="background: #7f8c8d; margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Create New Post Form -->
        <div class="post">
            <h2>Create New Post</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label>Featured Image:</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="display: block; margin-top: 5px; color: #666;">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                <button type="submit" name="create" class="btn">Create Post</button>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="post">
            <h2>Manage Posts</h2>
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
                $posts = $stmt->fetchAll();
                
                if (count($posts) > 0) {
                    foreach ($posts as $post) {
                        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px;'>";
                        
                        // Show image if available
                        if (!empty($post['image'])) {
                            echo "<div style='margin-bottom: 15px;'>";
                            echo "<img src='" . htmlspecialchars($post['image']) . "' style='max-width: 100%; max-height: 200px; display: block;'>";
                            echo "</div>";
                        }
                        
                        echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
                        echo "<p><small>" . date('F j, Y', strtotime($post['created_at'])) . "</small></p>";
                        echo "<p>" . nl2br(htmlspecialchars(substr($post['content'], 0, 100))) . "...</p>";
                        echo "<a href='post.php?id=" . $post['id'] . "' class='btn'>View</a> ";
                        echo "<a href='?edit=" . $post['id'] . "' class='btn' style='background: #f39c12;'>Edit</a> ";
                        echo "<a href='?delete=" . $post['id'] . "' class='btn btn-danger' onclick='return confirm(\"Delete this post?\")'>Delete</a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No posts found. <a href='setup.php'>Run setup</a> to create sample posts.</p>";
                }
            } catch(Exception $e) {
                echo "<p style='color: red;'>Error loading posts: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        
        <?php endif; ?>
    </main>
</body>
</html>