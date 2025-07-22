<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'sms_config.php';

checkLogin();

$requestId = $_GET['request_id'] ?? 0;
$message = '';

// دریافت اطلاعات درخواست
if ($requestId) {
    $request = getRequest($requestId);
    if (!$request) {
        header('Location: dashboard.php');
        exit;
    }
    $customer = getCustomer($request['customer_id']);
} else {
    header('Location: dashboard.php');
    exit;
}

// پردازش ارسال پیامک
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $smsMessage = $_POST['sms_message'] ?? '';
    $usePattern = $_POST['use_pattern'] ?? 'no';
    
    if (!empty($smsMessage)) {
        try {
            if ($usePattern === 'yes' && SMS_PATTERN_NEW_REQUEST) {
                // ارسال با الگو
                $smsResult = sendNewRequestSMS($customer['phone'], $request['tracking_code'], $request['title']);
            } else {
                // ارسال متن دلخواه
                $smsResult = sendSMSUpdated($customer['phone'], $smsMessage);
            }
            
            if ($smsResult['success']) {
                $message = '<div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6">
                              <div class="flex items-center">
                                  <i class="fas fa-check-circle text-green-500 text-xl ml-3"></i>
                                  <div>
                                      <h4 class="font-semibold">ارسال موفق</h4>
                                      <p class="text-sm mt-1">پیامک با موفقیت ارسال شد به شماره ' . $customer['phone'] . '</p>
                                  </div>
                              </div>
                            </div>';
            } else {
                $message = '<div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6">
                              <div class="flex items-center">
                                  <i class="fas fa-exclamation-circle text-red-500 text-xl ml-3"></i>
                                  <div>
                                      <h4 class="font-semibold">خطا در ارسال</h4>
                                      <p class="text-sm mt-1">' . $smsResult['message'] . '</p>
                                  </div>
                              </div>
                            </div>';
            }
        } catch (Exception $e) {
            $message = '<div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6">
                          <div class="flex items-center">
                              <i class="fas fa-exclamation-triangle text-red-500 text-xl ml-3"></i>
                              <div>
                                  <h4 class="font-semibold">خطای سیستمی</h4>
                                  <p class="text-sm mt-1">' . $e->getMessage() . '</p>
                              </div>
                          </div>
                        </div>';
        }
    } else {
        $message = '<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-xl mb-6">
                      <div class="flex items-center">
                          <i class="fas fa-exclamation-triangle text-yellow-500 text-xl ml-3"></i>
                          <div>
                              <h4 class="font-semibold">ورودی ناکامل</h4>
                              <p class="text-sm mt-1">لطفاً متن پیامک را وارد کنید</p>
                          </div>
                      </div>
                    </div>';
    }
}

// متن‌های پیش‌فرض
$defaultMessages = [
    'new_request' => "درخواست شما با کد رهگیری {$request['tracking_code']} ثبت شد. عنوان: {$request['title']} - پاسخگو رایانه",
    'status_update' => "وضعیت درخواست {$request['tracking_code']} به {$request['status']} تغییر یافت. پاسخگو رایانه",
    'reminder' => "یادآوری: درخواست شما با کد {$request['tracking_code']} در دست بررسی است. پاسخگو رایانه",
    'completion' => "درخواست شما با کد {$request['tracking_code']} تکمیل شد. لطفاً جهت تحویل مراجعه فرمایید. پاسخگو رایانه"
];

$pageTitle = 'ارسال پیامک - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'درخواست‌ها', 'url' => 'requests.php'],
    ['title' => 'ارسال پیامک']
];

include 'includes/header.php';
?>
    
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
                    <a href="view_request.php?id=<?php echo $requestId; ?>&theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium bg-blue-500 text-white rounded-md">
                        <i class="fas fa-eye ml-1"></i>مشاهده درخواست
                    </a>
                </div>
                
                <!-- کنترل‌های راست -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    
                    <!-- تغییر تم -->
                    <div class="flex bg-gray-200 rounded-lg p-1">
                        <a href="?request_id=<?php echo $requestId; ?>&theme=light" 
                           class="px-3 py-1 rounded-md text-sm transition-colors <?php echo !$isDark ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-sun"></i>
                        </a>
                        <a href="?request_id=<?php echo $requestId; ?>&theme=dark" 
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
                <a href="view_request.php?id=<?php echo $requestId; ?>&theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-eye ml-2"></i>مشاهده درخواست
                </a>
            </div>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- اطلاعات درخواست -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden mb-8 animate-slide-in">
            <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                <h2 class="text-xl font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-sms ml-2 text-green-500"></i>
                    ارسال پیامک اطلاع‌رسانی
                </h2>
                <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                    ارسال پیامک برای اطلاع‌رسانی به مشتری درباره وضعیت درخواست
                </p>
            </div>
            
            <!-- جزئیات درخواست -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> block mb-1">کد رهگیری</label>
                            <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-3 py-2 rounded-lg">
                                <span class="font-mono text-lg <?php echo $isDark ? 'dark-text' : 'light-text'; ?>"><?php echo en2fa($request['tracking_code']); ?></span>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> block mb-1">عنوان درخواست</label>
                            <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-3 py-2 rounded-lg">
                                <span class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>"><?php echo htmlspecialchars($request['title']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> block mb-1">مشتری</label>
                            <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-3 py-2 rounded-lg">
                                <div class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-medium"><?php echo htmlspecialchars($customer['name']); ?></div>
                                <div class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> font-mono text-sm"><?php echo $customer['phone']; ?></div>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> block mb-1">وضعیت</label>
                            <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-3 py-2 rounded-lg">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    <?php 
                                    switch($request['status']) {
                                        case 'تکمیل شده': echo 'bg-green-100 text-green-800'; break;
                                        case 'لغو شده': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $message; ?>

        <!-- هشدار غیرفعال بودن سرویس -->
        <?php if (!isSMSEnabled()): ?>
        <div class="bg-orange-50 border border-orange-200 text-orange-800 px-6 py-4 rounded-xl mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-500 text-xl ml-3"></i>
                <div>
                    <h4 class="font-semibold">سرویس پیامک غیرفعال</h4>
                    <p class="text-sm mt-1">
                        سرویس پیامک غیرفعال است یا تنظیمات ناقص است. برای فعال‌سازی، فایل <code class="bg-orange-100 px-1 rounded">sms_config.php</code> را بررسی کنید.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- قالب‌های آماده -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden mb-8 animate-slide-in">
            <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-templates ml-2 text-blue-500"></i>
                    قالب‌های آماده پیامک
                </h3>
                <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                    روی هر قالب کلیک کنید تا متن آن در فرم پایین قرار گیرد
                </p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php 
                    $templates = [
                        'new_request' => ['title' => 'اطلاع ثبت درخواست', 'icon' => 'fas fa-plus-circle', 'color' => 'blue'],
                        'status_update' => ['title' => 'به‌روزرسانی وضعیت', 'icon' => 'fas fa-sync-alt', 'color' => 'green'],
                        'reminder' => ['title' => 'یادآوری', 'icon' => 'fas fa-bell', 'color' => 'yellow'],
                        'completion' => ['title' => 'اطلاع تکمیل', 'icon' => 'fas fa-check-circle', 'color' => 'purple']
                    ];
                    
                    foreach ($templates as $key => $template): 
                    ?>
                    <div class="message-template <?php echo $isDark ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-200'; ?> border rounded-xl p-4" 
                         onclick="setMessage('<?php echo addslashes($defaultMessages[$key]); ?>')">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="<?php echo $template['icon']; ?> text-<?php echo $template['color']; ?>-500 text-lg ml-2"></i>
                                <h4 class="font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                    <?php echo $template['title']; ?>
                                </h4>
                            </div>
                            <i class="fas fa-hand-pointer text-blue-500 text-sm"></i>
                        </div>
                        <p class="text-sm <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> leading-relaxed">
                            <?php echo htmlspecialchars(substr($defaultMessages[$key], 0, 100)) . (strlen($defaultMessages[$key]) > 100 ? '...' : ''); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- فرم ارسال پیامک -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden animate-slide-in">
            <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-edit ml-2 text-purple-500"></i>
                    تنظیمات و ارسال پیامک
                </h3>
                <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                    متن پیامک خود را تایپ کنید یا از قالب‌های آماده استفاده کنید
                </p>
            </div>
            
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    
                    <!-- ناحیه متن پیامک -->
                    <div>
                        <label for="sms_message" class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-3">
                            <i class="fas fa-comment-alt ml-1"></i>
                            متن پیامک *
                        </label>
                        <div class="relative">
                            <textarea name="sms_message" id="sms_message" rows="5" required
                                      class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-xl px-4 py-3 text-sm"
                                      placeholder="متن پیامک خود را اینجا بنویسید..."
                                      maxlength="160"><?php echo $_POST['sms_message'] ?? $defaultMessages['new_request']; ?></textarea>
                            <div class="absolute bottom-3 left-3">
                                <span id="charCount" class="text-xs <?php echo $isDark ? 'text-gray-400' : 'text-gray-500'; ?> bg-white px-2 py-1 rounded-full">
                                    0/160
                                </span>
                            </div>
                        </div>
                        <p class="text-xs <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> mt-2">
                            <i class="fas fa-info-circle ml-1"></i>
                            حداکثر ۱۶۰ کاراکتر برای یک پیامک استاندارد
                        </p>
                    </div>

                    <?php if (SMS_PATTERN_NEW_REQUEST): ?>
                    <!-- گزینه استفاده از الگو -->
                    <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-blue-50'; ?> p-4 rounded-xl">
                        <div class="flex items-center">
                            <input type="checkbox" name="use_pattern" value="yes" id="use_pattern"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="use_pattern" class="mr-3 text-sm <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                <i class="fas fa-magic ml-1"></i>
                                استفاده از الگوی از پیش تعریف شده (اگر متن بالا را تغییر ندهید)
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- اطلاعات گیرنده و دکمه‌ها -->
                    <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> p-4 rounded-xl">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center">
                                <i class="fas fa-mobile-alt text-green-500 text-lg ml-2"></i>
                                <div>
                                    <span class="text-sm <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">ارسال به:</span>
                                    <div class="font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>"><?php echo $customer['phone']; ?></div>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <a href="view_request.php?id=<?php echo $requestId; ?>&theme=<?php echo $theme; ?>" 
                                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                    <i class="fas fa-arrow-right ml-2"></i>
                                    بازگشت
                                </a>
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors text-sm font-medium <?php echo !isSMSEnabled() ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        <?php echo !isSMSEnabled() ? 'disabled title="سرویس پیامک غیرفعال است"' : ''; ?>>
                                    <i class="fas fa-paper-plane ml-2"></i>
                                    ارسال پیامک
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </main>

    <!-- جاوا اسکریپت -->
    <script>
        // منوی موبایل
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // شمارشگر کاراکتر
        const messageTextarea = document.getElementById('sms_message');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            const length = messageTextarea.value.length;
            charCount.textContent = length + '/160';
            
            if (length > 160) {
                charCount.classList.add('text-red-500', 'bg-red-100');
                charCount.classList.remove('<?php echo $isDark ? "text-gray-400" : "text-gray-500"; ?>', 'bg-white');
            } else if (length > 140) {
                charCount.classList.add('text-yellow-600', 'bg-yellow-100');
                charCount.classList.remove('<?php echo $isDark ? "text-gray-400" : "text-gray-500"; ?>', 'bg-white', 'text-red-500', 'bg-red-100');
            } else {
                charCount.classList.remove('text-red-500', 'bg-red-100', 'text-yellow-600', 'bg-yellow-100');
                charCount.classList.add('<?php echo $isDark ? "text-gray-400" : "text-gray-500"; ?>', 'bg-white');
            }
        }
        
        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // شمارش اولیه
        
        // تنظیم متن از قالب
        function setMessage(message) {
            messageTextarea.value = message;
            updateCharCount();
            messageTextarea.focus();
            
            // انیمیشن کوتاه برای نشان دادن تغییر
            messageTextarea.style.transform = 'scale(1.02)';
            setTimeout(() => {
                messageTextarea.style.transform = 'scale(1)';
            }, 200);
        }
        
        // بستن منوی موبایل در صفحات بزرگ
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('mobile-menu').classList.add('hidden');
            }
        });
    </script>
</body>
</html>