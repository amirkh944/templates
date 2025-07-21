<?php
// کنترل تم
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'سیستم مدیریت درخواست پاسخگو رایانه'; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* کارت‌های آمار */
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* ویجت‌های دسترسی سریع */
        .quick-access-item {
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .quick-access-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        /* نمودارها */
        .chart-container {
            position: relative;
            height: 350px;
        }
        
        /* ریسپانسیو */
        @media (max-width: 768px) {
            .chart-container { height: 250px; }
        }
    </style>
</head>
<body class="min-h-screen <?php echo $isDark ? 'dark-bg' : 'light-bg'; ?>">
    
    <!-- نوار ناوبری -->
    <nav class="<?php echo $isDark ? 'bg-gray-800' : 'bg-white'; ?> shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative flex h-16 items-center justify-between">
               
                <!-- لوگو و عنوان -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                            <i class="fas fa-desktop ml-2 text-blue-500"></i>
                            سامانه مدیریت درخواست
                            <span class="bg-blue-100 text-blue-800 text-sm font-semibold me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-200 dark:text-blue-800 ms-2">پاسخگو رایانه</span>
                        </h1>
                    </div>
                </div>
                
                <!-- منوی دسکتاپ -->
                <div class="hidden md:flex items-center space-x-4 space-x-reverse">
                    
                    <a href="new-request?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-plus-circle ml-1"></i>درخواست جدید
                    </a>

                    <a href="search-requests?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-search ml-1"></i>جستجو
                    </a>
                    <a href="requests?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-list ml-1"></i>درخواست‌ها
                    </a>
                    <a href="customers?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-users ml-1"></i>مشتریان
                    </a>
                    <a href="payments?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-credit-card ml-1"></i>مالی
                    </a>
                    <a href="communications?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-comments ml-1"></i>ارتباطات
                    </a>
                    <?php if ($_SESSION['is_admin'] ?? false): ?>
                    <a href="users?theme=<?php echo $theme; ?>" 
                       class="px-3 py-2 rounded-md text-sm font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-user-cog ml-1"></i>کاربران
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- کنترل‌های راست -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    
                    <!-- نام کاربر -->
                    <span class="<?php echo $isDark ? 'text-gray-300' : 'text-gray-700'; ?> text-sm">
                        خوش آمدید، <span class="font-semibold"><?php echo $_SESSION['username']; ?></span>
                    </span>
                    
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
                    <a href="logout" 
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
                <a href="new-request?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-plus-circle ml-2"></i>درخواست جدید
                </a>
                <a href="track-request?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-search ml-2"></i>پیگیری
                </a>
                <a href="search-requests?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-search ml-2"></i>جستجو
                </a>
                <a href="requests?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-list ml-2"></i>درخواست‌ها
                </a>
                <a href="customers?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-users ml-2"></i>مشتریان
                </a>
                <a href="payments?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-credit-card ml-2"></i>مالی
                </a>
                <a href="communications?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-comments ml-2"></i>ارتباطات
                </a>
                <?php if ($_SESSION['is_admin'] ?? false): ?>
                <a href="users?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-user-cog ml-2"></i>کاربران
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <script>
            // کنترل منوی موبایل
            document.getElementById('mobile-menu-btn').addEventListener('click', function() {
                const mobileMenu = document.getElementById('mobile-menu');
                mobileMenu.classList.toggle('hidden');
            });
        </script>