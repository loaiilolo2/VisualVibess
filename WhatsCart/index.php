<?php
// index.php - الواجهة الرئيسية للمتجر
require_once 'includes/functions.php';

// 1. جلب البيانات من قاعدة البيانات
// جلب المنتجات النشطة فقط
$stmt = $pdo->query("SELECT * FROM products WHERE active = 1 ORDER BY id DESC");
$products = $stmt->fetchAll();

// جلب الإعدادات العامة
$site_title = getSetting('site_title');
$whatsapp   = getSetting('whatsapp');
$currency   = getSetting('currency');

// 2. تحديد متغيرات اللغة والاتجاه
$current_lang = $_SESSION['lang']; // يتم تحديدها في includes/lang.php
$dir = ($current_lang == 'ar') ? 'rtl' : 'ltr';

// تجهيز نصوص التنبيهات للجافاسكريبت (للترجمة داخل الـ JS)
$js_trans = [
    'empty' => __('empty_cart'),
    'fill'  => ($current_lang == 'ar') ? 'الرجاء إدخال البيانات' : 'Please fill all details',
    'order_title' => ($current_lang == 'ar') ? 'طلب جديد' : 'New Order'
];
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= e($site_title) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50 text-gray-800"
      x-data="app()"
      x-init="
          whatsappNumber = '<?= $whatsapp ?>';
          currencySymbol = '<?= $currency ?>';
          lang = <?= htmlspecialchars(json_encode($js_trans), ENT_QUOTES, 'UTF-8') ?>;
      "
>

    <nav class="bg-white shadow-sm sticky top-0 z-40 fade-in">
        <div class="max-w-4xl mx-auto px-4 py-3 flex justify-between items-center">
            
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-emerald-600 tracking-wide"><?= e($site_title) ?></h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="?lang=<?= $current_lang == 'en' ? 'ar' : 'en' ?>" 
                   class="text-sm font-semibold text-gray-500 hover:text-emerald-600 transition uppercase">
                    <?= $current_lang == 'en' ? 'العربية' : 'English' ?>
                </a>

                <button @click="cartOpen = true" class="relative p-2 rounded-full hover:bg-gray-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="count > 0" x-transition.scale 
                          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-sm"
                          x-text="count">
                    </span>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-4 pb-24 fade-in">
        
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?= __('home') ?></h2>
            <div class="h-1 w-16 bg-emerald-500 mx-auto rounded"></div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach($products as $p): ?>
            <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden flex flex-col h-full relative group">
                
                <div class="relative aspect-square overflow-hidden bg-gray-100">
                    <img src="assets/uploads/<?= e($p['image']) ?>" 
                         alt="<?= e($p['title']) ?>" 
                         loading="lazy"
                         class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="font-bold text-gray-800 mb-1 text-sm md:text-base line-clamp-2"><?= e($p['title']) ?></h3>
                    
                    <div class="mt-auto flex items-center justify-between pt-2">
                        <span class="text-emerald-600 font-bold text-lg"><?= number_format($p['price'], 2) ?> <small class="text-xs"><?= $currency ?></small></span>
                        
                        <button @click="add(<?= $p['id'] ?>, '<?= e($p['title']) ?>', <?= $p['price'] ?>)" 
                                class="bg-gray-900 text-white w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center hover:bg-emerald-600 transition shadow-lg active:scale-95"
                                title="<?= __('add_to_cart') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($products) == 0): ?>
            <div class="text-center py-20 text-gray-400">
                <p>No products found / لا توجد منتجات حالياً</p>
            </div>
        <?php endif; ?>
    </main>

    <div x-show="cartOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" 
             @click="cartOpen = false"
             x-transition.opacity></div>

        <div class="fixed inset-y-0 <?= ($dir == 'rtl') ? 'left-0' : 'right-0' ?> max-w-full flex">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col h-full transform transition ease-in-out duration-300"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="<?= ($dir == 'rtl') ? '-translate-x-full' : 'translate-x-full' ?>"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="<?= ($dir == 'rtl') ? '-translate-x-full' : 'translate-x-full' ?>">
                
                <div class="px-4 py-4 border-b flex justify-between items-center bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span><?= __('cart') ?></span>
                        <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1 rounded-full" x-text="count"></span>
                    </h2>
                    <button @click="cartOpen = false" class="text-gray-400 hover:text-red-500 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-70">
                            <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <p><?= __('empty_cart') ?></p>
                        </div>
                    </template>

                    <ul class="space-y-4">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <li class="flex py-2 border-b border-gray-100 last:border-0 animate-pulse-once">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800" x-text="item.title"></h4>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span x-text="item.price"></span> x <span x-text="item.qty"></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="font-bold text-emerald-600" x-text="(item.price * item.qty).toFixed(2)"></span>
                                    
                                    <div class="flex items-center border rounded-lg bg-gray-50">
                                        <button @click="updateQty(index, -1)" class="px-2 py-1 text-gray-600 hover:text-red-500">-</button>
                                        <span class="px-2 text-sm font-semibold" x-text="item.qty"></span>
                                        <button @click="updateQty(index, 1)" class="px-2 py-1 text-gray-600 hover:text-emerald-500">+</button>
                                    </div>
                                </div>
                                <button @click="remove(index)" class="mr-3 text-red-400 hover:text-red-600 self-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="border-t bg-gray-50 p-4">
                    <div class="flex justify-between items-center mb-4 text-lg font-bold text-gray-800">
                        <span><?= __('total') ?></span>
                        <span x-text="total + ' ' + currencySymbol"></span>
                    </div>

                    <div class="space-y-3 mb-4">
                        <input type="text" x-model="name" placeholder="<?= __('name') ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none transition text-sm">
                        
                        <textarea x-model="address" placeholder="<?= __('address') ?>" rows="2"
                                  class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none transition text-sm"></textarea>
                    </div>

                    <button @click="checkout" 
                            :disabled="cart.length === 0"
                            class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold hover:bg-emerald-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed flex justify-center items-center gap-2 shadow-lg hover:shadow-xl">
                        <span><?= __('checkout') ?></span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" class="whatsapp-float hover:scale-110 transition duration-300">
        <svg style="width: 35px; height: 35px; fill: white;" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
    </a>

    <script src="assets/js/app.js"></script>
</body>
</html>