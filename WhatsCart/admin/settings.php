<?php
// admin/settings.php
require_once '../includes/functions.php';

// التحقق من الصلاحية
if(!isLoggedIn()) redirect('login.php');

$msg = '';
$error = '';

// معالجة حفظ البيانات
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. تحديث الإعدادات العامة
    if(isset($_POST['save_general'])) {
        $title = trim($_POST['site_title']);
        $phone = trim($_POST['whatsapp']);
        $curr  = trim($_POST['currency']);

        if(!empty($title) && !empty($phone)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$title, 'site_title']);
            $stmt->execute([$phone, 'whatsapp']);
            $stmt->execute([$curr, 'currency']);
            $msg = "تم تحديث إعدادات المتجر بنجاح!";
        } else {
            $error = "الرجاء عدم ترك الحقول فارغة.";
        }
    }

    // 2. تحديث بيانات الدخول (الأمان)
    if(isset($_POST['save_security'])) {
        $user = trim($_POST['admin_user']);
        $pass = $_POST['admin_pass'];

        if(!empty($user)) {
            // تحديث اسم المستخدم
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_user'");
            $stmt->execute([$user]);

            // تحديث الباسورد فقط إذا تم كتابة شيء جديد
            if(!empty($pass)) {
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_pass'");
                $stmt->execute([$hashed_pass]);
            }
            $msg = "تم تحديث بيانات الدخول بنجاح!";
        } else {
            $error = "اسم المستخدم لا يمكن أن يكون فارغاً.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعدادات المتجر</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-50">

<div class="flex h-screen overflow-hidden">
    
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-2xl">
        <div class="h-20 flex items-center justify-center border-b border-slate-800">
            <h2 class="text-2xl font-bold text-emerald-400">WhatsCart</h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                الرئيسية
            </a>
            <a href="products.php" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                المنتجات
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-900/20 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                الإعدادات
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-2 text-red-400 hover:text-red-300 transition text-sm font-bold">تسجيل خروج</a>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-6 md:p-10">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-8">إعدادات النظام</h1>

        <?php if(!empty($msg)): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 border border-green-100 flex items-center gap-2 shadow-sm animate-pulse-once">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <?= $msg ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6 border-b pb-4">
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">بيانات المتجر</h2>
                </div>

                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">اسم المتجر</label>
                        <input type="text" name="site_title" value="<?= getSetting('site_title') ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">رقم واتساب (مع كود الدولة)</label>
                        <div class="relative">
                            <span class="absolute top-3.5 left-4 text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413 11.815 11.815 0 00-8.414-3.48C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24z"/></svg>
                            </span>
                            <input type="text" name="whatsapp" value="<?= getSetting('whatsapp') ?>" 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition text-left" dir="ltr">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">مثال: 201000000000 (بدون +)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">رمز العملة</label>
                        <input type="text" name="currency" value="<?= getSetting('currency') ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>

                    <button type="submit" name="save_general" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-200">
                        حفظ التعديلات
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 h-fit">
                <div class="flex items-center gap-3 mb-6 border-b pb-4">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">بيانات الدخول</h2>
                </div>

                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">اسم المستخدم (الأدمن)</label>
                        <input type="text" name="admin_user" value="<?= getSetting('admin_user') ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">كلمة المرور الجديدة</label>
                        <input type="password" name="admin_pass" placeholder="اتركها فارغة إذا لا تريد التغيير" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <button type="submit" name="save_security" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition shadow-lg">
                        تحديث بيانات الدخول
                    </button>
                </form>
            </div>

        </div>
    </main>
</div>

</body>
</html>