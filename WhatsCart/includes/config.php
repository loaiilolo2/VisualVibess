<?php
// includes/config.php
$db_host = 'localhost';
$db_name = 'whatscart_db'; // تأكد أن ده اسم قاعدتك
$db_user = 'root';
$db_pass = '';

try {
    // التعديل هنا: أضفنا charset=utf8mb4
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // سطر إضافي لضمان اللغة العربية 100%
    $pdo->exec("set names utf8mb4");
    
} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
?>