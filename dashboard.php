<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

// کنترل تم
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';

// دریافت آمار کلی
$stats = getStats();
$recentRequests = array_slice(getAllRequests(), 0, 5);

// آمار برای چارت‌ها
$weeklyStats = getWeeklyStats();
$monthlyStats = getMonthlyStats();
$statusStats = getStatusStats();
include 'templates/header.php';
?>
        
        <!-- کارت‌های آمار کلی -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- کل درخواست‌ها -->
            <div class="stat-card <?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                            کل درخواست‌ها
                        </p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa($stats['total_requests']); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- در حال پردازش -->
            <div class="stat-card <?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                            در حال پردازش
                        </p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa($stats['pending_requests']); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- کل مشتریان -->
            <div class="stat-card <?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                            کل مشتریان
                        </p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa($stats['total_customers']); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- کل درآمد -->
            <div class="stat-card <?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm font-medium <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">
                            کل درآمد
                        </p>
                        <p class="text-2xl font-bold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                            <?php echo en2fa(formatNumber($stats['total_income'])); ?>
                        </p>
                        <p class="text-xs <?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">تومان</p>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- دسترسی سریع -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6 mb-8">
            <h2 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-6">
                <i class="fas fa-bolt ml-2 text-yellow-500"></i>
                دسترسی سریع
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                
                <a href="new_request.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-plus-circle text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">درخواست جدید</span>
                </a>
                
                <a href="search_requests.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-search text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">جستجو</span>
                </a>
                
                <a href="requests.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-list text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">مدیریت درخواست‌ها</span>
                </a>
                
                <a href="customers.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-indigo-500 to-indigo-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-users text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">مشتریان</span>
                </a>
                
                <a href="contacts.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-user-plus text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">مشتری جدید</span>
                </a>
                
                <a href="communications.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-teal-500 to-teal-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-phone text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">ارتباطات</span>
                </a>
                
                <a href="payments.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-red-500 to-red-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-credit-card text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">مدیریت مالی</span>
                </a>
                
                <a href="send_sms.php?theme=<?php echo $theme; ?>" 
                   class="quick-access-item bg-gradient-to-r from-pink-500 to-pink-600 text-white p-4 rounded-lg text-center">
                    <i class="fas fa-sms text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium">ارسال پیامک</span>
                </a>
                
            </div>
        </div>

        <!-- نمودارها -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- نمودار درآمد هفتگی -->
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                    <i class="fas fa-chart-line ml-2 text-blue-500"></i>
                    درآمد ۷ روز گذشته
                </h3>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            
            <!-- نمودار وضعیت درخواست‌ها -->
            <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl p-6">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?> mb-4">
                    <i class="fas fa-chart-pie ml-2 text-green-500"></i>
                    وضعیت درخواست‌ها
                </h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            
        </div>

        <!-- آخرین درخواست‌ها -->
        <div class="<?php echo $isDark ? 'dark-card' : 'light-card'; ?> rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b <?php echo $isDark ? 'border-gray-600' : 'border-gray-200'; ?>">
                <h3 class="text-lg font-semibold <?php echo $isDark ? 'dark-text' : 'light-text'; ?>">
                    <i class="fas fa-history ml-2 text-purple-500"></i>
                    آخرین درخواست‌ها
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (empty($recentRequests)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-4xl <?php echo $isDark ? 'text-gray-400' : 'text-gray-300'; ?> mb-4"></i>
                    <p class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?>">هنوز درخواستی ثبت نشده است</p>
                </div>
                <?php else: ?>
                <table class="min-w-full">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">درخواست</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">مشتری</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">وضعیت</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">کد رهگیری</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                        <?php foreach ($recentRequests as $request): ?>
                        <tr class="hover:<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> text-sm font-medium">
                                    <?php echo htmlspecialchars($request['title']); ?>
                                </div>
                                <div class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-xs">
                                    <?php echo en2fa(jalali_date('Y/m/d', strtotime($request['created_at']))); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> text-sm">
                                    <?php echo htmlspecialchars($request['customer_name']); ?>
                                </div>
                                <div class="<?php echo $isDark ? 'dark-text-secondary' : 'light-text-secondary'; ?> text-xs">
                                    <?php echo $request['customer_phone']; ?>
                                </div>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="<?php echo $isDark ? 'dark-text' : 'light-text'; ?> text-sm font-mono">
                                    <?php echo en2fa($request['tracking_code']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
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
        
        // بستن منوی موبایل در صفحات بزرگ
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('mobile-menu').classList.add('hidden');
            }
        });
        
        // تنظیمات نمودار
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '<?php echo $isDark ? "#e2e8f0" : "#1e293b"; ?>',
                        font: { family: 'Vazir' }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '<?php echo $isDark ? "#94a3b8" : "#64748b"; ?>' },
                    grid: { color: '<?php echo $isDark ? "#475569" : "#e2e8f0"; ?>' }
                },
                y: {
                    ticks: { color: '<?php echo $isDark ? "#94a3b8" : "#64748b"; ?>' },
                    grid: { color: '<?php echo $isDark ? "#475569" : "#e2e8f0"; ?>' }
                }
            }
        };
        
        // نمودار درآمد هفتگی
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($weeklyStats, 'date')); ?>,
                datasets: [{
                    label: 'درآمد روزانه (تومان)',
                    data: <?php echo json_encode(array_column($weeklyStats, 'income')); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: chartOptions
        });
        
        // نمودار وضعیت درخواست‌ها
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($statusStats, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($statusStats, 'count')); ?>,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '<?php echo $isDark ? "#e2e8f0" : "#1e293b"; ?>',
                            font: { family: 'Vazir' },
                            padding: 20
                        }
                    }
                }
            }
        });
        
    </script>
</body>
</html>