<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

if (!isset($_GET['id'])) {
    header('Location: customers.php');
    exit;
}

$customerId = $_GET['id'];
$customer = getCustomer($customerId);

if (!$customer) {
    header('Location: customers.php');
    exit;
}

$requests = getCustomerRequests($customerId);
$payments = getCustomerPayments($customerId);
$balance = getCustomerBalance($customerId);

$pageTitle = 'مشاهده مشتری - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'مشتریان', 'url' => 'customers.php'],
    ['title' => 'مشاهده مشتری']
];

include 'includes/header.php';
?>
<!-- صفحه مشاهده مشتری -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-info to-primary rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-user-circle ml-2"></i>
                    مشاهده مشتری
                </h1>
                <p class="text-lg">
                    <?php echo htmlspecialchars($customer['name']); ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- اطلاعات مشتری -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-info-circle text-info ml-2"></i>
                اطلاعات مشتری
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">شناسه مشتری</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-hashtag text-base-content/50 ml-2"></i>
                            <span><?php echo en2fa($customer['id']); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">نام و نام خانوادگی</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-user text-base-content/50 ml-2"></i>
                            <span><?php echo htmlspecialchars($customer['name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">تلفن همراه</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-phone text-base-content/50 ml-2"></i>
                            <span><?php echo $customer['phone']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">آدرس ایمیل</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-envelope text-base-content/50 ml-2"></i>
                            <span><?php echo $customer['email'] ?: 'ندارد'; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">تاریخ ثبت</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                            <span><?php echo en2fa(jalali_date('Y/m/d', strtotime($customer['created_at']))); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">موجودی مالی</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-wallet text-base-content/50 ml-2"></i>
                            <span class="font-bold <?php echo $balance >= 0 ? 'text-success' : 'text-error'; ?>">
                                <?php echo en2fa(formatNumber($balance)); ?> تومان
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- آمار سریع -->
            <div class="stats shadow mt-6">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <div class="stat-title">کل درخواست‌ها</div>
                    <div class="stat-value text-primary"><?php echo en2fa(count($requests)); ?></div>
                </div>
                
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <i class="fas fa-credit-card text-2xl"></i>
                    </div>
                    <div class="stat-title">کل پرداخت‌ها</div>
                    <div class="stat-value text-secondary"><?php echo en2fa(count($payments)); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- درخواست‌های مشتری -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-clipboard-list text-warning ml-2"></i>
                    درخواست‌های مشتری
                </h2>
                <div class="badge badge-info">
                    <?php echo en2fa(count($requests)); ?> درخواست
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>کد رهگیری</th>
                            <th>عنوان</th>
                            <th>تاریخ ثبت</th>
                            <th>هزینه</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr class="hover">
                            <td>
                                <code class="bg-base-200 px-2 py-1 rounded text-sm font-mono">
                                    <?php echo en2fa($request['tracking_code']); ?>
                                </code>
                            </td>
                            <td class="font-medium">
                                <?php echo htmlspecialchars($request['title']); ?>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-base-content/50 ml-1"></i>
                                    <?php echo en2fa($request['registration_date']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-neutral">
                                    <?php echo en2fa(formatNumber($request['cost'])); ?> ت
                                </div>
                            </td>
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
                                <div class="flex gap-2">
                                    <a href="view_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="مشاهده">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="print_receipt.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="چاپ">
                                        <i class="fas fa-print"></i>
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

    <!-- تاریخچه پرداخت‌ها -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-history text-success ml-2"></i>
                    تاریخچه پرداخت‌ها
                </h2>
                <div class="badge badge-success">
                    <?php echo en2fa(count($payments)); ?> پرداخت
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>نوع</th>
                            <th>مبلغ</th>
                            <th>توضیحات</th>
                            <th>رسید</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-base-content/50 ml-1"></i>
                                    <?php echo en2fa(jalali_date('Y/m/d', strtotime($payment['created_at']))); ?>
                                </div>
                            </td>
                            <td>
                                <div class="badge <?php echo $payment['payment_type'] == 'واریز' ? 'badge-success' : 'badge-error'; ?>">
                                    <i class="fas <?php echo $payment['payment_type'] == 'واریز' ? 'fa-arrow-down' : 'fa-arrow-up'; ?> ml-1"></i>
                                    <?php echo $payment['payment_type']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-neutral">
                                    <?php echo en2fa(formatNumber($payment['amount'])); ?> ت
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($payment['description']); ?></td>
                            <td>
                                <?php if ($payment['receipt_image']): ?>
                                    <a href="uploads/receipts/<?php echo $payment['receipt_image']; ?>" target="_blank" 
                                       class="btn btn-ghost btn-sm" title="مشاهده رسید">
                                        <i class="fas fa-image text-info"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-base-content/50">ندارد</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- دکمه‌های عملیات -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-xl mb-4">
                <i class="fas fa-tools text-accent ml-2"></i>
                عملیات
            </h2>
            
            <div class="flex flex-wrap gap-4">
                <a href="customers.php" class="btn btn-neutral">
                    <i class="fas fa-arrow-right ml-2"></i>
                    بازگشت به لیست
                </a>
                <a href="new_request.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    درخواست جدید
                </a>
                <a href="add_payment.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-success">
                    <i class="fas fa-credit-card ml-2"></i>
                    ثبت پرداخت
                </a>
                <a href="print_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-info">
                    <i class="fas fa-print ml-2"></i>
                    چاپ اطلاعات
                </a>
            </div>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>