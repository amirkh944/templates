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

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $deviceModel = $_POST['device_model'];
    $imei1 = $_POST['imei1'];
    $imei2 = $_POST['imei2'];
    $problemDescription = $_POST['problem_description'];
    $estimatedDuration = $_POST['estimated_duration'];
    $actionsRequired = $_POST['actions_required'];
    $cost = floatval($_POST['cost']);
    $status = $_POST['status'];
    
    try {
        updateRequest($requestId, $title, $deviceModel, $imei1, $imei2, $problemDescription, $estimatedDuration, $actionsRequired, $cost, $status);
        $message = '<div class="alert alert-success mb-6">
                      <i class="fas fa-check-circle"></i>
                      <span>درخواست با موفقیت به‌روزرسانی شد.</span>
                    </div>';
        
        // بارگذاری مجدد اطلاعات درخواست
        $request = getRequest($requestId);
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-error mb-6">
                      <i class="fas fa-exclamation-circle"></i>
                      <span>خطا در به‌روزرسانی درخواست: ' . $e->getMessage() . '</span>
                    </div>';
    }
}

$pageTitle = 'ویرایش درخواست - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'درخواست‌ها', 'url' => 'requests.php'],
    ['title' => 'ویرایش درخواست']
];

include 'includes/header.php';
?>
<!-- صفحه ویرایش درخواست -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-warning to-accent rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-edit ml-2"></i>
                    ویرایش درخواست
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
    
    <?php echo $message; ?>
    
    <form method="POST" class="space-y-8">
        
        <!-- اطلاعات مشتری -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-xl mb-6">
                    <i class="fas fa-user text-info ml-2"></i>
                    اطلاعات مشتری
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <span class="label-text font-semibold">شماره تماس</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-phone text-base-content/50 ml-2"></i>
                            <span><?php echo $request['customer_phone']; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">آدرس ایمیل</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-envelope text-base-content/50 ml-2"></i>
                            <span><?php echo $request['customer_email'] ?: 'ندارد'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- اطلاعات درخواست -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-xl mb-6">
                    <i class="fas fa-clipboard-list text-primary ml-2"></i>
                    اطلاعات درخواست
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">عنوان درخواست *</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-tag"></i>
                            </span>
                            <input type="text" name="title" required 
                                   class="input input-bordered w-full"
                                   placeholder="عنوان درخواست را وارد کنید"
                                   value="<?php echo htmlspecialchars($request['title']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">مدل دستگاه</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-mobile-alt"></i>
                            </span>
                            <input type="text" name="device_model" 
                                   class="input input-bordered w-full"
                                   placeholder="مدل دستگاه"
                                   value="<?php echo htmlspecialchars($request['device_model']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">مدت زمان احتمالی</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-clock"></i>
                            </span>
                            <input type="text" name="estimated_duration" 
                                   class="input input-bordered w-full"
                                   placeholder="مدت زمان احتمالی"
                                   value="<?php echo htmlspecialchars($request['estimated_duration']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IMEI اول</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-barcode"></i>
                            </span>
                            <input type="text" name="imei1" 
                                   class="input input-bordered w-full"
                                   placeholder="IMEI اول"
                                   value="<?php echo htmlspecialchars($request['imei1']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IMEI دوم</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-barcode"></i>
                            </span>
                            <input type="text" name="imei2" 
                                   class="input input-bordered w-full"
                                   placeholder="IMEI دوم"
                                   value="<?php echo htmlspecialchars($request['imei2']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">هزینه درخواست (تومان)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-money-bill"></i>
                            </span>
                            <input type="number" name="cost" step="0.01" 
                                   class="input input-bordered w-full"
                                   placeholder="هزینه به تومان"
                                   value="<?php echo $request['cost']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">وضعیت درخواست</span>
                        </label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="در حال پردازش" <?php echo $request['status'] == 'در حال پردازش' ? 'selected' : ''; ?>>در حال پردازش</option>
                            <option value="تکمیل شده" <?php echo $request['status'] == 'تکمیل شده' ? 'selected' : ''; ?>>تکمیل شده</option>
                            <option value="لغو شده" <?php echo $request['status'] == 'لغو شده' ? 'selected' : ''; ?>>لغو شده</option>
                        </select>
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">شرح مشکل</span>
                        </label>
                        <textarea name="problem_description" rows="4" 
                                  class="textarea textarea-bordered"
                                  placeholder="شرح کامل مشکل را وارد کنید..."><?php echo htmlspecialchars($request['problem_description']); ?></textarea>
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">اقدامات قابل انجام</span>
                        </label>
                        <textarea name="actions_required" rows="4" 
                                  class="textarea textarea-bordered"
                                  placeholder="اقدامات و راه‌حل‌های پیشنهادی..."><?php echo htmlspecialchars($request['actions_required']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- اطلاعات ثبت -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-xl mb-6">
                    <i class="fas fa-info-circle text-secondary ml-2"></i>
                    اطلاعات ثبت
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <span class="label-text font-semibold">کد رهگیری</span>
                        </label>
                        <div class="input input-bordered bg-base-200 flex items-center">
                            <i class="fas fa-hashtag text-base-content/50 ml-2"></i>
                            <code class="bg-base-300 px-2 py-1 rounded">
                                <?php echo en2fa($request['tracking_code']); ?>
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- دکمه‌های عملیات -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <div class="flex flex-wrap gap-4 justify-end">
                    <a href="requests.php" class="btn btn-neutral">
                        <i class="fas fa-times ml-2"></i>
                        انصراف
                    </a>
                    <a href="view_request.php?id=<?php echo $request['id']; ?>" class="btn btn-info">
                        <i class="fas fa-eye ml-2"></i>
                        مشاهده
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save ml-2"></i>
                        به‌روزرسانی درخواست
                    </button>
                </div>
            </div>
        </div>
        
    </form>
    
</div>

<?php include 'includes/footer.php'; ?>