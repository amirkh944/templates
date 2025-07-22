<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$query = $_GET['q'] ?? '';
$results = [];
$message = '';

if ($query) {
    $results = searchRequests($query);
    if (empty($results)) {
        $message = '<div class="alert alert-warning mb-6">
                      <i class="fas fa-search"></i>
                      <span>هیچ درخواستی با این کلمه کلیدی یافت نشد.</span>
                    </div>';
    }
}

$pageTitle = 'جستجوی درخواست‌ها - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'جستجوی درخواست‌ها']
];

include 'includes/header.php';
?>

<!-- صفحه جستجوی درخواست‌ها -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-accent to-secondary rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-search ml-2"></i>
                    جستجوی درخواست‌ها
                </h1>
                <p class="text-lg">
                    جستجوی پیشرفته در بین تمام درخواست‌ها
                </p>
            </div>
        </div>
    </div>
    
    <!-- فرم جستجو -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-filter text-primary ml-2"></i>
                جستجوی هوشمند
            </h2>
            
            <form method="GET" class="space-y-6">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">عبارت جستجو</span>
                        <span class="label-text-alt">نام مشتری، تلفن، IMEI یا کد رهگیری</span>
                    </label>
                    <div class="input-group">
                        <input type="text" name="q" 
                               class="input input-bordered input-lg w-full"
                               value="<?php echo htmlspecialchars($query); ?>"
                               placeholder="مثال: محمد احمدی، ۰۹۱۲۳۴۵۶۷۸۹، ABC123 یا ۸۶۱۱۲۳۴۵۶۷۸۹"
                               autofocus>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search ml-2"></i>
                            جستجو
                        </button>
                    </div>
                </div>
                
                <?php if ($query): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <div class="text-sm">جستجو برای: <strong>"<?php echo htmlspecialchars($query); ?>"</strong></div>
                        <div class="text-xs mt-1">
                            <a href="?" class="link link-hover">
                                <i class="fas fa-times ml-1"></i>پاک کردن جستجو
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php echo $message; ?>

    <!-- نتایج جستجو -->
    <?php if ($query && !empty($results)): ?>
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-list-ul text-success ml-2"></i>
                    نتایج جستجو
                </h2>
                <div class="flex gap-2">
                    <div class="badge badge-success badge-lg">
                        <?php echo en2fa(count($results)); ?> درخواست یافت شد
                    </div>
                    <button class="btn btn-ghost btn-sm" onclick="window.print()">
                        <i class="fas fa-print ml-2"></i>
                        چاپ نتایج
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>کد رهگیری</th>
                            <th>عنوان</th>
                            <th>مشتری</th>
                            <th>دستگاه</th>
                            <th>IMEI</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $request): ?>
                        <tr class="hover">
                            <td>
                                <code class="bg-base-200 px-2 py-1 rounded text-sm font-mono">
                                    <?php echo en2fa($request['tracking_code']); ?>
                                </code>
                            </td>
                            <td class="font-medium">
                                <div class="max-w-xs">
                                    <div class="tooltip tooltip-right" data-tip="<?php echo htmlspecialchars($request['title']); ?>">
                                        <?php echo htmlspecialchars(mb_substr($request['title'], 0, 30) . (mb_strlen($request['title']) > 30 ? '...' : '')); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="avatar placeholder ml-3">
                                        <div class="bg-neutral-focus text-neutral-content rounded-full w-8">
                                            <span class="text-xs">
                                                <?php echo mb_substr($request['customer_name'], 0, 1); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm"><?php echo htmlspecialchars($request['customer_name']); ?></div>
                                        <div class="text-xs text-base-content/60" dir="ltr"><?php echo $request['customer_phone']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-mobile-alt text-base-content/50 ml-2"></i>
                                    <?php echo $request['device_model'] ?: '<span class="text-base-content/40">نامشخص</span>'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="space-y-1">
                                    <?php if ($request['imei1']): ?>
                                        <div class="badge badge-ghost badge-sm">IMEI1: <?php echo en2fa($request['imei1']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($request['imei2']): ?>
                                        <div class="badge badge-ghost badge-sm">IMEI2: <?php echo en2fa($request['imei2']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$request['imei1'] && !$request['imei2']): ?>
                                        <span class="text-base-content/40 text-xs">ثبت نشده</span>
                                    <?php endif; ?>
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
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                                    <?php echo en2fa(jalali_date('Y/m/d', strtotime($request['created_at']))); ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <a href="view_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="مشاهده جزئیات">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="edit_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="ویرایش">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    <a href="print_receipt.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="چاپ رسید">
                                        <i class="fas fa-print text-secondary"></i>
                                    </a>
                                    <a href="send_sms.php?request_id=<?php echo $request['id']; ?>" 
                                       class="btn btn-ghost btn-sm" title="ارسال پیامک">
                                        <i class="fas fa-sms text-success"></i>
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
    
    <?php elseif (!$query): ?>
    <!-- راهنمای جستجو -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- امکانات جستجو -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4">
                    <i class="fas fa-search-plus text-primary ml-2"></i>
                    امکانات جستجو
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center p-3 rounded-lg bg-base-200">
                        <div class="avatar placeholder ml-3">
                            <div class="bg-primary text-primary-content rounded-full w-10">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">جستجو بر اساس نام مشتری</div>
                            <div class="text-sm text-base-content/60">مثال: محمد احمدی، علی رضایی</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 rounded-lg bg-base-200">
                        <div class="avatar placeholder ml-3">
                            <div class="bg-success text-success-content rounded-full w-10">
                                <i class="fas fa-phone"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">جستجو بر اساس شماره تلفن</div>
                            <div class="text-sm text-base-content/60">مثال: ۰۹۱۲۳۴۵۶۷۸۹</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 rounded-lg bg-base-200">
                        <div class="avatar placeholder ml-3">
                            <div class="bg-secondary text-secondary-content rounded-full w-10">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">جستجو بر اساس IMEI</div>
                            <div class="text-sm text-base-content/60">مثال: ۸۶۱۱۲۳۴۵۶۷۸۹۰۱۲۳</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 rounded-lg bg-base-200">
                        <div class="avatar placeholder ml-3">
                            <div class="bg-accent text-accent-content rounded-full w-10">
                                <i class="fas fa-barcode"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">جستجو بر اساس کد رهگیری</div>
                            <div class="text-sm text-base-content/60">مثال: ABC123، XYZ456</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- دسترسی سریع -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4">
                    <i class="fas fa-bolt text-warning ml-2"></i>
                    دسترسی سریع
                </h2>
                
                <div class="space-y-3">
                    <a href="requests.php" class="btn btn-outline btn-primary w-full justify-start">
                        <i class="fas fa-list ml-2"></i>
                        مشاهده تمام درخواست‌ها
                    </a>
                    <a href="new_request.php" class="btn btn-outline btn-success w-full justify-start">
                        <i class="fas fa-plus-circle ml-2"></i>
                        ثبت درخواست جدید
                    </a>
                    <a href="customers.php" class="btn btn-outline btn-info w-full justify-start">
                        <i class="fas fa-users ml-2"></i>
                        مدیریت مشتریان
                    </a>
                    <a href="communications.php" class="btn btn-outline btn-secondary w-full justify-start">
                        <i class="fas fa-comments ml-2"></i>
                        ارتباطات و تماس‌ها
                    </a>
                </div>
                
                <div class="divider">آمار سریع</div>
                
                <div class="stats bg-base-200 rounded-lg">
                    <div class="stat">
                        <div class="stat-title text-xs">درخواست‌های امروز</div>
                        <div class="stat-value text-primary text-lg">
                            <?php 
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE DATE(created_at) = CURDATE()");
                                echo en2fa($stmt->fetchColumn());
                            } catch (Exception $e) {
                                echo '۰';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <?php endif; ?>
    
</div>

<?php include 'includes/footer.php'; ?>