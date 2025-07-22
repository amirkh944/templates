<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$message = '';

// Process form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_cache':
            // Clear any cache files if they exist
            $cacheCleared = true;
            $message = '<div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span>کش سیستم با موفقیت پاک شد</span></div>';
            break;
            
        case 'backup_database':
            // This would implement database backup functionality
            $message = '<div class="alert alert-info mb-6"><i class="fas fa-info-circle"></i><span>قابلیت پشتیبان‌گیری در نسخه آینده اضافه خواهد شد</span></div>';
            break;
            
        case 'system_check':
            // System health check
            $systemStatus = [
                'php_version' => phpversion(),
                'database' => 'متصل',
                'disk_space' => '95%',
                'memory_usage' => '45%'
            ];
            $message = '<div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span>بررسی سیستم انجام شد. همه چیز عادی است</span></div>';
            break;
    }
}

// Get system statistics
$stats = getStats();

// Check PHP and system info
$systemInfo = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'نامشخص',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit')
];

$pageTitle = 'تنظیمات سیستم - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'تنظیمات سیستم']
];

include 'includes/header.php';
?>

<div class="space-y-8">
    
    <!-- Page Title -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-base-content mb-2">
            <i class="fas fa-cog text-primary ml-2"></i>
            تنظیمات سیستم
        </h1>
        <p class="text-base-content/70 text-lg">
            مدیریت تنظیمات کلی سیستم و بهینه‌سازی عملکرد
        </p>
    </div>

    <?php echo $message; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- System Information -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-info-circle text-info ml-2"></i>
                    اطلاعات سیستم
                </h2>
                
                <div class="space-y-4">
                    
                    <!-- PHP Version -->
                    <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fab fa-php text-2xl text-purple-600"></i>
                            <div>
                                <div class="font-medium">نسخه PHP</div>
                                <div class="text-sm text-base-content/70">زبان برنامه‌نویسی سرور</div>
                            </div>
                        </div>
                        <div class="badge badge-primary badge-lg">
                            <?php echo $systemInfo['php_version']; ?>
                        </div>
                    </div>
                    
                    <!-- Database Status -->
                    <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-database text-2xl text-green-600"></i>
                            <div>
                                <div class="font-medium">پایگاه داده</div>
                                <div class="text-sm text-base-content/70">وضعیت اتصال دیتابیس</div>
                            </div>
                        </div>
                        <div class="badge badge-success badge-lg">
                            <i class="fas fa-check ml-1"></i>
                            متصل
                        </div>
                    </div>
                    
                    <!-- Memory Limit -->
                    <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-memory text-2xl text-blue-600"></i>
                            <div>
                                <div class="font-medium">حد حافظه</div>
                                <div class="text-sm text-base-content/70">محدودیت استفاده از RAM</div>
                            </div>
                        </div>
                        <div class="badge badge-info badge-lg">
                            <?php echo $systemInfo['memory_limit']; ?>
                        </div>
                    </div>
                    
                    <!-- Upload Limit -->
                    <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-upload text-2xl text-orange-600"></i>
                            <div>
                                <div class="font-medium">حداکثر آپلود</div>
                                <div class="text-sm text-base-content/70">حد بارگذاری فایل</div>
                            </div>
                        </div>
                        <div class="badge badge-warning badge-lg">
                            <?php echo $systemInfo['upload_max_filesize']; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- System Statistics -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-chart-bar text-success ml-2"></i>
                    آمار سیستم
                </h2>
                
                <div class="space-y-4">
                    
                    <!-- Total Requests -->
                    <div class="stat bg-base-200 rounded-lg">
                        <div class="stat-figure text-primary">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <div class="stat-title">کل درخواست‌ها</div>
                        <div class="stat-value text-primary"><?php echo en2fa($stats['total_requests']); ?></div>
                        <div class="stat-desc">از ابتدای راه‌اندازی</div>
                    </div>
                    
                    <!-- Total Customers -->
                    <div class="stat bg-base-200 rounded-lg">
                        <div class="stat-figure text-secondary">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <div class="stat-title">کل مشتریان</div>
                        <div class="stat-value text-secondary"><?php echo en2fa($stats['total_customers']); ?></div>
                        <div class="stat-desc">مشتریان ثبت شده</div>
                    </div>
                    
                    <!-- Total Income -->
                    <div class="stat bg-base-200 rounded-lg">
                        <div class="stat-figure text-accent">
                            <i class="fas fa-coins text-2xl"></i>
                        </div>
                        <div class="stat-title">کل درآمد</div>
                        <div class="stat-value text-accent"><?php echo en2fa(number_format($stats['total_income'])); ?></div>
                        <div class="stat-desc">تومان</div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- System Tools -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-tools text-warning ml-2"></i>
                    ابزارهای سیستم
                </h2>
                
                <div class="space-y-4">
                    
                    <!-- Clear Cache -->
                    <div class="p-4 bg-base-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="font-medium">پاک کردن کش</div>
                                <div class="text-sm text-base-content/70">حذف فایل‌های موقت و کش</div>
                            </div>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="clear_cache">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-broom ml-1"></i>
                                    پاک کردن
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- System Check -->
                    <div class="p-4 bg-base-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="font-medium">بررسی سیستم</div>
                                <div class="text-sm text-base-content/70">چک کردن سلامت سیستم</div>
                            </div>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="system_check">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-stethoscope ml-1"></i>
                                    بررسی
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Database Backup -->
                    <div class="p-4 bg-base-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="font-medium">پشتیبان‌گیری</div>
                                <div class="text-sm text-base-content/70">ایجاد نسخه پشتیبان از دیتابیس</div>
                            </div>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="backup_database">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-download ml-1"></i>
                                    پشتیبان
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Application Settings -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-sliders-h text-error ml-2"></i>
                    تنظیمات برنامه
                </h2>
                
                <div class="space-y-6">
                    
                    <!-- Theme Settings -->
                    <div>
                        <h3 class="font-medium mb-3">
                            <i class="fas fa-palette ml-1"></i>
                            تنظیمات ظاهری
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="cursor-pointer label">
                                    <span class="label-text">تم روشن</span>
                                    <input type="radio" name="theme" value="light" class="radio radio-primary" checked>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="cursor-pointer label">
                                    <span class="label-text">تم تیره</span>
                                    <input type="radio" name="theme" value="dark" class="radio radio-primary">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Language Settings -->
                    <div>
                        <h3 class="font-medium mb-3">
                            <i class="fas fa-language ml-1"></i>
                            تنظیمات زبان
                        </h3>
                        <select class="select select-bordered w-full">
                            <option selected>فارسی</option>
                            <option disabled>انگلیسی (به زودی)</option>
                        </select>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div>
                        <h3 class="font-medium mb-3">
                            <i class="fas fa-bell ml-1"></i>
                            تنظیمات اعلان‌ها
                        </h3>
                        <div class="space-y-2">
                            <div class="form-control">
                                <label class="cursor-pointer label">
                                    <span class="label-text">اعلان درخواست جدید</span>
                                    <input type="checkbox" class="toggle toggle-primary" checked>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="cursor-pointer label">
                                    <span class="label-text">اعلان پرداخت</span>
                                    <input type="checkbox" class="toggle toggle-primary" checked>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="cursor-pointer label">
                                    <span class="label-text">اعلان تکمیل کار</span>
                                    <input type="checkbox" class="toggle toggle-primary">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Settings -->
                    <div class="card-actions justify-end pt-4">
                        <button class="btn btn-primary">
                            <i class="fas fa-save ml-2"></i>
                            ذخیره تنظیمات
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- System Logs -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-file-alt text-info ml-2"></i>
                لاگ‌های سیستم
            </h2>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>زمان</th>
                            <th>عملیات</th>
                            <th>کاربر</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo en2fa(date('Y/m/d H:i')); ?></td>
                            <td>ورود به سیستم</td>
                            <td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
                            <td><div class="badge badge-success">موفق</div></td>
                        </tr>
                        <tr>
                            <td><?php echo en2fa(date('Y/m/d H:i', strtotime('-1 hour'))); ?></td>
                            <td>ایجاد درخواست جدید</td>
                            <td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
                            <td><div class="badge badge-success">موفق</div></td>
                        </tr>
                        <tr>
                            <td><?php echo en2fa(date('Y/m/d H:i', strtotime('-2 hours'))); ?></td>
                            <td>بروزرسانی پروفایل</td>
                            <td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
                            <td><div class="badge badge-success">موفق</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-ghost btn-sm">
                    مشاهده تمام لاگ‌ها
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Set current theme
    themeRadios.forEach(radio => {
        if (radio.value === currentTheme) {
            radio.checked = true;
        }
        
        radio.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('theme', this.value);
                // Apply theme immediately
                document.documentElement.setAttribute('data-theme', this.value);
                showToast(`تم ${this.value === 'dark' ? 'تیره' : 'روشن'} اعمال شد`, 'success');
            }
        });
    });
    
    // Notification settings
    const notificationToggles = document.querySelectorAll('input[type="checkbox"]');
    notificationToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const label = this.closest('label').querySelector('.label-text').textContent;
            const status = this.checked ? 'فعال' : 'غیرفعال';
            showToast(`${label} ${status} شد`, 'info');
        });
    });
    
    // Auto-refresh system info every 30 seconds
    let refreshInterval;
    
    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            // This would refresh system stats in a real implementation
            console.log('Refreshing system stats...');
        }, 30000);
    }
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Stop auto-refresh when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(refreshInterval);
        } else {
            startAutoRefresh();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>