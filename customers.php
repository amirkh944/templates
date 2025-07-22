<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$customers = getAllCustomers();

$pageTitle = 'مدیریت مشتریان - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'مدیریت مشتریان']
];

include 'includes/header.php';
?>
<!-- صفحه مدیریت مشتریان -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-info to-secondary rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-users ml-2"></i>
                    مدیریت مشتریان
                </h1>
                <p class="text-lg">
                    مشاهده و مدیریت اطلاعات مشتریان
                </p>
            </div>
        </div>
    </div>
    
    <!-- آمار سریع مشتریان -->
    <div class="stats shadow w-full">
        <div class="stat">
            <div class="stat-figure text-primary">
                <i class="fas fa-users text-3xl"></i>
            </div>
            <div class="stat-title">کل مشتریان</div>
            <div class="stat-value text-primary"><?php echo en2fa(count($customers)); ?></div>
            <div class="stat-desc">مشتریان ثبت‌شده در سیستم</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-secondary">
                <i class="fas fa-user-plus text-3xl"></i>
            </div>
            <div class="stat-title">مشتریان جدید امروز</div>
            <div class="stat-value text-secondary">
                <?php 
                $todayCustomers = array_filter($customers, function($customer) {
                    return date('Y-m-d', strtotime($customer['created_at'])) === date('Y-m-d');
                });
                echo en2fa(count($todayCustomers));
                ?>
            </div>
            <div class="stat-desc">ثبت‌شده امروز</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-accent">
                <i class="fas fa-chart-line text-3xl"></i>
            </div>
            <div class="stat-title">میانگین موجودی</div>
            <div class="stat-value text-accent">
                <?php 
                $totalBalance = 0;
                foreach ($customers as $customer) {
                    $totalBalance += getCustomerBalance($customer['id']);
                }
                $avgBalance = count($customers) > 0 ? $totalBalance / count($customers) : 0;
                echo en2fa(number_format($avgBalance));
                ?>
            </div>
            <div class="stat-desc">تومان</div>
        </div>
    </div>
    
    <!-- جدول مشتریان -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-list text-info ml-2"></i>
                    لیست مشتریان
                </h2>
                <div class="flex gap-2">
                    <a href="new_customer.php" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        مشتری جدید
                    </a>
                    <button class="btn btn-ghost" onclick="window.print()">
                        <i class="fas fa-print ml-2"></i>
                        چاپ لیست
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>نام و نام خانوادگی</th>
                            <th>تلفن همراه</th>
                            <th>ایمیل</th>
                            <th>تاریخ ثبت</th>
                            <th>موجودی</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <?php $balance = getCustomerBalance($customer['id']); ?>
                        <tr class="hover">
                            <td>
                                <div class="badge badge-neutral">
                                    <?php echo en2fa($customer['id']); ?>
                                </div>
                            </td>
                            <td class="font-medium">
                                <div class="flex items-center">
                                    <div class="avatar placeholder ml-3">
                                        <div class="bg-neutral-focus text-neutral-content rounded-full w-8">
                                            <span class="text-xs">
                                                <?php echo mb_substr($customer['name'], 0, 1); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-base-content/50 ml-2"></i>
                                    <span dir="ltr"><?php echo $customer['phone']; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-base-content/50 ml-2"></i>
                                    <?php echo $customer['email'] ?: 'ندارد'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                                    <?php echo en2fa(jalali_date('Y/m/d', strtotime($customer['created_at']))); ?>
                                </div>
                            </td>
                            <td>
                                <div class="badge <?php echo $balance >= 0 ? 'badge-success' : 'badge-error'; ?>">
                                    <i class="fas fa-wallet ml-1"></i>
                                    <?php echo en2fa(formatNumber($balance)); ?> ت
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="مشاهده">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="new_request.php?customer_id=<?php echo $customer['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="درخواست جدید">
                                        <i class="fas fa-plus text-primary"></i>
                                    </a>
                                    <a href="print_customer.php?id=<?php echo $customer['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="چاپ">
                                        <i class="fas fa-print text-secondary"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($customers)): ?>
            <div class="text-center py-12">
                <i class="fas fa-users text-6xl text-base-content/20 mb-4"></i>
                <h3 class="text-xl font-semibold text-base-content/60 mb-2">هیچ مشتری‌ای یافت نشد</h3>
                <p class="text-base-content/40 mb-6">برای شروع، اولین مشتری خود را اضافه کنید</p>
                <a href="new_customer.php" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    اضافه کردن مشتری جدید
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>