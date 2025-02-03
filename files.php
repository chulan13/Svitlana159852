<?php
session_start();
$directory = 'files';

// poszukiwarka
$searchQuery = '';
$files = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
    $allFiles = scandir($directory);
    foreach ($allFiles as $file) {
        if (strpos($file, $searchQuery) !== false) {
            $files[] = $file;
        }
    }
} else {
    $files = scandir($directory); 
}

if (isset($_GET['view']) && file_exists($directory . '/' . $_GET['view'])) {
    $filePath = escapeshellarg($directory . '/' . $_GET['view']); 

    $output = [];
    exec("cat $filePath", $output);

    $fileContent = implode("\n", $output);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; width: 50%; margin: auto; }
        form { margin-bottom: 20px; }
        textarea, input { width: 100%; }
        button { display: block; margin-top: 10px; }
        .file { border-bottom: 1px solid #ccc; padding: 10px 0; }
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
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>

        <h2>Search Files</h2>
        <form method="POST">
            <input type="text" name="search" placeholder="Search files..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit" name="search">Search</button>
        </form>

        <h2>Files in Directory</h2>
        <ul>
            <?php
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue; 
                echo '<li class="file">';
                echo '<a href="?view=' . urlencode($file) . '">' . htmlspecialchars($file) . '</a>';
                echo '</li>';
            }
            ?>
        </ul>

        <?php if ($fileContent): ?>
            <h2>File Content</h2>
            <pre><?php echo htmlspecialchars($fileContent); ?></pre>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
