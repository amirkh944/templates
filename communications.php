<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$message = '';
$contacts = getAllContacts();
$customers = getAllCustomers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_POST['customer_id'];
    $contactType = $_POST['contact_type'];
    $subject = $_POST['subject'];
    $contactMessage = $_POST['message'];
    
    try {
        addContact($customerId, $contactType, $subject, $contactMessage);
        $message = '<div class="alert alert-success mb-6">
                      <i class="fas fa-check-circle"></i>
                      <span>تماس با موفقیت ثبت شد.</span>
                    </div>';
        
        // بارگذاری مجدد لیست تماس‌ها
        $contacts = getAllContacts();
        
        // پاک کردن فرم
        $_POST = array();
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-error mb-6">
                      <i class="fas fa-exclamation-circle"></i>
                      <span>خطا در ثبت تماس: ' . $e->getMessage() . '</span>
                    </div>';
    }
}

$pageTitle = 'مدیریت ارتباطات - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'مدیریت ارتباطات']
];

include 'includes/header.php';
?>
<!-- صفحه مدیریت ارتباطات -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-secondary to-accent rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-comments ml-2"></i>
                    مدیریت ارتباطات
                </h1>
                <p class="text-lg">
                    ثبت و مدیریت تماس‌ها و ارتباطات با مشتریان
                </p>
            </div>
        </div>
    </div>
    
    <?php echo $message; ?>
    
    <!-- آمار سریع ارتباطات -->
    <div class="stats shadow w-full">
        <div class="stat">
            <div class="stat-figure text-primary">
                <i class="fas fa-comments text-3xl"></i>
            </div>
            <div class="stat-title">کل ارتباطات</div>
            <div class="stat-value text-primary"><?php echo en2fa(count($contacts)); ?></div>
            <div class="stat-desc">تماس‌های ثبت‌شده</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-secondary">
                <i class="fas fa-phone text-3xl"></i>
            </div>
            <div class="stat-title">تماس‌های امروز</div>
            <div class="stat-value text-secondary">
                <?php 
                $todayContacts = array_filter($contacts, function($contact) {
                    return date('Y-m-d', strtotime($contact['contact_date'])) === date('Y-m-d');
                });
                echo en2fa(count($todayContacts));
                ?>
            </div>
            <div class="stat-desc">ارتباطات امروز</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-accent">
                <i class="fas fa-chart-line text-3xl"></i>
            </div>
            <div class="stat-title">مشتریان فعال</div>
            <div class="stat-value text-accent">
                <?php 
                $activeCustomers = array_unique(array_column($contacts, 'customer_id'));
                echo en2fa(count($activeCustomers));
                ?>
            </div>
            <div class="stat-desc">مشتریان با تماس</div>
        </div>
    </div>
    
    <!-- فرم ثبت تماس جدید -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-plus-circle text-primary ml-2"></i>
                ثبت تماس و ارتباط جدید
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">مشتری *</span>
                        </label>
                        <select name="customer_id" required class="select select-bordered w-full">
                            <option value="">انتخاب مشتری</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" <?php echo ($_POST['customer_id'] ?? '') == $customer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name']); ?> - <?php echo $customer['phone']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">نوع تماس *</span>
                        </label>
                        <select name="contact_type" required class="select select-bordered w-full">
                            <option value="">انتخاب نوع تماس</option>
                            <option value="تماس" <?php echo ($_POST['contact_type'] ?? '') == 'تماس' ? 'selected' : ''; ?>>
                                <i class="fas fa-phone ml-2"></i>تماس تلفنی
                            </option>
                            <option value="ایمیل" <?php echo ($_POST['contact_type'] ?? '') == 'ایمیل' ? 'selected' : ''; ?>>
                                <i class="fas fa-envelope ml-2"></i>ایمیل
                            </option>
                            <option value="پیامک" <?php echo ($_POST['contact_type'] ?? '') == 'پیامک' ? 'selected' : ''; ?>>
                                <i class="fas fa-sms ml-2"></i>پیامک
                            </option>
                            <option value="واتساپ" <?php echo ($_POST['contact_type'] ?? '') == 'واتساپ' ? 'selected' : ''; ?>>
                                <i class="fab fa-whatsapp ml-2"></i>واتساپ
                            </option>
                            <option value="حضوری" <?php echo ($_POST['contact_type'] ?? '') == 'حضوری' ? 'selected' : ''; ?>>
                                <i class="fas fa-user ml-2"></i>مراجعه حضوری
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">موضوع</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-tag"></i>
                            </span>
                            <input type="text" name="subject" 
                                   class="input input-bordered w-full"
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                                   placeholder="موضوع تماس یا ارتباط">
                        </div>
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">پیام و توضیحات *</span>
                        </label>
                        <textarea name="message" rows="4" required 
                                  class="textarea textarea-bordered"
                                  placeholder="متن پیام، نتیجه تماس یا توضیحات کامل ارتباط"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-phone-alt ml-2"></i>
                        ثبت تماس
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- لیست تماس‌ها -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-history text-info ml-2"></i>
                    تاریخچه ارتباطات
                </h2>
                <div class="badge badge-info">
                    <?php echo en2fa(count($contacts)); ?> تماس
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>مشتری</th>
                            <th>نوع تماس</th>
                            <th>موضوع</th>
                            <th>پیام</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                                    <div>
                                        <div class="font-medium">
                                            <?php echo en2fa(jalali_date('Y/m/d', strtotime($contact['contact_date']))); ?>
                                        </div>
                                        <div class="text-sm text-base-content/60">
                                            <?php echo en2fa(jalali_date('H:i', strtotime($contact['contact_date']))); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="avatar placeholder ml-3">
                                        <div class="bg-neutral-focus text-neutral-content rounded-full w-8">
                                            <span class="text-xs">
                                                <?php echo mb_substr($contact['customer_name'], 0, 1); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($contact['customer_name']); ?></div>
                                        <div class="text-sm text-base-content/60" dir="ltr"><?php echo $contact['customer_phone']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge <?php 
                                    switch($contact['contact_type']) {
                                        case 'تماس': echo 'badge-info'; break;
                                        case 'ایمیل': echo 'badge-success'; break;
                                        case 'پیامک': echo 'badge-secondary'; break;
                                        case 'واتساپ': echo 'badge-success'; break;
                                        case 'حضوری': echo 'badge-warning'; break;
                                        default: echo 'badge-neutral';
                                    }
                                    ?>">
                                    <i class="<?php 
                                        switch($contact['contact_type']) {
                                            case 'تماس': echo 'fas fa-phone'; break;
                                            case 'ایمیل': echo 'fas fa-envelope'; break;
                                            case 'پیامک': echo 'fas fa-sms'; break;
                                            case 'واتساپ': echo 'fab fa-whatsapp'; break;
                                            case 'حضوری': echo 'fas fa-user'; break;
                                            default: echo 'fas fa-comment';
                                        }
                                    ?> ml-1"></i>
                                    <?php echo $contact['contact_type']; ?>
                                </div>
                            </td>
                            <td class="font-medium">
                                <?php echo $contact['subject'] ? htmlspecialchars($contact['subject']) : '<span class="text-base-content/40">بدون موضوع</span>'; ?>
                            </td>
                            <td>
                                <div class="max-w-xs">
                                    <div class="tooltip" data-tip="کلیک برای مشاهده کامل">
                                        <button onclick="showFullMessage('<?php echo addslashes($contact['message']); ?>')" 
                                                class="text-right">
                                            <?php echo nl2br(htmlspecialchars(substr($contact['message'], 0, 80))); ?>
                                            <?php if (strlen($contact['message']) > 80): ?>
                                                <span class="text-base-content/50">...</span>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button onclick="showFullMessage('<?php echo addslashes($contact['message']); ?>')" 
                                            class="btn btn-ghost btn-sm" title="مشاهده کامل">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($contacts)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-comments text-6xl text-base-content/20 mb-4"></i>
                    <h3 class="text-xl font-semibold text-base-content/60 mb-2">هیچ تماسی ثبت نشده</h3>
                    <p class="text-base-content/40 mb-6">برای شروع، اولین تماس خود را ثبت کنید</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal برای نمایش پیام کامل -->
<dialog id="messageModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-comment text-primary ml-2"></i>
            متن کامل پیام
        </h3>
        <div id="fullMessage" class="whitespace-pre-wrap text-base-content py-4"></div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-primary">بستن</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function showFullMessage(message) {
        document.getElementById('fullMessage').textContent = message;
        document.getElementById('messageModal').showModal();
    }
</script>

<?php include 'includes/footer.php'; ?>