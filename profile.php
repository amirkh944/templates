<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$message = '';
$user = getUser($_SESSION['user_id']);

// Process form submission
if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = 'نام کاربری الزامی است';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر نیست';
    }
    
    if (empty($phone)) {
        $errors[] = 'شماره تلفن الزامی است';
    }
    
    // Check username uniqueness
    if ($username !== $user['username']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'این نام کاربری قبلاً استفاده شده است';
        }
    }
    
    // Password validation
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'رمز عبور فعلی الزامی است';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'رمز عبور فعلی اشتباه است';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'تکرار رمز عبور صحیح نیست';
        }
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $phone, $hashedPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$username, $email, $phone, $_SESSION['user_id']]);
            }
            
            $_SESSION['username'] = $username;
            $message = '<div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span>پروفایل با موفقیت به‌روزرسانی شد</span></div>';
            $user = getUser($_SESSION['user_id']);
        } catch (Exception $e) {
            $message = '<div class="alert alert-error mb-6"><i class="fas fa-exclamation-triangle"></i><span>خطا در به‌روزرسانی پروفایل</span></div>';
        }
    } else {
        $message = '<div class="alert alert-error mb-6"><i class="fas fa-exclamation-triangle"></i><span>' . implode('<br>', $errors) . '</span></div>';
    }
}

$pageTitle = 'پروفایل کاربری - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'پروفایل کاربری']
];

include 'includes/header.php';
?>

<div class="space-y-8">
    
    <!-- Page Title -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-base-content mb-2">
            <i class="fas fa-user-edit text-primary ml-2"></i>
            پروفایل کاربری
        </h1>
        <p class="text-base-content/70 text-lg">
            اطلاعات شخصی و تنظیمات حساب کاربری خود را مدیریت کنید
        </p>
    </div>

    <?php echo $message; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body text-center">
                    
                    <!-- Avatar -->
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24">
                            <span class="text-3xl font-bold">
                                <?php echo mb_substr($user['username'], 0, 1, 'UTF-8'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-base-content">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>
                    
                    <div class="badge badge-<?php echo $user['is_admin'] ? 'error' : 'primary'; ?> badge-lg mb-4">
                        <i class="fas fa-<?php echo $user['is_admin'] ? 'crown' : 'user'; ?> ml-1"></i>
                        <?php echo $user['is_admin'] ? 'مدیر سیستم' : 'کاربر'; ?>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-center gap-2 text-base-content/70">
                            <i class="fas fa-calendar-alt"></i>
                            <span>عضویت: <?php echo en2fa(jalali_date('Y/m/d', strtotime($user['created_at']))); ?></span>
                        </div>
                        
                        <?php if (!empty($user['user_id_code'])): ?>
                        <div class="flex items-center justify-center gap-2 text-base-content/70">
                            <i class="fas fa-id-card"></i>
                            <span>کد کاربری: <?php echo en2fa($user['user_id_code']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Edit Profile Form -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-6">
                        <i class="fas fa-edit text-primary ml-2"></i>
                        ویرایش اطلاعات شخصی
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        
                        <!-- Account Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        <i class="fas fa-user ml-1"></i>
                                        نام کاربری
                                    </span>
                                </label>
                                <input type="text" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>"
                                       class="input input-bordered w-full" required>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        <i class="fas fa-envelope ml-1"></i>
                                        ایمیل
                                    </span>
                                </label>
                                <input type="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       class="input input-bordered w-full" required>
                            </div>
                            
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">
                                    <i class="fas fa-phone ml-1"></i>
                                    شماره تلفن
                                </span>
                            </label>
                            <input type="tel" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>"
                                   class="input input-bordered w-full" required
                                   placeholder="09123456789">
                        </div>
                        
                        <div class="divider">تغییر رمز عبور</div>
                        
                        <!-- Password Change -->
                        <div class="bg-base-200 p-6 rounded-lg space-y-4">
                            <p class="text-sm text-base-content/70 mb-4">
                                <i class="fas fa-info-circle text-info ml-1"></i>
                                برای تغییر رمز عبور، تمام فیلدهای زیر را پر کنید
                            </p>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        <i class="fas fa-lock ml-1"></i>
                                        رمز عبور فعلی
                                    </span>
                                </label>
                                <input type="password" name="current_password" 
                                       class="input input-bordered w-full"
                                       placeholder="رمز عبور فعلی خود را وارد کنید">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">
                                            <i class="fas fa-key ml-1"></i>
                                            رمز عبور جدید
                                        </span>
                                    </label>
                                    <input type="password" name="new_password" 
                                           class="input input-bordered w-full"
                                           placeholder="حداقل ۶ کاراکتر">
                                </div>
                                
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">
                                            <i class="fas fa-check ml-1"></i>
                                            تکرار رمز عبور جدید
                                        </span>
                                    </label>
                                    <input type="password" name="confirm_password" 
                                           class="input input-bordered w-full"
                                           placeholder="رمز عبور جدید را مجدداً وارد کنید">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="card-actions justify-end gap-4 pt-6">
                            <a href="dashboard.php" class="btn btn-ghost">
                                <i class="fas fa-times ml-2"></i>
                                انصراف
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save ml-2"></i>
                                ذخیره تغییرات
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    const currentPassword = document.querySelector('input[name="current_password"]');
    
    // Check password match
    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('رمزهای عبور مطابقت ندارند');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
    
    // If new password entered, current password is required
    newPassword.addEventListener('input', function() {
        if (this.value) {
            currentPassword.required = true;
        } else {
            currentPassword.required = false;
        }
    });
    
    form.addEventListener('submit', function(e) {
        if (newPassword.value && !currentPassword.value) {
            e.preventDefault();
            showToast('برای تغییر رمز عبور، وارد کردن رمز فعلی الزامی است', 'error');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>