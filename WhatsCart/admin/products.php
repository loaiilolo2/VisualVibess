<?php
// admin/products.php
require_once '../includes/functions.php';

// التحقق من صلاحية المدير
if(!isLoggedIn()) redirect('login.php');

$error = '';
$success = '';

// -----------------------------------------------------------
// 1. معالجة طلب إضافة منتج (مع حماية رفع الصور)
// -----------------------------------------------------------
if(isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $price = $_POST['price'];
    
    // إعدادات رفع الصورة
    $imgName = 'default.png'; // الصورة الافتراضية
    $uploadError = false;

    // هل قام المدير برفع ملف؟
    if(!empty($_FILES['image']['name'])) {
        $fileName = $_FILES['image']['name'];
        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // قائمة الامتدادات المسموحة فقط
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        // التحقق من الامتداد
        if(in_array($fileExt, $allowedExt)) {
            // التحقق من محتوى الملف (MIME Type) لمنع الملفات الخبيثة
            if(getimagesize($fileTmp)) {
                // إنشاء اسم عشوائي للصورة
                $newFileName = uniqid('prod_', true) . '.' . $fileExt;
                $destination = "../assets/uploads/" . $newFileName;

                if(move_uploaded_file($fileTmp, $destination)) {
                    $imgName = $newFileName;
                } else {
                    $error = "فشل نقل الصورة إلى السيرفر. تأكد من صلاحيات مجلد uploads.";
                    $uploadError = true;
                }
            } else {
                $error = "الملف المرفوع تالف أو ليس صورة صالحة.";
                $uploadError = true;
            }
        } else {
            $error = "نوع الملف غير مدعوم. المسموح فقط: JPG, PNG, WEBP";
            $uploadError = true;
        }
    }

    // الحفظ في قاعدة البيانات إذا لم يكن هناك خطأ في الرفع
    if(!$uploadError) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (title, price, image) VALUES (?, ?, ?)");
            $stmt->execute([$title, $price, $imgName]);
            $success = "تم إضافة المنتج بنجاح!";
        } catch(PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}

// -----------------------------------------------------------
// 2. معالجة طلب حذف منتج
// -----------------------------------------------------------
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // جلب بيانات المنتج لحذف صورته
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if($product) {
        // حذف الصورة من السيرفر (إذا لم تكن هي الصورة الافتراضية)
        if($product['image'] !== 'default.png' && file_exists("../assets/uploads/" . $product['image'])) {
            unlink("../assets/uploads/" . $product['image']);
        }

        // حذف السجل من قاعدة البيانات
        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$id]);
        
        // إعادة تحميل الصفحة لتحديث القائمة
        redirect('products.php');
    }
}

// -----------------------------------------------------------
// 3. جلب كافة المنتجات للعرض
// -----------------------------------------------------------
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المنتجات</title>
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
            <a href="products.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-900/20 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                المنتجات
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                الإعدادات
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-2 text-red-400 hover:text-red-300 transition text-sm font-bold">تسجيل خروج</a>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-6 md:p-10">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">المنتجات</h1>
                <p class="text-gray-500 text-sm mt-1">إدارة منتجات المتجر وإضافة صورها</p>
            </div>
            <button onclick="document.getElementById('addProductForm').classList.toggle('hidden')" 
                    class="bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2 w-full md:w-auto justify-center">
                <span>+ إضافة منتج جديد</span>
            </button>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 border border-green-100 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <div id="addProductForm" class="hidden bg-white p-6 rounded-2xl shadow-lg border border-gray-100 mb-8 transition-all duration-300 animate-fade-in">
            <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">بيانات المنتج الجديد</h3>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <div class="md:col-span-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">اسم المنتج</label>
                    <input type="text" name="title" required placeholder="مثال: سماعة بلوتوث"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50 transition">
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">السعر</label>
                    <input type="number" step="0.01" name="price" required placeholder="0.00"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50 transition">
                </div>
                
                <div class="md:col-span-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">الصورة (JPG, PNG)</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                           class="block w-full text-sm text-gray-500 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                </div>
                
                <div class="md:col-span-12 mt-2">
                    <button type="submit" name="add" class="w-full bg-emerald-600 text-white py-3 rounded-lg font-bold hover:bg-emerald-700 transition shadow-md">
                        حفظ المنتج
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-right">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="p-5 text-sm font-bold text-gray-500">الصورة</th>
                        <th class="p-5 text-sm font-bold text-gray-500">اسم المنتج</th>
                        <th class="p-5 text-sm font-bold text-gray-500">السعر</th>
                        <th class="p-5 text-sm font-bold text-gray-500">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(count($products) > 0): ?>
                        <?php foreach($products as $p): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="p-5">
                                <div class="w-14 h-14 rounded-xl bg-gray-100 overflow-hidden border border-gray-200">
                                    <img src="../assets/uploads/<?= e($p['image']) ?>" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="p-5 font-bold text-gray-800"><?= e($p['title']) ?></td>
                            <td class="p-5 text-emerald-600 font-bold text-lg"><?= number_format($p['price'], 2) ?></td>
                            <td class="p-5">
                                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذا المنتج؟ سيتم حذف الصورة أيضاً.')" 
                                   class="text-red-500 hover:text-red-700 hover:bg-red-50 px-4 py-2 rounded-lg transition text-sm font-bold flex items-center gap-1 w-fit">
                                   <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    حذف
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-10 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                لا توجد منتجات حالياً. ابدأ بإضافة منتجك الأول من الزر بالأعلى.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<style>
    /* أنيميشن بسيط لظهور النموذج */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
</style>

</body>
</html>