<?php
// کنترل تم
$theme = $_GET['theme'] ?? ($_COOKIE['theme'] ?? 'light');
$isDark = $theme === 'dark';

// تنظیم کوکی تم
if (isset($_GET['theme'])) {
    setcookie('theme', $theme, time() + (86400 * 30), "/");
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'سیستم مدیریت درخواست پاسخگو رایانه'; ?></title>
    
    <!-- DaisyUI & Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Vazir Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        body { 
            font-family: 'Vazir', sans-serif; 
        }
        
        /* RTL Adjustments */
        .dropdown-content {
            direction: rtl;
        }
        
        /* Custom animations */
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Loading animation */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Gradient backgrounds */
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }
        
        .gradient-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
    </style>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'vazir': ['Vazir', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="font-vazir bg-base-100 min-h-screen">
    
    <!-- Navigation -->
    <div class="navbar bg-base-100 shadow-lg sticky top-0 z-50 border-b border-base-300">
        <div class="navbar-start">
            <!-- Mobile menu button -->
            <div class="dropdown lg:hidden">
                <div tabindex="0" role="button" class="btn btn-ghost">
                    <i class="fas fa-bars text-lg"></i>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-lg bg-base-100 rounded-box w-64 border border-base-300">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="text-sm"><i class="fas fa-tachometer-alt ml-2"></i>داشبورد</a></li>
                    <li><a href="new_request.php" class="text-sm"><i class="fas fa-plus-circle ml-2"></i>درخواست جدید</a></li>
                    <li><a href="search_requests.php" class="text-sm"><i class="fas fa-search ml-2"></i>جستجو</a></li>
                    <li><a href="requests.php" class="text-sm"><i class="fas fa-list ml-2"></i>درخواست‌ها</a></li>
                    <li><a href="customers.php" class="text-sm"><i class="fas fa-users ml-2"></i>مشتریان</a></li>
                    <li><a href="payments.php" class="text-sm"><i class="fas fa-credit-card ml-2"></i>مالی</a></li>
                    <li><a href="communications.php" class="text-sm"><i class="fas fa-comments ml-2"></i>ارتباطات</a></li>
                    <?php if ($_SESSION['is_admin'] ?? false): ?>
                    <li><a href="users.php" class="text-sm"><i class="fas fa-user-cog ml-2"></i>کاربران</a></li>
                    <?php endif; ?>
                    <li class="border-t border-base-300 mt-2 pt-2">
                        <a href="logout.php" class="text-error text-sm"><i class="fas fa-sign-out-alt ml-2"></i>خروج</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Logo and title -->
            <div class="flex items-center">
                <div class="avatar placeholder ml-3">
                    <div class="bg-primary text-primary-content rounded-lg w-10">
                        <i class="fas fa-desktop text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="text-lg font-bold text-base-content">
                        پاسخگو رایانه
                    </div>
                    <div class="text-xs text-base-content/70">
                        سامانه مدیریت درخواست
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Desktop menu -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 gap-1">
                <li><a href="dashboard.php" class="btn btn-ghost btn-sm"><i class="fas fa-tachometer-alt ml-1"></i>داشبورد</a></li>
                <li><a href="new_request.php" class="btn btn-ghost btn-sm"><i class="fas fa-plus-circle ml-1"></i>درخواست جدید</a></li>
                <li><a href="search_requests.php" class="btn btn-ghost btn-sm"><i class="fas fa-search ml-1"></i>جستجو</a></li>
                <li><a href="requests.php" class="btn btn-ghost btn-sm"><i class="fas fa-list ml-1"></i>درخواست‌ها</a></li>
                <li><a href="customers.php" class="btn btn-ghost btn-sm"><i class="fas fa-users ml-1"></i>مشتریان</a></li>
                <li><a href="payments.php" class="btn btn-ghost btn-sm"><i class="fas fa-credit-card ml-1"></i>مالی</a></li>
                <li><a href="communications.php" class="btn btn-ghost btn-sm"><i class="fas fa-comments ml-1"></i>ارتباطات</a></li>
                <?php if ($_SESSION['is_admin'] ?? false): ?>
                <li><a href="users.php" class="btn btn-ghost btn-sm"><i class="fas fa-user-cog ml-1"></i>کاربران</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="navbar-end gap-2">
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User info -->
            <div class="hidden md:flex items-center ml-4">
                <div class="text-sm text-base-content/70">
                    خوش آمدید، <span class="font-semibold text-base-content"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
            
            <!-- Theme toggle -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                    <i class="fas fa-palette"></i>
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-32 border border-base-300">
                    <li><a href="?theme=light" class="text-sm"><i class="fas fa-sun ml-2"></i>روشن</a></li>
                    <li><a href="?theme=dark" class="text-sm"><i class="fas fa-moon ml-2"></i>تیره</a></li>
                    <li><a href="?theme=cupcake" class="text-sm"><i class="fas fa-heart ml-2"></i>کاپ کیک</a></li>
                    <li><a href="?theme=corporate" class="text-sm"><i class="fas fa-building ml-2"></i>اداری</a></li>
                    <li><a href="?theme=synthwave" class="text-sm"><i class="fas fa-wave-square ml-2"></i>سینت ویو</a></li>
                </ul>
            </div>
            
            <!-- User menu -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-8 rounded-full bg-primary text-primary-content flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <ul tabindex="0" class="mt-3 z-[1] p-2 shadow-lg menu menu-sm dropdown-content bg-base-100 rounded-box w-52 border border-base-300">
                    <li><a href="profile.php" class="text-sm"><i class="fas fa-user-edit ml-2"></i>ویرایش پروفایل</a></li>
                    <li><a href="settings.php" class="text-sm"><i class="fas fa-cog ml-2"></i>تنظیمات</a></li>
                    <li class="border-t border-base-300 mt-1 pt-1">
                        <a href="logout.php" class="text-error text-sm"><i class="fas fa-sign-out-alt ml-2"></i>خروج</a>
                    </li>
                </ul>
            </div>
            <?php else: ?>
            <!-- Public tracking link -->
            <a href="public/customer/track.php" class="btn btn-primary btn-sm">
                <i class="fas fa-search ml-1"></i>
                پیگیری درخواست
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Breadcrumb -->
    <div class="bg-base-200 px-4 py-2">
        <div class="breadcrumbs text-sm max-w-7xl mx-auto">
            <ul>
                <li><a href="dashboard.php" class="text-primary"><i class="fas fa-home ml-1"></i>خانه</a></li>
                <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <li><a href="<?php echo $breadcrumb['url']; ?>" class="text-primary"><?php echo $breadcrumb['title']; ?></a></li>
                        <?php else: ?>
                            <li class="text-base-content/70"><?php echo $breadcrumb['title']; ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main content container -->
    <main class="container mx-auto px-4 py-6 max-w-7xl">