<?php
// includes/lang.php
session_start();

// تحديد اللغة الافتراضية
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default Language
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$translations = [
    'en' => [
        'home' => 'Home',
        'cart' => 'Cart',
        'add_to_cart' => 'Add to Cart',
        'total' => 'Total',
        'checkout' => 'Checkout via WhatsApp',
        'empty_cart' => 'Your cart is empty',
        'name' => 'Full Name',
        'address' => 'Address / Notes',
        'admin_login' => 'Admin Login',
        'dashboard' => 'Dashboard',
        'products' => 'Products',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'save' => 'Save Changes',
        'price' => 'Price',
        'currency' => 'USD',
        'dir' => 'ltr'
    ],
    'ar' => [
        'home' => 'الرئيسية',
        'cart' => 'السلة',
        'add_to_cart' => 'أضف للسلة',
        'total' => 'الإجمالي',
        'checkout' => 'إتمام الطلب عبر واتساب',
        'empty_cart' => 'سلة المشتريات فارغة',
        'name' => 'الاسم بالكامل',
        'address' => 'العنوان والملاحظات',
        'admin_login' => 'دخول المشرف',
        'dashboard' => 'لوحة التحكم',
        'products' => 'المنتجات',
        'settings' => 'الإعدادات',
        'logout' => 'خروج',
        'save' => 'حفظ التغييرات',
        'price' => 'السعر',
        'currency' => 'ريال',
        'dir' => 'rtl'
    ]
];

function __($key) {
    global $translations;
    $lang = $_SESSION['lang'];
    return $translations[$lang][$key] ?? $key;
}
?>