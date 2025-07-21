<?php
checkLogin();

$pageTitle = 'پیگیری درخواست - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'پیگیری درخواست']
];
include 'includes/header.php';

$trackingCode = '';
$request = null;
$error = '';
$success = '';

// پردازش فرم جستجو
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trackingCode = trim($_POST['tracking_code']);
    
    if (empty($trackingCode)) {
        $error = 'لطفاً کد رهگیری را وارد کنید';
    } else {
        // جستجوی درخواست با کد رهگیری
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT r.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email
            FROM requests r
            JOIN customers c ON r.customer_id = c.id
            WHERE r.tracking_code = ?
        ");
        $stmt->execute([$trackingCode]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            $error = 'درخواستی با این کد رهگیری یافت نشد';
        } else {
            $success = 'درخواست شما یافت شد';
        }
    }
}
?>

<!-- صفحه پیگیری درخواست -->
<div class="max-w-4xl mx-auto">
    
    <!-- عنوان صفحه -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
            <i class="fas fa-search ml-3 text-blue-500"></i>
            پیگیری درخواست
        </h1>
        <p class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
            با وارد کردن کد رهگیری، اطلاعات درخواست خود را مشاهده کنید
        </p>
    </div>

    <!-- فرم جستجو -->
    <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6 mb-8">
        <form method="POST" class="space-y-4">
            <div>
                <label for="tracking_code" class="block text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">
                    کد رهگیری
                </label>
                <div class="flex gap-3">
                    <input type="text" 
                           id="tracking_code" 
                           name="tracking_code" 
                           value="<?php echo htmlspecialchars($trackingCode); ?>"
                           placeholder="مثال: 1234567"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?php echo $isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-gray-900'; ?>"
                           required>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-search ml-2"></i>
                        جستجو
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- پیام‌های خطا و موفقیت -->
    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle ml-2"></i>
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle ml-2"></i>
        <?php echo $success; ?>
    </div>
    <?php endif; ?>

    <!-- نمایش اطلاعات درخواست -->
    <?php if ($request): ?>
    <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden">
        
        <!-- هدر کارت -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-clipboard-list ml-2"></i>
                        اطلاعات درخواست
                    </h2>
                    <p class="text-blue-100 text-sm mt-1">
                        کد رهگیری: <span class="font-mono font-bold"><?php echo en2fa($request['tracking_code']); ?></span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="print-receipt?id=<?php echo $request['id']; ?>" 
                       class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-print ml-1"></i>
                        چاپ رسید
                    </a>
                    <a href="view-request?id=<?php echo $request['id']; ?>" 
                       class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-eye ml-1"></i>
                        مشاهده جزئیات
                    </a>
                </div>
            </div>
        </div>

        <!-- محتوای کارت -->
        <div class="p-6">
            
            <!-- اطلاعات اصلی -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <!-- اطلاعات درخواست -->
                <div>
                    <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                        <i class="fas fa-info-circle ml-2 text-blue-500"></i>
                        اطلاعات درخواست
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">عنوان:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-medium">
                                <?php echo htmlspecialchars($request['title']); ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">وضعیت:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php 
                                switch($request['status']) {
                                    case 'تکمیل شده':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'لغو شده':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        echo 'bg-yellow-100 text-yellow-800';
                                }
                                ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">تاریخ ثبت:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d H:i', strtotime($request['created_at']))); ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">هزینه:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-bold">
                                <?php echo en2fa(formatNumber($request['cost'])); ?> تومان
                            </p>
                        </div>
                    </div>
                </div>

                <!-- اطلاعات مشتری -->
                <div>
                    <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                        <i class="fas fa-user ml-2 text-green-500"></i>
                        اطلاعات مشتری
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">نام:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-medium">
                                <?php echo htmlspecialchars($request['customer_name']); ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">تلفن:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-mono">
                                <?php echo $request['customer_phone']; ?>
                            </p>
                        </div>
                        <?php if ($request['customer_email']): ?>
                        <div>
                            <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">ایمیل:</span>
                            <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                <?php echo htmlspecialchars($request['customer_email']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- اطلاعات دستگاه -->
            <?php if ($request['device_model'] || $request['imei1'] || $request['imei2']): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                    <i class="fas fa-mobile-alt ml-2 text-purple-500"></i>
                    اطلاعات دستگاه
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php if ($request['device_model']): ?>
                    <div>
                        <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">مدل دستگاه:</span>
                        <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo htmlspecialchars($request['device_model']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($request['imei1']): ?>
                    <div>
                        <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">IMEI 1:</span>
                        <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-mono">
                            <?php echo en2fa($request['imei1']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($request['imei2']): ?>
                    <div>
                        <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">IMEI 2:</span>
                        <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> font-mono">
                            <?php echo en2fa($request['imei2']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- توضیحات مشکل -->
            <?php if ($request['problem_description']): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                    <i class="fas fa-exclamation-triangle ml-2 text-orange-500"></i>
                    توضیحات مشکل
                </h3>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($request['problem_description'])); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- اقدامات مورد نیاز -->
            <?php if ($request['actions_required']): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                    <i class="fas fa-tools ml-2 text-blue-500"></i>
                    اقدامات مورد نیاز
                </h3>
                <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-20 rounded-lg p-4">
                    <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($request['actions_required'])); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- اطلاعات تکمیلی -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if ($request['registration_date']): ?>
                <div>
                    <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">تاریخ ثبت نام:</span>
                    <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                        <?php echo htmlspecialchars($request['registration_date']); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if ($request['estimated_duration']): ?>
                <div>
                    <span class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">مدت زمان تخمینی:</span>
                    <p class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                        <?php echo htmlspecialchars($request['estimated_duration']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- راهنمای استفاده -->
    <?php if (!$request): ?>
    <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
        <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
            <i class="fas fa-info-circle ml-2 text-blue-500"></i>
            راهنمای استفاده
        </h3>
        <div class="space-y-3 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
            <p>• کد رهگیری ۷ رقمی خود را که هنگام ثبت درخواست دریافت کرده‌اید وارد کنید</p>
            <p>• کد رهگیری معمولاً به صورت عددی ۷ رقمی است (مثال: 1234567)</p>
            <p>• در صورت عدم دسترسی به کد رهگیری، با پشتیبانی تماس بگیرید</p>
            <p>• می‌توانید رسید درخواست خود را چاپ کنید</p>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// فوکوس خودکار روی فیلد کد رهگیری
document.addEventListener('DOMContentLoaded', function() {
    const trackingInput = document.getElementById('tracking_code');
    if (trackingInput && !trackingInput.value) {
        trackingInput.focus();
    }
});

// اعتبارسنجی کد رهگیری
document.getElementById('tracking_code').addEventListener('input', function(e) {
    // فقط اعداد مجاز
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // محدود کردن به ۷ رقم
    if (this.value.length > 7) {
        this.value = this.value.slice(0, 7);
    }
});
</script>

<?php include 'includes/footer.php'; ?>