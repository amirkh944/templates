<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

if (!isset($_GET['id'])) {
    header('Location: requests.php');
    exit;
}

$requestId = $_GET['id'];
$request = getRequest($requestId);

if (!$request) {
    header('Location: requests.php');
    exit;
}

$payments = getCustomerPayments($request['customer_id']);
$balance = getCustomerBalance($request['customer_id']);

$pageTitle = 'مشاهده درخواست - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'درخواست‌ها', 'url' => 'requests.php'],
    ['title' => 'مشاهده درخواست']
];

include 'includes/header.php';
?>
<!-- صفحه مشاهده درخواست -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-primary to-secondary rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-clipboard-list ml-2"></i>
                    مشاهده درخواست
                </h1>
                <p class="text-lg">
                    کد رهگیری: <?php echo en2fa($request['tracking_code']); ?>
                </p>
                <div class="badge badge-accent badge-lg mt-2">
                    <?php echo $request['status']; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- اطلاعات درخواست -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-info-circle text-info ml-2"></i>
                جزئیات درخواست
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">عنوان درخواست</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-tag text-base-content/50 ml-2"></i>
                            <span><?php echo htmlspecialchars($request['title']); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">مدل دستگاه</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-mobile-alt text-base-content/50 ml-2"></i>
                            <span><?php echo $request['device_model'] ?: 'ندارد'; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IMEI اول</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-barcode text-base-content/50 ml-2"></i>
                            <span><?php echo $request['imei1'] ? en2fa($request['imei1']) : 'ندارد'; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IMEI دوم</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-barcode text-base-content/50 ml-2"></i>
                            <span><?php echo $request['imei2'] ? en2fa($request['imei2']) : 'ندارد'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">مدت زمان احتمالی</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-clock text-base-content/50 ml-2"></i>
                            <span><?php echo $request['estimated_duration'] ?: 'تعیین نشده'; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">هزینه درخواست</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-money-bill text-base-content/50 ml-2"></i>
                            <span class="font-bold text-success"><?php echo en2fa(formatNumber($request['cost'])); ?> تومان</span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">تاریخ ثبت</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                            <span><?php echo en2fa($request['registration_date']); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">وضعیت درخواست</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-info text-base-content/50 ml-2"></i>
                            <span class="badge <?php 
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
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- توضیحات -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">شرح مشکل</span>
                    </label>
                    <div class="textarea textarea-bordered bg-base-200 min-h-[120px] p-4">
                        <?php echo $request['problem_description'] ? nl2br(htmlspecialchars($request['problem_description'])) : 'ندارد'; ?>
                    </div>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">اقدامات قابل انجام</span>
                    </label>
                    <div class="textarea textarea-bordered bg-base-200 min-h-[120px] p-4">
                        <?php echo $request['actions_required'] ? nl2br(htmlspecialchars($request['actions_required'])) : 'ندارد'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- اطلاعات مشتری -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-user text-primary ml-2"></i>
                اطلاعات مشتری
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">نام و نام خانوادگی</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-user text-base-content/50 ml-2"></i>
                            <span><?php echo htmlspecialchars($request['customer_name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">تلفن همراه</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-phone text-base-content/50 ml-2"></i>
                            <span><?php echo $request['customer_phone']; ?></span>
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
                            <span><?php echo $request['customer_email'] ?: 'ندارد'; ?></span>
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
        </div>
    </div>
    
    <!-- عملیات -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-tools text-accent ml-2"></i>
                عملیات
            </h2>
            
            <div class="flex flex-wrap gap-4">
                <a href="requests.php" class="btn btn-neutral">
                    <i class="fas fa-arrow-right ml-2"></i>
                    بازگشت
                </a>
                <a href="edit_request.php?id=<?php echo $request['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit ml-2"></i>
                    ویرایش درخواست
                </a>
                <a href="print_receipt.php?id=<?php echo $request['id']; ?>" class="btn btn-success">
                    <i class="fas fa-print ml-2"></i>
                    چاپ رسید
                </a>
                <a href="add_payment.php?request_id=<?php echo $request['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-credit-card ml-2"></i>
                    ثبت پرداخت
                </a>
                <a href="view_customer.php?id=<?php echo $request['customer_id']; ?>" class="btn btn-info">
                    <i class="fas fa-user ml-2"></i>
                    مشاهده مشتری
                </a>
            </div>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>