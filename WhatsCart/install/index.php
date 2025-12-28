<?php
// install/index.php
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'];
    $name = $_POST['name'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create Database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        
        // Import Structure
        $sql = "
        CREATE TABLE IF NOT EXISTS `settings` (
            `setting_key` varchar(50) PRIMARY KEY,
            `setting_value` text
        );
        INSERT INTO `settings` VALUES 
        ('site_title', 'WhatsCart Store'),
        ('whatsapp', '123456789'),
        ('currency', 'USD'),
        ('admin_user', 'admin'),
        ('admin_pass', '" . password_hash('123456', PASSWORD_DEFAULT) . "');

        CREATE TABLE IF NOT EXISTS `products` (
            `id` int(11) AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255) NOT NULL,
            `price` decimal(10,2) NOT NULL,
            `image` varchar(255) DEFAULT 'default.png',
            `active` tinyint(1) DEFAULT 1
        );
        ";
        $pdo->exec($sql);

        // Update config.php
        $configContent = "<?php
\$db_host = '$host';
\$db_name = '$name';
\$db_user = '$user';
\$db_pass = '$pass';

try {
    \$pdo = new PDO(\"mysql:host=\$db_host;dbname=\$db_name;charset=utf8mb4\", \$db_user, \$db_pass);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die(\"DB Error\");
}
?>";
        file_put_contents('../includes/config.php', $configContent);
        
        $msg = "<p style='color:green'>Installation Success! Please delete 'install' folder.</p><a href='../admin/login.php'>Go to Admin</a>";
        
    } catch (PDOException $e) {
        $msg = "<p style='color:red'>Connection Failed: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Installer</title><link href="https://cdn.tailwindcss.com" rel="stylesheet"></head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-xl font-bold mb-4">WhatsCart Installer</h2>
        <?= $msg ?>
        <form method="POST">
            <input class="w-full border p-2 mb-2" type="text" name="host" placeholder="Database Host (localhost)" value="localhost" required>
            <input class="w-full border p-2 mb-2" type="text" name="name" placeholder="Database Name" required>
            <input class="w-full border p-2 mb-2" type="text" name="user" placeholder="Database User" required>
            <input class="w-full border p-2 mb-2" type="password" name="pass" placeholder="Database Password">
            <button class="w-full bg-blue-600 text-white p-2 rounded">Install</button>
        </form>
    </div>
</body>
</html>