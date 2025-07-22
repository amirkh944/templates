<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$query = $_GET['q'] ?? '';
$results = [];
$message = '';

if ($query) {
    $results = searchRequests($query);
    if (empty($results)) {
        $message = '<div class="alert alert-warning mb-6">
                      <i class="fas fa-search"></i>
                      <span>هیچ درخواستی با این کلمه کلیدی یافت نشد.</span>
                    </div>';
    }
}

$pageTitle = 'جستجوی درخواست‌ها - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'جستجوی درخواست‌ها']
];

include 'includes/header.php';

// Get theme parameter
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';
?>
    <!-- Navigation -->
    <nav class="<?php echo $isDark ? 'bg-gray-800 shadow-lg' : 'bg-white shadow-lg'; ?>">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php?theme=<?php echo $theme; ?>" class="text-xl font-bold <?php echo $isDark ? 'text-white' : 'text-gray-800'; ?>">
                        مدیریت درخواست پاسخگو رایانه
                    </a>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <!-- Navigation Menu -->
                    <div class="hidden md:flex space-x-2 space-x-reverse">
                        <a href="dashboard.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <i class="fas fa-home ml-1"></i>داشبورد
                        </a>
                        <a href="new_request.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <i class="fas fa-plus-circle ml-1"></i>درخواست جدید
                        </a>
                        <a href="requests.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <i class="fas fa-list ml-1"></i>لیست درخواست‌ها
                        </a>
                        <a href="customers.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <i class="fas fa-users ml-1"></i>مشتریان
                        </a>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <div class="flex space-x-2">
                        <a href="?theme=light&q=<?php echo urlencode($query); ?>" class="px-3 py-1 rounded <?php echo !$isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>" title="حالت روشن">
                            <i class="fas fa-sun"></i>
                        </a>
                        <a href="?theme=dark&q=<?php echo urlencode($query); ?>" class="px-3 py-1 rounded <?php echo $isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>" title="حالت تیره">
                            <i class="fas fa-moon"></i>
                        </a>
                    </div>
                    
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">خروج</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Search Form -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    <i class="fas fa-search ml-2"></i>
                    جستجوی درخواست‌ها
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    جستجو بر اساس نام مشتری، شماره تلفن، IMEI یا کد رهگیری
                </p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" class="flex gap-4">
                    <input type="hidden" name="theme" value="<?php echo $theme; ?>">
                    <div class="flex-1">
                        <input type="text" name="q" 
                               class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-4 py-2 text-lg"
                               value="<?php echo htmlspecialchars($query); ?>"
                               placeholder="نام مشتری، شماره تلفن، IMEI یا کد رهگیری را وارد کنید..."
                               autofocus>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition duration-200">
                        <i class="fas fa-search ml-2"></i>
                        جستجو
                    </button>
                </form>
                
                <?php if ($query): ?>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-600'; ?>">
                        جستجو برای: <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
                    </span>
                    <a href="?theme=<?php echo $theme; ?>" class="text-sm text-blue-500 hover:text-blue-700">
                        <i class="fas fa-times ml-1"></i>پاک کردن
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php echo $message; ?>

        <!-- Search Results -->
        <?php if ($query && !empty($results)): ?>
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    نتایج جستجو
                </h3>
                <p class="mt-1 text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    <?php echo en2fa(count($results)); ?> درخواست یافت شد
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                کد رهگیری
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                عنوان درخواست
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                مشتری
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                دستگاه
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                IMEI
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                وضعیت
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                تاریخ ثبت
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                عملیات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="<?php echo $isDark ? 'bg-gray-800 divide-gray-600' : 'bg-white divide-gray-200'; ?> divide-y">
                        <?php foreach ($results as $request): ?>
                        <tr class="hover:<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo en2fa($request['tracking_code']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo htmlspecialchars($request['title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <div class="font-medium"><?php echo htmlspecialchars($request['customer_name']); ?></div>
                                <div class="text-gray-500 font-mono text-xs"><?php echo $request['customer_phone']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo $request['device_model'] ?: 'نامشخص'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php if ($request['imei1']): ?>
                                    <div>IMEI1: <?php echo $request['imei1']; ?></div>
                                <?php endif; ?>
                                <?php if ($request['imei2']): ?>
                                    <div>IMEI2: <?php echo $request['imei2']; ?></div>
                                <?php endif; ?>
                                <?php if (!$request['imei1'] && !$request['imei2']): ?>
                                    <span class="text-gray-400">ثبت نشده</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php 
                                    switch($request['status']) {
                                        case 'تکمیل شده': echo 'bg-green-100 text-green-800'; break;
                                        case 'لغو شده': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-500'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d', strtotime($request['created_at']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2 space-x-reverse">
                                    <a href="view_request.php?id=<?php echo $request['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition duration-200" title="مشاهده جزئیات">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_request.php?id=<?php echo $request['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-green-600 hover:text-green-900 transition duration-200" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="print_receipt.php?id=<?php echo $request['id']; ?>" 
                                       class="text-purple-600 hover:text-purple-900 transition duration-200" title="چاپ رسید">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="send_sms.php?request_id=<?php echo $request['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-green-600 hover:text-green-900 transition duration-200" title="ارسال پیامک">
                                        <i class="fas fa-sms"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif (!$query): ?>
        <!-- Search Tips -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?> mb-4">
                    <i class="fas fa-info-circle ml-2 text-blue-500"></i>
                    راهنمای جستجو
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">امکانات جستجو:</h4>
                        <ul class="space-y-2 text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-600'; ?>">
                            <li class="flex items-center">
                                <i class="fas fa-user text-blue-500 ml-2 w-4"></i>
                                جستجو بر اساس نام مشتری
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone text-green-500 ml-2 w-4"></i>
                                جستجو بر اساس شماره تلفن
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-mobile-alt text-purple-500 ml-2 w-4"></i>
                                جستجو بر اساس IMEI دستگاه
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-barcode text-orange-500 ml-2 w-4"></i>
                                جستجو بر اساس کد رهگیری
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">نمونه جستجو:</h4>
                        <ul class="space-y-2 text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-600'; ?>">
                            <li>محمد احمدی</li>
                            <li>۰۹۱۲۳۴۵۶۷۸۹</li>
                            <li>۸۶۱۱۲۳۴۵۶۷۸۹۰۱۲۳</li>
                            <li>ABC1234</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php include 'includes/footer.php'; ?>