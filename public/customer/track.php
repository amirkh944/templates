<?php
require_once '../../config.php';
require_once '../../functions.php';

$pageTitle = 'پیگیری درخواست - پاسخگو رایانه';
$trackingCode = '';
$request = null;
$customer = null;
$payments = [];
$error = '';
$success = '';

// پردازش فرم جستجو
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trackingCode = trim($_POST['tracking_code']);
    
    if (empty($trackingCode)) {
        $error = 'لطفاً کد رهگیری را وارد کنید';
    } else {
        // جستجوی درخواست با کد رهگیری
        $stmt = $pdo->prepare("
            SELECT r.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email
            FROM requests r
            JOIN customers c ON r.customer_id = c.id
            WHERE r.tracking_code = ?
        ");
        $stmt->execute([$trackingCode]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            $error = 'درخواستی با این کد رهگیری یافت نشد';
        } else {
            $success = 'درخواست شما یافت شد';
            
            // دریافت اطلاعات مشتری
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$request['customer_id']]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // دریافت پرداخت‌های مرتبط
            $stmt = $pdo->prepare("
                SELECT * FROM payments 
                WHERE customer_id = ? OR request_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$request['customer_id'], $request['id']]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

include '../../includes/header.php';
?>

<div class="hero min-h-screen bg-gradient-to-br from-primary/5 to-secondary/5">
    <div class="hero-content text-center">
        <div class="max-w-6xl">
            
            <!-- هدر صفحه -->
            <div class="mb-12">
                <div class="w-20 h-20 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-3xl text-primary-content"></i>
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-base-content mb-4">
                    پیگیری درخواست
                </h1>
                <p class="text-lg text-base-content/70 max-w-2xl mx-auto">
                    با وارد کردن کد رهگیری ۷ رقمی، وضعیت درخواست خود را به صورت آنلاین پیگیری کنید
                </p>
            </div>
            
            <!-- فرم جستجو -->
            <div class="card w-full max-w-md mx-auto shadow-2xl bg-base-100 border border-base-300 mb-8">
                <form class="card-body" method="POST">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium text-lg">
                                <i class="fas fa-barcode ml-2 text-primary"></i>
                                کد رهگیری ۷ رقمی
                            </span>
                        </label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="tracking_code" 
                                value="<?php echo htmlspecialchars($trackingCode); ?>"
                                placeholder="1234567"
                                class="input input-bordered input-lg flex-1 text-center tracking-widest font-mono"
                                pattern="[0-9]{7}"
                                maxlength="7"
                                required
                                autofocus
                            />
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">
                                کد رهگیری در زمان ثبت درخواست به شما ارائه شده است
                            </span>
                        </label>
                    </div>
                </form>
            </div>
            
            <!-- پیام‌های خطا و موفقیت -->
            <?php if ($error): ?>
            <div class="alert alert-error max-w-md mx-auto mb-6">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success max-w-md mx-auto mb-6">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
            <?php endif; ?>
            
            <!-- نمایش اطلاعات درخواست -->
            <?php if ($request): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
                
                <!-- کارت اطلاعات کلی -->
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="card-title text-2xl">
                                <i class="fas fa-info-circle text-primary ml-2"></i>
                                اطلاعات درخواست
                            </h2>
                            <div class="badge badge-lg <?php 
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
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">کد رهگیری:</span>
                                <div class="flex items-center gap-2">
                                    <code class="bg-base-200 px-3 py-1 rounded text-lg font-bold">
                                        <?php echo en2fa($request['tracking_code']); ?>
                                    </code>
                                    <button onclick="copyToClipboard('<?php echo $request['tracking_code']; ?>')" 
                                            class="btn btn-ghost btn-sm">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">عنوان:</span>
                                <span class="font-bold"><?php echo htmlspecialchars($request['title']); ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">تاریخ ثبت:</span>
                                <span><?php echo en2fa(jalali_date('Y/m/d H:i', strtotime($request['created_at']))); ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">هزینه:</span>
                                <span class="text-2xl font-bold text-primary">
                                    <?php echo en2fa(number_format($request['cost'])); ?> 
                                    <span class="text-sm font-normal">تومان</span>
                                </span>
                            </div>
                            
                            <?php if ($request['estimated_duration']): ?>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">مدت زمان تخمینی:</span>
                                <span><?php echo htmlspecialchars($request['estimated_duration']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- دکمه‌های عملیات -->
                        <div class="card-actions justify-center mt-6">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print ml-2"></i>
                                چاپ رسید
                            </button>
                            <a href="mailto:support@pasokhraya.com?subject=پیگیری درخواست <?php echo $request['tracking_code']; ?>" 
                               class="btn btn-outline">
                                <i class="fas fa-envelope ml-2"></i>
                                تماس با پشتیبانی
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- کارت جزئیات فنی -->
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h2 class="card-title text-2xl mb-6">
                            <i class="fas fa-cogs text-secondary ml-2"></i>
                            جزئیات فنی
                        </h2>
                        
                        <?php if ($request['device_model']): ?>
                        <div class="mb-4">
                            <h4 class="font-medium text-base-content/70 mb-2">مدل دستگاه:</h4>
                            <p class="bg-base-200 p-3 rounded-lg">
                                <?php echo htmlspecialchars($request['device_model']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($request['problem_description']): ?>
                        <div class="mb-4">
                            <h4 class="font-medium text-base-content/70 mb-2">توضیحات مشکل:</h4>
                            <p class="bg-base-200 p-3 rounded-lg leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($request['problem_description'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($request['actions_required']): ?>
                        <div class="mb-4">
                            <h4 class="font-medium text-base-content/70 mb-2">اقدامات در حال انجام:</h4>
                            <div class="bg-info/10 border border-info/30 p-3 rounded-lg">
                                <p class="leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($request['actions_required'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- اطلاعات IMEI -->
                        <?php if ($request['imei1'] || $request['imei2']): ?>
                        <div class="grid grid-cols-1 gap-3">
                            <?php if ($request['imei1']): ?>
                            <div>
                                <span class="font-medium text-base-content/70">IMEI 1:</span>
                                <code class="bg-base-200 px-2 py-1 rounded mr-2">
                                    <?php echo en2fa($request['imei1']); ?>
                                </code>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($request['imei2']): ?>
                            <div>
                                <span class="font-medium text-base-content/70">IMEI 2:</span>
                                <code class="bg-base-200 px-2 py-1 rounded mr-2">
                                    <?php echo en2fa($request['imei2']); ?>
                                </code>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- اطلاعات مشتری -->
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h2 class="card-title text-2xl mb-6">
                            <i class="fas fa-user text-success ml-2"></i>
                            اطلاعات مشتری
                        </h2>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">نام:</span>
                                <span class="font-bold"><?php echo htmlspecialchars($customer['name']); ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">تلفن:</span>
                                <a href="tel:<?php echo $customer['phone']; ?>" 
                                   class="link link-primary font-mono">
                                    <?php echo $customer['phone']; ?>
                                </a>
                            </div>
                            
                            <?php if ($customer['email']): ?>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">ایمیل:</span>
                                <a href="mailto:<?php echo $customer['email']; ?>" 
                                   class="link link-primary">
                                    <?php echo htmlspecialchars($customer['email']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-base-content/70">عضویت از:</span>
                                <span><?php echo en2fa(jalali_date('Y/m/d', strtotime($customer['created_at']))); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- اطلاعات مالی -->
                <?php if (!empty($payments)): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h2 class="card-title text-2xl mb-6">
                            <i class="fas fa-credit-card text-warning ml-2"></i>
                            تراکنش‌های مالی
                        </h2>
                        
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>تاریخ</th>
                                        <th>نوع</th>
                                        <th>مبلغ</th>
                                        <th>توضیحات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <?php echo en2fa(jalali_date('Y/m/d', strtotime($payment['created_at']))); ?>
                                        </td>
                                        <td>
                                            <div class="badge <?php echo $payment['payment_type'] == 'واریز' ? 'badge-success' : 'badge-error'; ?>">
                                                <?php echo $payment['payment_type']; ?>
                                            </div>
                                        </td>
                                        <td class="font-bold">
                                            <?php echo en2fa(number_format($payment['amount'])); ?> تومان
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['description'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            <?php else: ?>
            
            <!-- راهنمای استفاده -->
            <div class="card bg-base-100 shadow-xl border border-base-300 max-w-2xl mx-auto">
                <div class="card-body">
                    <h2 class="card-title text-2xl justify-center mb-6">
                        <i class="fas fa-question-circle text-info ml-2"></i>
                        راهنمای استفاده
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-right">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-primary-content font-bold">۱</span>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">کد رهگیری را وارد کنید</h4>
                                    <p class="text-sm text-base-content/70">
                                        کد ۷ رقمی که هنگام ثبت درخواست دریافت کرده‌اید
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-secondary rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-secondary-content font-bold">۲</span>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">وضعیت را مشاهده کنید</h4>
                                    <p class="text-sm text-base-content/70">
                                        تمامی جزئیات و وضعیت فعلی درخواست شما
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-accent-content font-bold">۳</span>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">رسید را چاپ کنید</h4>
                                    <p class="text-sm text-base-content/70">
                                        امکان چاپ رسید کامل درخواست شما
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-info rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-info-content font-bold">۴</span>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">با پشتیبانی تماس بگیرید</h4>
                                    <p class="text-sm text-base-content/70">
                                        در صورت نیاز به راهنمایی بیشتر
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="text-center">
                        <h4 class="font-bold mb-3">نیاز به کمک دارید؟</h4>
                        <div class="flex flex-wrap justify-center gap-3">
                            <a href="tel:02122334455" class="btn btn-outline btn-sm">
                                <i class="fas fa-phone ml-1"></i>
                                ۰۲۱-۲۲۳۳۴۴۵۵
                            </a>
                            <a href="mailto:support@pasokhraya.com" class="btn btn-outline btn-sm">
                                <i class="fas fa-envelope ml-1"></i>
                                ایمیل پشتیبانی
                            </a>
                            <a href="https://t.me/pasokhraya" class="btn btn-outline btn-sm">
                                <i class="fab fa-telegram ml-1"></i>
                                تلگرام
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @media print {
        .navbar, .footer, .btn, .hero { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
        body { background: white !important; color: black !important; }
    }
</style>

<script>
    // اعتبارسنجی کد رهگیری
    document.querySelector('input[name="tracking_code"]').addEventListener('input', function(e) {
        // فقط اعداد مجاز
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // محدود کردن به ۷ رقم
        if (this.value.length > 7) {
            this.value = this.value.slice(0, 7);
        }
    });
    
    // فوکوس خودکار
    document.addEventListener('DOMContentLoaded', function() {
        const trackingInput = document.querySelector('input[name="tracking_code"]');
        if (trackingInput && !trackingInput.value) {
            trackingInput.focus();
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>