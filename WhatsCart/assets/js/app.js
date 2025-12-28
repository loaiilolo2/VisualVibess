document.addEventListener('alpine:init', () => {
    Alpine.data('app', () => ({
        cartOpen: false,
        cart: JSON.parse(localStorage.getItem('cart') || '[]'),
        name: localStorage.getItem('customer_name') || '',
        address: localStorage.getItem('customer_address') || '',
        whatsappNumber: '', // سيتم تمريرها من الـ HTML
        currencySymbol: '$', // سيتم تمريرها من الـ HTML

        // التهيئة الأولية
        init() {
            this.$watch('cart', () => {
                this.save();
            });
        },

        // حساب عدد العناصر
        get count() { 
            return this.cart.reduce((a, b) => a + b.qty, 0); 
        },

        // حساب الإجمالي
        get total() { 
            return this.cart.reduce((a, b) => a + (b.price * b.qty), 0).toFixed(2); 
        },

        // إضافة للسلة
        add(id, title, price) {
            let item = this.cart.find(i => i.id === id);
            if (item) {
                item.qty++;
            } else {
                this.cart.push({ id: id, title: title, price: price, qty: 1 });
            }
            // إظهار إشعار صغير (Toast) - اختياري
            this.showToast(title);
        },

        // حذف عنصر
        remove(idx) {
            this.cart.splice(idx, 1);
        },

        // زيادة ونقصان الكمية داخل السلة
        updateQty(idx, val) {
            this.cart[idx].qty += val;
            if (this.cart[idx].qty < 1) this.cart.splice(idx, 1);
        },

        // حفظ البيانات
        save() { 
            localStorage.setItem('cart', JSON.stringify(this.cart));
            localStorage.setItem('customer_name', this.name);
            localStorage.setItem('customer_address', this.address);
        },

        // إرسال الطلب
        checkout() {
            if (this.cart.length === 0) {
                alert('السلة فارغة / Cart is empty');
                return;
            }
            if (!this.name || !this.address) {
                alert('الرجاء إدخال الاسم والعنوان / Please fill all details');
                return;
            }

            let msg = `*New Order / طلب جديد*\n`;
            msg += `👤 Name: ${this.name}\n`;
            msg += `📍 Address: ${this.address}\n`;
            msg += `------------------------\n`;
            
            this.cart.forEach(i => {
                msg += `▫️ ${i.title} (x${i.qty}) - ${i.price * i.qty}\n`;
            });
            
            msg += `------------------------\n`;
            msg += `*💰 Total: ${this.total} ${this.currencySymbol}*`;

            let url = `https://wa.me/${this.whatsappNumber}?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
            
            // تفريغ السلة بعد الطلب (اختياري، يفضل عدم التفريغ ليتأكد العميل)
            // this.cart = []; 
            this.cartOpen = false;
        },

        // دالة مساعدة للإشعارات (Toasts)
        showToast(productName) {
            // يمكن تطويرها لإظهار رسالة منبثقة
            console.log("Added: " + productName);
        }
    }))
});