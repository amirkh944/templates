<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

// دریافت پارامترهای فیلتر
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$customerId = $_GET['customer_id'] ?? '';

// تنظیم تاریخ‌های پیش‌فرض (یک ماه گذشته)
if (!$startDate) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!$endDate) {
    $endDate = date('Y-m-d');
}

// دریافت تراکنش‌ها با فیلتر
$transactions = getTransactionsWithFilter($startDate, $endDate, $customerId);

// آمار مالی کلی
$stats = getStats();

// آمار مالی در بازه انتخابی
$periodStats = getFinancialStatsByDateRange($startDate, $endDate);

// دریافت مشتریان برای فیلتر
$customers = getAllCustomers();

// کنترل تم (قالب)
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مالی - مدیریت درخواست پاسخگو رایانه</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Vazir', sans-serif; }
        <?php if ($isDark): ?>
        .dark-theme {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
            color: #e5e7eb;
        }
        .dark-card {
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid #374151;
        }
        .dark-input {
            background: #374151;
            border: 1px solid #4b5563;
            color: #e5e7eb;
        }
        .dark-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        <?php endif; ?>
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body class="<?php echo $isDark ? 'dark-theme min-h-screen' : 'bg-gray-100'; ?>">
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
                            <i class="fas fa-list ml-1"></i>درخواست‌ها
                        </a>
                        <a href="customers.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <i class="fas fa-users ml-1"></i>مشتریان
                        </a>
                        <a href="add_payment.php?theme=<?php echo $theme; ?>" class="px-3 py-2 rounded bg-green-500 text-white hover:bg-green-600">
                            <i class="fas fa-plus ml-1"></i>پرداخت جدید
                        </a>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="<?php echo $isDark ? 'text-gray-300' : 'text-gray-700'; ?> hover:bg-gray-100 p-2 rounded">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    
                    <span class="<?php echo $isDark ? 'text-gray-300' : 'text-gray-700'; ?> hidden sm:block">خوش آمدید، <?php echo $_SESSION['username']; ?></span>
                    
                    <!-- Theme Toggle -->
                    <div class="flex space-x-2">
                        <a href="?theme=light&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&customer_id=<?php echo $customerId; ?>" 
                           class="px-3 py-1 rounded <?php echo !$isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>" title="حالت روشن">
                            <i class="fas fa-sun"></i>
                        </a>
                        <a href="?theme=dark&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&customer_id=<?php echo $customerId; ?>" 
                           class="px-3 py-1 rounded <?php echo $isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>" title="حالت تیره">
                            <i class="fas fa-moon"></i>
                        </a>
                    </div>
                    
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">خروج</a>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden <?php echo $isDark ? 'bg-gray-700' : 'bg-white'; ?> border-t">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="dashboard.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-home ml-2"></i>داشبورد
                    </a>
                    <a href="new_request.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-plus-circle ml-2"></i>درخواست جدید
                    </a>
                    <a href="requests.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-list ml-2"></i>درخواست‌ها
                    </a>
                    <a href="customers.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded <?php echo $isDark ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-users ml-2"></i>مشتریان
                    </a>
                    <a href="add_payment.php?theme=<?php echo $theme; ?>" class="block px-3 py-2 rounded bg-green-500 text-white hover:bg-green-600">
                        <i class="fas fa-plus ml-2"></i>پرداخت جدید
                    </a>
                </div>
            </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Financial Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line text-2xl text-green-500"></i>
                        </div>
                        <div class="mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> truncate">کل درآمد</dt>
                                <dd class="text-lg font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>"><?php echo en2fa(formatNumber($stats['total_income'])); ?> تومان</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-pie text-2xl text-red-500"></i>
                        </div>
                        <div class="mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> truncate">کل بدهکاری</dt>
                                <dd class="text-lg font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>"><?php echo en2fa(formatNumber($stats['total_debt'])); ?> تومان</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-balance-scale text-2xl text-purple-500"></i>
                        </div>
                        <div class="mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> truncate">خالص درآمد</dt>
                                <dd class="text-lg font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>"><?php echo en2fa(formatNumber($stats['total_income'] - $stats['total_debt'])); ?> تومان</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt text-2xl text-blue-500"></i>
                        </div>
                        <div class="mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> truncate">درآمد دوره انتخابی</dt>
                                <dd class="text-lg font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>"><?php echo en2fa(formatNumber($periodStats['net_income'])); ?> تومان</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    <i class="fas fa-filter ml-2"></i>
                    فیلتر تراکنش‌ها
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    فیلتر تراکنش‌ها بر اساس تاریخ و مشتری
                </p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="hidden" name="theme" value="<?php echo $theme; ?>">
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                            از تاریخ
                        </label>
                        <input type="date" name="start_date" 
                               class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                               value="<?php echo $startDate; ?>">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                            تا تاریخ
                        </label>
                        <input type="date" name="end_date" 
                               class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                               value="<?php echo $endDate; ?>">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                            مشتری
                        </label>
                        <select name="customer_id" class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2">
                            <option value="">همه مشتریان</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" <?php echo $customerId == $customer['id'] ? 'selected' : ''; ?>>
                                <?php echo $customer['name']; ?> - <?php echo $customer['phone']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-search ml-2"></i>
                            اعمال فیلتر
                        </button>
                    </div>
                </form>

                <!-- Quick Filters -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="?theme=<?php echo $theme; ?>&start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                       class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">امروز</a>
                    <a href="?theme=<?php echo $theme; ?>&start_date=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                       class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">هفته گذشته</a>
                    <a href="?theme=<?php echo $theme; ?>&start_date=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                       class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">ماه گذشته</a>
                    <a href="?theme=<?php echo $theme; ?>&start_date=<?php echo date('Y-m-d', strtotime('-90 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                       class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">سه ماه گذشته</a>
                </div>
            </div>
        </div>

        <!-- Financial Chart -->
        <?php 
        $weeklyFinancialStats = getWeeklyStats();
        if (!empty($weeklyFinancialStats)): 
        ?>
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">نمودار درآمد هفتگی</h3>
                <p class="mt-1 text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">آمار درآمد ۷ روز گذشته</p>
            </div>
            <div class="px-4 pb-5">
                <div class="chart-container">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Transactions Table -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    تراکنش‌های مالی
                </h3>
                <p class="mt-1 text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    <?php echo en2fa(count($transactions)); ?> تراکنش در بازه انتخابی
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                تاریخ
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                مشتری
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                درخواست
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                نوع
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                مبلغ
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                توضیحات
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                تراز مشتری
                            </th>
                        </tr>
                    </thead>
                    <tbody class="<?php echo $isDark ? 'bg-gray-800 divide-gray-600' : 'bg-white divide-gray-200'; ?> divide-y">
                        <?php 
                        $totalIncome = 0;
                        $totalDebt = 0;
                        foreach ($transactions as $transaction): 
                            if ($transaction['payment_type'] == 'واریز') {
                                $totalIncome += $transaction['amount'];
                            } else {
                                $totalDebt += $transaction['amount'];
                            }
                            $customerBalance = getCustomerBalance($transaction['customer_id']);
                        ?>
                        <tr class="hover:<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-500'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d H:i', strtotime($transaction['created_at']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <div class="font-medium"><?php echo htmlspecialchars($transaction['customer_name']); ?></div>
                                <div class="text-gray-500 font-mono text-xs"><?php echo $transaction['customer_phone']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php if ($transaction['request_title']): ?>
                                    <div class="font-medium"><?php echo htmlspecialchars($transaction['request_title']); ?></div>
                                    <div class="text-gray-500 text-xs">کد: <?php echo en2fa($transaction['tracking_code']); ?></div>
                                <?php else: ?>
                                    <span class="text-gray-400">بدون درخواست</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php echo $transaction['payment_type'] == 'واریز' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <i class="<?php echo $transaction['payment_type'] == 'واریز' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'; ?> ml-1"></i>
                                    <?php echo $transaction['payment_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <span class="<?php echo $transaction['payment_type'] == 'واریز' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $transaction['payment_type'] == 'واریز' ? '+' : '-'; ?>
                                    <?php echo en2fa(formatNumber($transaction['amount'])); ?> تومان
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo htmlspecialchars($transaction['description'] ?: 'بدون توضیحات'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <span class="<?php echo $customerBalance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php if ($customerBalance >= 0): ?>
                                        <i class="fas fa-arrow-up ml-1"></i>بستانکار
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down ml-1"></i>بدهکار
                                    <?php endif; ?>
                                    <div class="text-xs"><?php echo en2fa(formatNumber(abs($customerBalance))); ?> تومان</div>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($transactions)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-receipt text-4xl <?php echo $isDark ? 'text-gray-400' : 'text-gray-300'; ?> mb-4"></i>
                    <p class="<?php echo $isDark ? 'text-gray-400' : 'text-gray-500'; ?>">در این بازه زمانی تراکنشی یافت نشد</p>
                </div>
                <?php else: ?>
                <!-- Summary Footer -->
                <div class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?> px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?>">مجموع واریزی:</span>
                            <span class="font-bold text-green-600"><?php echo en2fa(formatNumber($totalIncome)); ?> تومان</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?>">مجموع بدهکاری:</span>
                            <span class="font-bold text-red-600"><?php echo en2fa(formatNumber($totalDebt)); ?> تومان</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?>">خالص:</span>
                            <span class="font-bold <?php echo ($totalIncome - $totalDebt) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo en2fa(formatNumber($totalIncome - $totalDebt)); ?> تومان
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($weeklyFinancialStats)): ?>
    <script>
        // Financial Chart
        const financialCtx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(financialCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($weeklyFinancialStats, 'date')); ?>,
                datasets: [{
                    label: 'درآمد روزانه',
                    data: <?php echo json_encode(array_column($weeklyFinancialStats, 'income')); ?>,
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '<?php echo $isDark ? "#e5e7eb" : "#374151"; ?>'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '<?php echo $isDark ? "#9ca3af" : "#6b7280"; ?>'
                        },
                        grid: {
                            color: '<?php echo $isDark ? "#374151" : "#f3f4f6"; ?>'
                        }
                    },
                    y: {
                        ticks: {
                            color: '<?php echo $isDark ? "#9ca3af" : "#6b7280"; ?>'
                        },
                        grid: {
                            color: '<?php echo $isDark ? "#374151" : "#f3f4f6"; ?>'
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
    
    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Auto-hide mobile menu on larger screens
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>