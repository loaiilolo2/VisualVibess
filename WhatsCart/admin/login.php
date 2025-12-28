<?php
require_once '../includes/functions.php';
if(isLoggedIn()) redirect('index.php');

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    
    // جلب البيانات من القاعدة
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_user'");
    $stmt->execute();
    $db_user = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_pass'");
    $stmt->execute();
    $db_pass = $stmt->fetchColumn();

    if($user === $db_user && password_verify($pass, $db_pass)) {
        $_SESSION['admin_logged_in'] = true;
        redirect('index.php');
    } else {
        $error = "بيانات الدخول غير صحيحة";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول المدير</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    
    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">لوحة التحكم</h1>
            <p class="text-gray-500 text-sm">قم بإدخال بياناتك للمتابعة</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm text-center border border-red-100">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">اسم المستخدم</label>
                <input name="user" type="text" required 
                       class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-200 outline-none transition duration-200"
                       placeholder="Admin Username">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                <input name="pass" type="password" required 
                       class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-200 outline-none transition duration-200"
                       placeholder="••••••••">
            </div>

            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition duration-300 shadow-lg shadow-emerald-200">
                تسجيل الدخول
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; <?= date('Y') ?> WhatsCart Admin Panel
        </div>
    </div>

</body>
</html>