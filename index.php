<?php
session_start();

$db = new SQLite3('database.db');

$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, username TEXT UNIQUE, password TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY, user_id INTEGER, content TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id))");

// rejestracja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: login.php");
    exit;
}

// login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        header("Location: index.php");
        exit;
    } else {
        echo "Invalid login credentials";
    }
}

// publikowanie postow
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :content)");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':content', $_POST['content'], SQLITE3_TEXT);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// poszukiwarka
$searchQuery = '';
$results = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $searchQuery = $_POST['search'];
        $query = "SELECT posts.*, users.username FROM posts 
                  JOIN users ON posts.user_id = users.id 
                  WHERE posts.user_id = '$user_id' 
                  AND posts.content LIKE '%$searchQuery%' 
                  ORDER BY posts.created_at DESC";
    } else {
        $query = "SELECT posts.*, users.username FROM posts 
                  JOIN users ON posts.user_id = users.id 
                  WHERE posts.user_id = '$user_id' 
                  ORDER BY posts.created_at DESC";
    }
    
    $results = $db->query($query);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP Blog</title>
    <style>
        body { font-family: Arial, sans-serif; width: 50%; margin: auto; }
        form { margin-bottom: 20px; }
        textarea, input { width: 100%; }
        button { display: block; margin-top: 10px; }
        .post { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .timestamp { color: gray; font-size: 0.8em; }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        
    <?php else: ?>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <a href="files.php">View Files</a>
        <a href="logout.php">Logout</a>
        <h2>Post Something</h2>
        <form method="POST">
            <textarea name="content" required></textarea>
            <button type="submit">Post</button>
        </form>
        <h2>Your Posts</h2>
        <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="post">
                <p><strong>Author: <?php echo htmlspecialchars($row['username']); ?></strong></p>
                <p><?php echo htmlspecialchars($row['content']); ?></p>
                <div class="timestamp">Posted on: <?php echo $row['created_at']; ?></div>
            </div>
        <?php endwhile; ?>



        <form method="POST">
            <input type="text" name="search" placeholder="Search your posts" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

    <?php endif; ?>
</body>
</html>
