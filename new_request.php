<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'sms_config.php';

checkLogin();

$message = '';
$customers = getAllCustomers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerName = $_POST['customer_name'];
    $customerPhone = $_POST['customer_phone'];
    $customerEmail = $_POST['customer_email'] ?? '';
    $existingCustomerId = $_POST['existing_customer'] ?? '';
    
    $title = $_POST['title'];
    $deviceModel = $_POST['device_model'];
    $imei1 = $_POST['imei1'];
    $imei2 = $_POST['imei2'];
    $problemDescription = $_POST['problem_description'];
    $estimatedDuration = $_POST['estimated_duration'];
    $actionsRequired = $_POST['actions_required'];
    $cost = floatval($_POST['cost']);
    
    try {
        // تعیین مشتری
        if ($existingCustomerId) {
            $customerId = $existingCustomerId;
        } else {
            // بررسی وجود مشتری با همین شماره تلفن
            $existingCustomer = getCustomerByPhone($customerPhone);
            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            } else {
                $customerId = createCustomer($customerName, $customerPhone, $customerEmail);
            }
        }
        
        // ایجاد درخواست
        $requestId = createRequest($customerId, $title, $deviceModel, $imei1, $imei2, $problemDescription, $estimatedDuration, $actionsRequired, $cost);
        
        // دریافت کد رهگیری
        $request = getRequest($requestId);
        $trackingCode = $request['tracking_code'];
        
        $message = '<div class="bg-green-50 border border-green-200 text-green-800 px-6 py-5 rounded-xl mb-8">
                      <div class="flex items-center mb-4">
                          <i class="fas fa-check-circle text-green-500 text-2xl ml-3"></i>
                          <div>
                              <h4 class="font-semibold text-lg">درخواست با موفقیت ثبت شد</h4>
                              <p class="text-sm mt-1">کد رهگیری: <span class="font-mono font-bold text-lg">' . en2fa($trackingCode) . '</span></p>
                          </div>
                      </div>
                      <div class="flex flex-wrap gap-3">
                          <a href="print_receipt.php?id=' . $requestId . '" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                              <i class="fas fa-print ml-2"></i>چاپ رسید
                          </a>
                          <a href="send_sms.php?request_id=' . $requestId . '&theme=' . ($_GET['theme'] ?? 'light') . '" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                              <i class="fas fa-sms ml-2"></i>ارسال پیامک
                          </a>
                          <a href="view_request.php?id=' . $requestId . '&theme=' . ($_GET['theme'] ?? 'light') . '" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                              <i class="fas fa-eye ml-2"></i>مشاهده جزئیات
                          </a>
                      </div>
                    </div>';
        
        // پاک کردن فرم
        $_POST = array();
        
    } catch (Exception $e) {
        $message = '<div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6">
                      <div class="flex items-center">
                          <i class="fas fa-exclamation-circle text-red-500 text-xl ml-3"></i>
                          <div>
                              <h4 class="font-semibold">خطا در ثبت درخواست</h4>
                              <p class="text-sm mt-1">' . $e->getMessage() . '</p>
                          </div>
                      </div>
                    </div>';
    }
}

// کنترل تم
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>درخواست جدید - سیستم مدیریت درخواست پاسخگو رایانه</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazir', sans-serif; }
        
        /* تم تیره */
        .dark-bg { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
        .dark-card { background: rgba(30, 41, 59, 0.95); border: 1px solid #475569; }
        .dark-text { color: #e2e8f0; }
        .dark-text-secondary { color: #94a3b8; }
        .dark-input { background: #374151; border: 1px solid #475569; color: #e2e8f0; }
        .dark-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        
        /* تم روشن */
        .light-bg { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); }
        .light-card { background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .light-text { color: #1e293b; }
        .light-text-secondary { color: #64748b; }
        
        /* انیمیشن‌ها */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        
        /* فرم */
        .form-section {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen <?php echo $isDark ? 'dark-bg' : 'light-bg'; ?>">
    
    <!-- نوار ناوبری -->
    <nav class="<?php echo $isDark ? 'bg-gray-800' : 'bg-white'; ?> shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                
                <!-- لوگو و عنوان -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="dashboard.php?theme=<?php echo $theme; ?>" class="text-xl font-bold <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                            <i class="fas fa-desktop ml-2 text-blue-500"></i>
                            پاسخگو رایانه
                        </a>
                    </div>
                </div>
                
                <!-- منوی ناوبری -->
                <div class="hidden md:flex items-center space-x-4 space-x-reverse">
                    <a href="dashboard.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-home ml-1"></i>داشبورد
                    </a>
                    <a href="requests.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-list ml-1"></i>درخواست‌ها
                    </a>
                    <a href="customers.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-users ml-1"></i>مشتریان
                    </a>
                    <a href="contacts.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium bg-blue-500 text-white">
                        <i class="fas fa-user-plus ml-1"></i>مشتری جدید
                    </a>
                </div>
                
                <!-- کنترل‌های راست -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    
                    <!-- تغییر تم -->
                    <div class="flex bg-gray-200 rounded-lg p-1">
                        <a href="?theme=light" 
                           class="px-3 py-1 rounded-md text-sm transition-colors <?php echo !$isDark ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-sun"></i>
                        </a>
                        <a href="?theme=dark" 
                           class="px-3 py-1 rounded-md text-sm transition-colors <?php echo $isDark ? 'bg-gray-800 text-yellow-400 shadow' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-moon"></i>
                        </a>
                    </div>
                    
                    <!-- خروج -->
                    <a href="logout.php" 
                       class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-sign-out-alt ml-1"></i>خروج
                    </a>
                    
                    <!-- منوی موبایل -->
                    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- منوی موبایل -->
        <div id="mobile-menu" class="md:hidden hidden <?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="dashboard.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-home ml-2"></i>داشبورد
                </a>
                <a href="requests.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-list ml-2"></i>درخواست‌ها
                </a>
                <a href="customers.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-users ml-2"></i>مشتریان
                </a>
            </div>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- عنوان صفحه -->
        <div class="mb-8 animate-slide-in">
            <h1 class="text-3xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                <i class="fas fa-plus-circle ml-3 text-blue-500"></i>
                ثبت درخواست جدید
            </h1>
            <p class="mt-2 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                لطفاً اطلاعات کامل درخواست و مشتری را وارد کنید
            </p>
        </div>

        <?php echo $message; ?>

        <!-- فرم ثبت درخواست -->
        <form method="POST" class="space-y-8">
            
            <!-- بخش اطلاعات مشتری -->
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden animate-slide-in form-section">
                <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                    <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                        <i class="fas fa-user ml-2 text-green-500"></i>
                        اطلاعات مشتری
                    </h3>
                    <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                        می‌توانید مشتری موجود را انتخاب کنید یا اطلاعات مشتری جدید را وارد کنید
                    </p>
                </div>
                
                <div class="p-6 space-y-6">
                    
                    <!-- انتخاب مشتری موجود -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                            <i class="fas fa-search ml-1"></i>
                            انتخاب مشتری موجود (اختیاری)
                        </label>
                        <select name="existing_customer" id="existing_customer" 
                                class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2">
                            <option value="">مشتری جدید</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                        data-phone="<?php echo $customer['phone']; ?>"
                                        data-email="<?php echo htmlspecialchars($customer['email']); ?>">
                                    <?php echo htmlspecialchars($customer['name']); ?> - <?php echo $customer['phone']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- اطلاعات مشتری جدید -->
                    <div id="customer-fields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                نام و نام خانوادگی *
                            </label>
                            <input type="text" name="customer_name" required
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="نام کامل مشتری">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                شماره تلفن *
                            </label>
                            <input type="tel" name="customer_phone" required
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="09123456789">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                ایمیل (اختیاری)
                            </label>
                            <input type="email" name="customer_email"
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="example@email.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش اطلاعات دستگاه -->
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden animate-slide-in form-section">
                <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                    <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                        <i class="fas fa-mobile-alt ml-2 text-purple-500"></i>
                        اطلاعات دستگاه
                    </h3>
                    <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                        مشخصات کامل دستگاه و مشکل آن را وارد کنید
                    </p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                عنوان درخواست *
                            </label>
                            <input type="text" name="title" required
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="مثال: تعمیر تاچ">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                مدل دستگاه *
                            </label>
                            <input type="text" name="device_model" required
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="مثال: iPhone 13 Pro">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                IMEI 1
                            </label>
                            <input type="text" name="imei1"
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="۱۵ رقم">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                IMEI 2 (اختیاری)
                            </label>
                            <input type="text" name="imei2"
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="۱۵ رقم">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                            شرح مشکل *
                        </label>
                        <textarea name="problem_description" rows="4" required
                                  class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                  placeholder="مشکل دستگاه را به تفصیل بنویسید..."></textarea>
                    </div>
                </div>
            </div>

            <!-- بخش جزئیات خدمات -->
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden animate-slide-in form-section">
                <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                    <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                        <i class="fas fa-cogs ml-2 text-orange-500"></i>
                        جزئیات خدمات
                    </h3>
                    <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                        اطلاعات تخمینی زمان، اقدامات و هزینه
                    </p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                مدت زمان تخمینی
                            </label>
                            <select name="estimated_duration"
                                    class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2">
                                <option value="کمتر از یک روز">کمتر از یک روز</option>
                                <option value="۱-۲ روز">۱-۲ روز</option>
                                <option value="۳-۵ روز">۳-۵ روز</option>
                                <option value="۶-۱۰ روز">۶-۱۰ روز</option>
                                <option value="بیش از ۱۰ روز">بیش از ۱۰ روز</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                                هزینه تخمینی (تومان)
                            </label>
                            <input type="number" name="cost" step="1000" min="0"
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                   placeholder="۰">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                            اقدامات لازم
                        </label>
                        <textarea name="actions_required" rows="3"
                                  class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-lg px-3 py-2"
                                  placeholder="اقدامات و قطعات مورد نیاز را شرح دهید..."></textarea>
                    </div>
                </div>
            </div>

            <!-- دکمه‌های عملیات -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end animate-slide-in">
                <a href="dashboard.php?theme=<?php echo $theme; ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                    <i class="fas fa-times ml-2"></i>
                    انصراف
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-save ml-2"></i>
                    ثبت درخواست
                </button>
            </div>
        </form>
        
    </main>

    <!-- جاوا اسکریپت -->
    <script>
        // منوی موبایل
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // بستن منوی موبایل در صفحات بزرگ
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('mobile-menu').classList.add('hidden');
            }
        });
        
        // مدیریت انتخاب مشتری موجود
        const existingCustomerSelect = document.getElementById('existing_customer');
        const customerFields = document.getElementById('customer-fields');
        const customerNameInput = document.querySelector('input[name="customer_name"]');
        const customerPhoneInput = document.querySelector('input[name="customer_phone"]');
        const customerEmailInput = document.querySelector('input[name="customer_email"]');
        
        existingCustomerSelect.addEventListener('change', function() {
            if (this.value) {
                // مشتری موجود انتخاب شده
                const selectedOption = this.options[this.selectedIndex];
                customerNameInput.value = selectedOption.dataset.name || '';
                customerPhoneInput.value = selectedOption.dataset.phone || '';
                customerEmailInput.value = selectedOption.dataset.email || '';
                
                // غیرفعال کردن فیلدها
                customerNameInput.disabled = true;
                customerPhoneInput.disabled = true;
                customerEmailInput.disabled = true;
                
                customerFields.style.opacity = '0.6';
            } else {
                // مشتری جدید
                customerNameInput.value = '';
                customerPhoneInput.value = '';
                customerEmailInput.value = '';
                
                // فعال کردن فیلدها
                customerNameInput.disabled = false;
                customerPhoneInput.disabled = false;
                customerEmailInput.disabled = false;
                
                customerFields.style.opacity = '1';
            }
        });
        
        // اعتبارسنجی فرم
        document.querySelector('form').addEventListener('submit', function(e) {
            const existingCustomer = existingCustomerSelect.value;
            const customerName = customerNameInput.value.trim();
            const customerPhone = customerPhoneInput.value.trim();
            
            if (!existingCustomer && (!customerName || !customerPhone)) {
                e.preventDefault();
                alert('لطفاً مشتری موجود را انتخاب کنید یا اطلاعات مشتری جدید را کامل وارد کنید.');
                return false;
            }
            
            // بررسی فرمت شماره تلفن
            if (!existingCustomer && customerPhone && !/^09\d{9}$/.test(customerPhone)) {
                e.preventDefault();
                alert('شماره تلفن باید با 09 شروع شده و 11 رقم باشد.');
                return false;
            }
        });
    </script>
</body>
</html>