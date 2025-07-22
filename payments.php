<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$pageTitle = 'مدیریت مالی - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'مدیریت مالی']
];

// دریافت پارامترهای فیلتر
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$customerId = $_GET['customer_id'] ?? '';
$paymentType = $_GET['payment_type'] ?? '';

// پیجینیشن
$page = $_GET['page'] ?? 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// دریافت تراکنش‌ها با فیلتر
$transactions = getTransactionsWithFilter($startDate, $endDate, $customerId);

// فیلتر بر اساس نوع پرداخت
if ($paymentType) {
    $transactions = array_filter($transactions, function($t) use ($paymentType) {
        return $t['payment_type'] == $paymentType;
    });
}

// آمار مالی کلی
$stats = getStats();

// آمار مالی در بازه انتخابی
$periodStats = getFinancialStatsByDateRange($startDate, $endDate);

// آمار ماهانه برای چارت
$monthlyData = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthStats = getFinancialStatsByDateRange(
        date('Y-m-01', strtotime("-$i months")),
        date('Y-m-t', strtotime("-$i months"))
    );
    $monthlyData[] = [
        'month' => $month,
        'income' => $monthStats['total_income'],
        'expenses' => $monthStats['total_debt'],
        'net' => $monthStats['net_income']
    ];
}

// دریافت مشتریان برای فیلتر
$customers = getAllCustomers();

// آمار کوتاه مدت
$shortStats = getShortTermStats();

include 'includes/header.php';
?>

<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-base-content flex items-center gap-3">
                <div class="w-12 h-12 bg-success rounded-xl flex items-center justify-center">
                    <i class="fas fa-credit-card text-success-content text-xl"></i>
                </div>
                مدیریت مالی
            </h1>
            <p class="text-base-content/70 mt-2">
                گزارش‌گیری مالی، تراکنش‌ها و آمار درآمد
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="add_payment.php" class="btn btn-primary">
                <i class="fas fa-plus ml-2"></i>
                تراکنش جدید
            </a>
            <button onclick="exportToExcel()" class="btn btn-outline btn-success">
                <i class="fas fa-file-excel ml-2"></i>
                خروجی Excel
            </button>
        </div>
    </div>
    
    <!-- آمار کلی -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- کل درآمد -->
        <div class="card bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">کل درآمد</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa(number_format($stats['total_income'])); ?></p>
                        <p class="text-sm opacity-75">تومان</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-arrow-up text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="badge badge-success badge-sm">
                        +<?php echo en2fa(number_format($shortStats['income_last_30_days'])); ?>
                    </div>
                    <span class="text-sm opacity-75">۳۰ روز گذشته</span>
                </div>
            </div>
        </div>
        
        <!-- کل بدهکاری -->
        <div class="card bg-gradient-to-br from-red-500 to-pink-600 text-white shadow-xl">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">کل بدهکاری</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa(number_format($stats['total_debt'])); ?></p>
                        <p class="text-sm opacity-75">تومان</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-arrow-down text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="badge badge-error badge-sm">
                        <?php echo en2fa(number_format($stats['total_debt'])); ?>
                    </div>
                    <span class="text-sm opacity-75">جاری</span>
                </div>
            </div>
        </div>
        
        <!-- خالص درآمد -->
        <div class="card bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">خالص درآمد</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa(number_format($stats['total_income'] - $stats['total_debt'])); ?></p>
                        <p class="text-sm opacity-75">تومان</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <?php 
                    $netIncome = $stats['total_income'] - $stats['total_debt'];
                    $badgeClass = $netIncome >= 0 ? 'badge-success' : 'badge-error';
                    $icon = $netIncome >= 0 ? 'fa-trending-up' : 'fa-trending-down';
                    ?>
                    <div class="badge <?php echo $badgeClass; ?> badge-sm">
                        <i class="fas <?php echo $icon; ?> mr-1"></i>
                        <?php echo $netIncome >= 0 ? 'سود' : 'زیان'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- تراکنش‌های دوره -->
        <div class="card bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-xl">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">تراکنش‌های دوره</h3>
                        <p class="text-3xl font-bold"><?php echo en2fa(count($transactions)); ?></p>
                        <p class="text-sm opacity-75">تراکنش</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="badge badge-info badge-sm">
                        <?php echo en2fa(number_format($periodStats['total_income'] + $periodStats['total_debt'])); ?>
                    </div>
                    <span class="text-sm opacity-75">مجموع مبلغ</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- چارت‌ها -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- چارت درآمد ماهانه -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-chart-bar text-primary ml-2"></i>
                    تحلیل درآمد ماهانه
                </h2>
                <div class="h-80">
                    <canvas id="monthlyIncomeChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- آمار دوره‌ای -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-calculator text-secondary ml-2"></i>
                    آمار دوره انتخابی
                </h2>
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="stat bg-primary/10 rounded-xl p-4">
                        <div class="stat-figure text-primary">
                            <i class="fas fa-coins text-2xl"></i>
                        </div>
                        <div class="stat-title">درآمد دوره</div>
                        <div class="stat-value text-primary text-2xl">
                            <?php echo en2fa(number_format($periodStats['total_income'])); ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                    
                    <div class="stat bg-error/10 rounded-xl p-4">
                        <div class="stat-figure text-error">
                            <i class="fas fa-credit-card text-2xl"></i>
                        </div>
                        <div class="stat-title">بدهکاری دوره</div>
                        <div class="stat-value text-error text-2xl">
                            <?php echo en2fa(number_format($periodStats['total_debt'])); ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                    
                    <div class="stat bg-success/10 rounded-xl p-4">
                        <div class="stat-figure text-success">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <div class="stat-title">خالص دوره</div>
                        <div class="stat-value text-success text-2xl">
                            <?php echo en2fa(number_format($periodStats['net_income'])); ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                </div>
                
                <!-- پروگرس نسبت سود -->
                <div class="mt-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span>نرخ سودآوری</span>
                        <?php 
                        $profitRate = $periodStats['total_income'] > 0 ? 
                            ($periodStats['net_income'] / $periodStats['total_income']) * 100 : 0;
                        ?>
                        <span><?php echo en2fa(number_format($profitRate, 1)); ?>%</span>
                    </div>
                    <progress class="progress progress-success w-full" 
                              value="<?php echo abs($profitRate); ?>" max="100"></progress>
                </div>
            </div>
        </div>
    </div>
    
    <!-- فیلترهای جستجو -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <i class="fas fa-filter text-accent ml-2"></i>
                فیلترهای جستجو
            </h2>
            
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- تاریخ شروع -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">از تاریخ</span>
                        </label>
                        <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                               class="input input-bordered w-full">
                    </div>
                    
                    <!-- تاریخ پایان -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">تا تاریخ</span>
                        </label>
                        <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                               class="input input-bordered w-full">
                    </div>
                    
                    <!-- فیلتر مشتری -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">مشتری</span>
                        </label>
                        <select name="customer_id" class="select select-bordered w-full">
                            <option value="">همه مشتریان</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" 
                                    <?php echo $customerId == $customer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- نوع تراکنش -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">نوع تراکنش</span>
                        </label>
                        <select name="payment_type" class="select select-bordered w-full">
                            <option value="">همه</option>
                            <option value="واریز" <?php echo $paymentType == 'واریز' ? 'selected' : ''; ?>>واریز</option>
                            <option value="بدهکاری" <?php echo $paymentType == 'بدهکاری' ? 'selected' : ''; ?>>بدهکاری</option>
                        </select>
                    </div>
                </div>
                
                <!-- دکمه‌های فیلتر -->
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search ml-2"></i>
                        اعمال فیلتر
                    </button>
                    <a href="payments.php" class="btn btn-ghost">
                        <i class="fas fa-times ml-2"></i>
                        پاک کردن
                    </a>
                    
                    <!-- فیلترهای سریع -->
                    <div class="divider divider-horizontal"></div>
                    <div class="flex gap-2">
                        <a href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                           class="btn btn-outline btn-sm">امروز</a>
                        <a href="?start_date=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                           class="btn btn-outline btn-sm">هفته گذشته</a>
                        <a href="?start_date=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                           class="btn btn-outline btn-sm">ماه گذشته</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- جدول تراکنش‌ها -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body p-0">
            
            <!-- هدر جدول -->
            <div class="p-6 border-b border-base-300">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">
                        <i class="fas fa-list text-warning ml-2"></i>
                        لیست تراکنش‌ها
                    </h2>
                    <div class="text-sm text-base-content/70">
                        مجموع: <?php echo en2fa(count($transactions)); ?> تراکنش
                    </div>
                </div>
            </div>
            
            <?php if (empty($transactions)): ?>
            <!-- پیام خالی -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-base-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-4xl text-base-content/30"></i>
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">تراکنشی یافت نشد</h3>
                <p class="text-base-content/70 mb-6">
                    در بازه زمانی انتخاب شده، تراکنشی ثبت نشده است.
                </p>
                <a href="add_payment.php" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    ثبت تراکنش جدید
                </a>
            </div>
            
            <?php else: ?>
            
            <!-- جدول -->
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>تاریخ</th>
                            <th>مشتری</th>
                            <th>درخواست</th>
                            <th>نوع</th>
                            <th>مبلغ</th>
                            <th>توضیحات</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover">
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium">
                                        <?php echo en2fa(jalali_date('Y/m/d', strtotime($transaction['created_at']))); ?>
                                    </div>
                                    <div class="text-base-content/60">
                                        <?php echo en2fa(date('H:i', strtotime($transaction['created_at']))); ?>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                            <span class="text-xs">
                                                <?php echo mb_substr($transaction['customer_name'] ?? 'ن', 0, 1); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-base-content">
                                            <?php echo htmlspecialchars($transaction['customer_name'] ?? 'نامشخص'); ?>
                                        </div>
                                        <div class="text-sm text-base-content/60">
                                            <?php echo $transaction['customer_phone'] ?? '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <?php if ($transaction['tracking_code']): ?>
                                <div class="flex items-center gap-2">
                                    <code class="bg-info/10 text-info px-2 py-1 rounded text-sm">
                                        <?php echo en2fa($transaction['tracking_code']); ?>
                                    </code>
                                    <div class="text-sm text-base-content/60">
                                        <?php echo htmlspecialchars($transaction['request_title'] ?? ''); ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-base-content/40">بدون درخواست</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div class="badge <?php echo $transaction['payment_type'] == 'واریز' ? 'badge-success' : 'badge-error'; ?>">
                                    <i class="fas <?php echo $transaction['payment_type'] == 'واریز' ? 'fa-arrow-up' : 'fa-arrow-down'; ?> ml-1"></i>
                                    <?php echo $transaction['payment_type']; ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="text-lg font-bold <?php echo $transaction['payment_type'] == 'واریز' ? 'text-success' : 'text-error'; ?>">
                                    <?php echo en2fa(number_format($transaction['amount'])); ?>
                                    <span class="text-sm font-normal text-base-content/60">تومان</span>
                                </div>
                            </td>
                            
                            <td>
                                <div class="max-w-xs">
                                    <?php echo htmlspecialchars($transaction['description'] ?? '-'); ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="flex justify-center gap-1">
                                    <div class="dropdown dropdown-end">
                                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-48">
                                            <li>
                                                <a href="edit_payment.php?id=<?php echo $transaction['id']; ?>" class="text-sm">
                                                    <i class="fas fa-edit"></i>
                                                    ویرایش
                                                </a>
                                            </li>
                                            <li>
                                                <a href="print_payment.php?id=<?php echo $transaction['id']; ?>" class="text-sm">
                                                    <i class="fas fa-print"></i>
                                                    چاپ رسید
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a onclick="deletePayment(<?php echo $transaction['id']; ?>)" class="text-sm text-error">
                                                    <i class="fas fa-trash"></i>
                                                    حذف
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- خلاصه مالی -->
            <div class="p-6 border-t border-base-300 bg-base-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="stat">
                        <div class="stat-title">مجموع واریزها</div>
                        <div class="stat-value text-success">
                            <?php 
                            $totalIncome = array_sum(array_map(function($t) {
                                return $t['payment_type'] == 'واریز' ? $t['amount'] : 0;
                            }, $transactions));
                            echo en2fa(number_format($totalIncome));
                            ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-title">مجموع بدهکاری‌ها</div>
                        <div class="stat-value text-error">
                            <?php 
                            $totalDebt = array_sum(array_map(function($t) {
                                return $t['payment_type'] == 'بدهکاری' ? $t['amount'] : 0;
                            }, $transactions));
                            echo en2fa(number_format($totalDebt));
                            ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-title">خالص</div>
                        <div class="stat-value <?php echo ($totalIncome - $totalDebt) >= 0 ? 'text-success' : 'text-error'; ?>">
                            <?php echo en2fa(number_format($totalIncome - $totalDebt)); ?>
                        </div>
                        <div class="stat-desc">تومان</div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- مودال تایید حذف -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">
            <i class="fas fa-exclamation-triangle ml-2"></i>
            تایید حذف تراکنش
        </h3>
        <p class="py-4">آیا از حذف این تراکنش اطمینان دارید؟ این عمل قابل بازگشت نیست.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">انصراف</button>
            </form>
            <button id="confirmDelete" class="btn btn-error">
                <i class="fas fa-trash ml-1"></i>
                حذف
            </button>
        </div>
    </div>
</dialog>

<script>
    let deletePaymentId = null;
    
    // چارت درآمد ماهانه
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyIncomeChart').getContext('2d');
        
        const monthNames = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.month + '-01');
                    return monthNames[date.getMonth()];
                }),
                datasets: [{
                    label: 'درآمد',
                    data: monthlyData.map(item => item.income),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'بدهکاری',
                    data: monthlyData.map(item => item.expenses),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                            }
                        }
                    }
                }
            }
        });
    });
    
    function deletePayment(id) {
        deletePaymentId = id;
        document.getElementById('deleteModal').showModal();
    }
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deletePaymentId) {
            fetch('delete_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: deletePaymentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('تراکنش با موفقیت حذف شد', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('خطا در حذف تراکنش', 'error');
                }
            });
            
            document.getElementById('deleteModal').close();
        }
    });
    
    function exportToExcel() {
        const params = new URLSearchParams(window.location.search);
        window.open('export_payments.php?' + params.toString(), '_blank');
    }
</script>

<?php include 'includes/footer.php'; ?>