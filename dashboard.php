<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$pageTitle = 'داشبورد - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد']
];

// دریافت آمار کلی
$stats = getStats();

// دریافت درخواست‌های اخیر
$stmt = $pdo->query("
    SELECT r.*, c.name as customer_name 
    FROM requests r 
    JOIN customers c ON r.customer_id = c.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت آمار هفتگی
$stmt = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM requests 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");
$weeklyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت آمار وضعیت
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM requests 
    GROUP BY status
");
$statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت آمار مالی ماهانه
$stmt = $pdo->query("
    SELECT MONTH(created_at) as month, SUM(amount) as total
    FROM payments 
    WHERE payment_type = 'واریز' AND YEAR(created_at) = YEAR(NOW())
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
");
$monthlyIncome = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- صفحه داشبورد -->
<div class="space-y-8">
    
    <!-- خوش آمدگویی -->
    <div class="hero bg-gradient-to-r from-primary to-secondary rounded-3xl text-primary-content">
        <div class="hero-content text-center py-12">
            <div class="max-w-lg">
                <h1 class="text-4xl font-bold mb-4">
                    سلام <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋
                </h1>
                <p class="text-xl mb-6">
                    خوش آمدید به پنل مدیریت پاسخگو رایانه
                </p>
                <div class="flex justify-center gap-4">
                    <a href="new_request.php" class="btn btn-accent btn-lg">
                        <i class="fas fa-plus-circle ml-2"></i>
                        درخواست جدید
                    </a>
                    <a href="search_requests.php" class="btn btn-outline btn-accent btn-lg">
                        <i class="fas fa-search ml-2"></i>
                        جستجو
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- کارت‌های آمار -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- کل درخواست‌ها -->
        <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">کل درخواست‌ها</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa($stats['total_requests']); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm opacity-75">مجموع تمام درخواست‌ها</span>
                    <i class="fas fa-arrow-up text-green-300"></i>
                </div>
            </div>
        </div>
        
        <!-- در حال پردازش -->
        <div class="card bg-gradient-to-br from-warning to-orange-500 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">در حال پردازش</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa($stats['pending_requests']); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm opacity-75">درخواست‌های در انتظار</span>
                    <i class="fas fa-sync text-yellow-300 animate-spin"></i>
                </div>
            </div>
        </div>
        
        <!-- تکمیل شده -->
        <div class="card bg-gradient-to-br from-success to-green-600 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">تکمیل شده</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa($stats['completed_requests']); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm opacity-75">کارهای انجام شده</span>
                    <i class="fas fa-trophy text-yellow-300"></i>
                </div>
            </div>
        </div>
        
        <!-- کل مشتریان -->
        <div class="card bg-gradient-to-br from-info to-cyan-600 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">کل مشتریان</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa($stats['total_customers']); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm opacity-75">مشتریان عزیز ما</span>
                    <i class="fas fa-heart text-red-300"></i>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- بخش چارت‌ها و جدول -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- چارت آمار هفتگی -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-chart-line text-primary ml-2"></i>
                    آمار هفتگی درخواست‌ها
                </h2>
                <div class="h-64">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- چارت وضعیت درخواست‌ها -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-chart-pie text-secondary ml-2"></i>
                    توزیع وضعیت درخواست‌ها
                </h2>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- بخش درخواست‌های اخیر و دسترسی سریع -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- درخواست‌های اخیر -->
        <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="card-title text-2xl">
                        <i class="fas fa-clock text-accent ml-2"></i>
                        درخواست‌های اخیر
                    </h2>
                    <a href="requests.php" class="btn btn-ghost btn-sm">
                        مشاهده همه
                        <i class="fas fa-arrow-left mr-2"></i>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>کد رهگیری</th>
                                <th>مشتری</th>
                                <th>عنوان</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $request): ?>
                            <tr class="hover">
                                <td>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm font-mono">
                                        <?php echo en2fa($request['tracking_code']); ?>
                                    </code>
                                </td>
                                <td class="font-medium">
                                    <?php echo htmlspecialchars($request['customer_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($request['title']); ?></td>
                                <td>
                                    <div class="badge <?php 
                                        switch($request['status']) {
                                            case 'تکمیل شده':
                                                echo 'badge-success';
                                                break;
                                            case 'لغو شده':
                                                echo 'badge-error';
                                                break;
                                            default:
                                                echo 'badge-warning';
                                        }
                                        ?>">
                                        <?php echo $request['status']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="view_request.php?id=<?php echo $request['id']; ?>" 
                                           class="btn btn-ghost btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_request.php?id=<?php echo $request['id']; ?>" 
                                           class="btn btn-ghost btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- دسترسی سریع -->
        <div class="space-y-6">
            
            <!-- منوی دسترسی سریع -->
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">
                        <i class="fas fa-bolt text-warning ml-2"></i>
                        دسترسی سریع
                    </h2>
                    
                    <div class="space-y-3">
                        <a href="new_request.php" class="btn btn-outline btn-primary w-full justify-start">
                            <i class="fas fa-plus-circle ml-2"></i>
                            درخواست جدید
                        </a>
                        <a href="customers.php" class="btn btn-outline btn-info w-full justify-start">
                            <i class="fas fa-users ml-2"></i>
                            مدیریت مشتریان
                        </a>
                        <a href="payments.php" class="btn btn-outline btn-success w-full justify-start">
                            <i class="fas fa-credit-card ml-2"></i>
                            مدیریت مالی
                        </a>
                        <a href="communications.php" class="btn btn-outline btn-secondary w-full justify-start">
                            <i class="fas fa-comments ml-2"></i>
                            ارتباطات
                        </a>
                        <?php if ($_SESSION['is_admin']): ?>
                        <a href="users.php" class="btn btn-outline btn-error w-full justify-start">
                            <i class="fas fa-user-cog ml-2"></i>
                            مدیریت کاربران
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- آمار مالی -->
            <div class="card bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">
                        <i class="fas fa-wallet ml-2"></i>
                        خلاصه مالی
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="opacity-90">کل درآمد:</span>
                            <span class="text-xl font-bold">
                                <?php echo en2fa(number_format($stats['total_income'])); ?> ت
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="opacity-90">کل بدهکاری:</span>
                            <span class="text-xl font-bold text-red-200">
                                <?php echo en2fa(number_format($stats['total_debt'])); ?> ت
                            </span>
                        </div>
                        
                        <div class="divider my-2"></div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">خالص:</span>
                            <span class="text-2xl font-bold text-yellow-300">
                                <?php echo en2fa(number_format($stats['total_income'] - $stats['total_debt'])); ?> ت
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-actions justify-end mt-4">
                        <a href="payments.php" class="btn btn-accent btn-sm">
                            جزئیات
                            <i class="fas fa-arrow-left mr-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- اطلاعات سیستم -->
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">
                        <i class="fas fa-info-circle text-info ml-2"></i>
                        اطلاعات سیستم
                    </h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">تاریخ امروز:</span>
                            <span class="font-medium"><?php echo en2fa(jalali_date('Y/m/d')); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">ساعت فعلی:</span>
                            <span class="font-medium"><?php echo en2fa(date('H:i')); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">نسخه سیستم:</span>
                            <span class="font-medium">v2.0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">دسترسی:</span>
                            <span class="badge badge-success badge-sm">
                                <?php echo $_SESSION['is_admin'] ? 'مدیر' : 'کاربر'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
</div>

<!-- اسکریپت‌های چارت -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // چارت آمار هفتگی
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $dates = [];
                    foreach ($weeklyStats as $stat) {
                        $dates[] = "'" . en2fa(jalali_date('m/d', strtotime($stat['date']))) . "'";
                    }
                    echo implode(',', $dates);
                    ?>
                ],
                datasets: [{
                    label: 'تعداد درخواست‌ها',
                    data: [<?php echo implode(',', array_column($weeklyStats, 'count')); ?>],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // چارت وضعیت
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    $labels = [];
                    foreach ($statusStats as $stat) {
                        $labels[] = "'" . $stat['status'] . "'";
                    }
                    echo implode(',', $labels);
                    ?>
                ],
                datasets: [{
                    data: [<?php echo implode(',', array_column($statusStats, 'count')); ?>],
                    backgroundColor: [
                        'rgb(34, 197, 94)',   // سبز برای تکمیل شده
                        'rgb(251, 191, 36)',  // زرد برای در حال پردازش
                        'rgb(239, 68, 68)'    // قرمز برای لغو شده
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
    });
</script>

<?php include 'includes/footer.php'; ?>