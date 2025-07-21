<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

// کنترل تم
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';

$requests = getAllRequests();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت درخواست‌ها - سیستم مدیریت درخواست پاسخگو رایانه</title>
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
        
        /* ردیف جدول */
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            transform: translateX(4px);
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
                    <a href="new_request.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-plus-circle ml-1"></i>درخواست جدید
                    </a>
                    <a href="search_requests.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-search ml-1"></i>جستجو
                    </a>
                    <a href="customers.php?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-users ml-1"></i>مشتریان
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
                <a href="new_request.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-plus-circle ml-2"></i>درخواست جدید
                </a>
                <a href="search_requests.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-search ml-2"></i>جستجو
                </a>
                <a href="customers.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-users ml-2"></i>مشتریان
                </a>
            </div>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- عنوان و اقدامات سریع -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 animate-slide-in">
            <div>
                <h1 class="text-3xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-list ml-3 text-purple-500"></i>
                    مدیریت درخواست‌ها
                </h1>
                <p class="mt-2 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                    مشاهده و مدیریت تمام درخواست‌های ثبت شده
                </p>
            </div>
            
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                <a href="search_requests.php?theme=<?php echo $theme; ?>" 
                   class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors text-center">
                    <i class="fas fa-search ml-2"></i>
                    جستجوی پیشرفته
                </a>
                <a href="new_request.php?theme=<?php echo $theme; ?>" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors text-center">
                    <i class="fas fa-plus-circle ml-2"></i>
                    درخواست جدید
                </a>
            </div>
        </div>

        <!-- آمار سریع -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-in">
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-clipboard-list text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">کل درخواست‌ها</p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>"><?php echo en2fa(count($requests)); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">در انتظار</p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa(count(array_filter($requests, function($r) { return $r['status'] == 'در حال بررسی'; }))); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">تکمیل شده</p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa(count(array_filter($requests, function($r) { return $r['status'] == 'تکمیل شده'; }))); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-times-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">لغو شده</p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa(count(array_filter($requests, function($r) { return $r['status'] == 'لغو شده'; }))); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول درخواست‌ها -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden animate-slide-in">
            <div class="px-6 py-5 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-table ml-2 text-blue-500"></i>
                    لیست درخواست‌ها
                </h3>
                <p class="mt-1 <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-sm">
                    نمایش تمام درخواست‌های ثبت شده به همراه وضعیت و جزئیات
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (empty($requests)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl <?php echo $isDark ? 'text-gray-400' : 'text-gray-300'; ?> mb-4"></i>
                    <h3 class="text-lg font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-2">هیچ درخواستی یافت نشد</h3>
                    <p class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> mb-6">هنوز درخواستی ثبت نشده است</p>
                    <a href="new_request.php?theme=<?php echo $theme; ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus-circle ml-2"></i>
                        اولین درخواست را ثبت کنید
                    </a>
                </div>
                <?php else: ?>
                <table class="min-w-full">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">کد رهگیری</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">عنوان</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">مشتری</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">دستگاه</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">وضعیت</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">تاریخ ایجاد</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                        <?php foreach ($requests as $request): ?>
                        <tr class="table-row hover:<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                    <?php echo en2fa($request['tracking_code']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                    <?php echo htmlspecialchars($request['title']); ?>
                                </div>
                                <div class="text-xs <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                                    <?php echo htmlspecialchars(substr($request['problem_description'], 0, 50)); ?><?php echo strlen($request['problem_description']) > 50 ? '...' : ''; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                    <?php echo htmlspecialchars($request['customer_name']); ?>
                                </div>
                                <div class="text-xs <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> font-mono">
                                    <?php echo $request['customer_phone']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                                    <?php echo htmlspecialchars($request['device_model']); ?>
                                </div>
                                <?php if (!empty($request['imei1'])): ?>
                                <div class="text-xs <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> font-mono">
                                    IMEI: <?php echo en2fa($request['imei1']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d', strtotime($request['created_at']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="view_request.php?id=<?php echo $request['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-blue-600 hover:text-blue-900 text-sm" title="مشاهده جزئیات">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_request.php?id=<?php echo $request['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-green-600 hover:text-green-900 text-sm" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="print_receipt.php?id=<?php echo $request['id']; ?>" 
                                       class="text-purple-600 hover:text-purple-900 text-sm" title="چاپ رسید">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($requests)): ?>
            <!-- پاورقی جدول -->
            <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                        نمایش <?php echo en2fa(count($requests)); ?> درخواست
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="print_all_requests.php" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-print ml-1"></i>
                            چاپ همه
                        </a>
                        <span class="text-gray-300">|</span>
                        <a href="export_requests.php" class="text-sm text-green-600 hover:text-green-800">
                            <i class="fas fa-download ml-1"></i>
                            دانلود Excel
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
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
    </script>
</body>
</html>