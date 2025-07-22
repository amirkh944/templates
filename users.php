<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkAdmin();

$message = '';
$users = getAllUsers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    try {
        createUser($username, $password, $email, $phone);
        $message = '<div class="alert alert-success mb-6">
                      <i class="fas fa-check-circle"></i>
                      <span>کاربر با موفقیت ایجاد شد.</span>
                    </div>';
        
        // بارگذاری مجدد لیست کاربران
        $users = getAllUsers();
        
        // پاک کردن فرم
        $_POST = array();
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-error mb-6">
                      <i class="fas fa-exclamation-circle"></i>
                      <span>خطا در ایجاد کاربر: ' . $e->getMessage() . '</span>
                    </div>';
    }
}

$pageTitle = 'مدیریت کاربران - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'داشبورد', 'url' => 'dashboard.php'],
    ['title' => 'مدیریت کاربران']
];

include 'includes/header.php';
?>
<!-- صفحه مدیریت کاربران -->
<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="hero bg-gradient-to-r from-error to-warning rounded-3xl text-primary-content">
        <div class="hero-content text-center py-8">
            <div class="max-w-lg">
                <h1 class="text-3xl font-bold mb-4">
                    <i class="fas fa-users-cog ml-2"></i>
                    مدیریت کاربران
                </h1>
                <p class="text-lg">
                    ایجاد و مدیریت کاربران سیستم
                </p>
            </div>
        </div>
    </div>
    
    <!-- فرم ایجاد کاربر جدید -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-6">
                <i class="fas fa-user-plus text-primary ml-2"></i>
                ایجاد کاربر جدید
            </h2>
            
            <?php echo $message; ?>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">نام کاربری *</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" required 
                                   class="input input-bordered w-full"
                                   placeholder="نام کاربری را وارد کنید"
                                   value="<?php echo $_POST['username'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">رمز عبور *</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" required 
                                   class="input input-bordered w-full"
                                   placeholder="رمز عبور را وارد کنید">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">آدرس ایمیل</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" 
                                   class="input input-bordered w-full"
                                   placeholder="آدرس ایمیل را وارد کنید"
                                   value="<?php echo $_POST['email'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">تلفن همراه</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="text" name="phone" 
                                   class="input input-bordered w-full"
                                   placeholder="شماره تلفن را وارد کنید"
                                   value="<?php echo $_POST['phone'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        ایجاد کاربر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- لیست کاربران -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title text-2xl">
                    <i class="fas fa-list text-info ml-2"></i>
                    لیست کاربران
                </h2>
                <div class="badge badge-info">
                    <?php echo en2fa(count($users)); ?> کاربر
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>نام کاربری</th>
                            <th>ایمیل</th>
                            <th>تلفن</th>
                            <th>کد کاربری</th>
                            <th>نوع کاربر</th>
                            <th>تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="hover">
                            <td>
                                <div class="badge badge-neutral">
                                    <?php echo en2fa($user['id']); ?>
                                </div>
                            </td>
                            <td class="font-medium">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-base-content/50 ml-2"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-base-content/50 ml-2"></i>
                                    <?php echo $user['email'] ?: 'ندارد'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-base-content/50 ml-2"></i>
                                    <?php echo $user['phone'] ?: 'ندارد'; ?>
                                </div>
                            </td>
                            <td>
                                <code class="bg-base-200 px-2 py-1 rounded text-sm">
                                    <?php echo en2fa($user['user_id_code']); ?>
                                </code>
                            </td>
                            <td>
                                <div class="badge <?php echo $user['is_admin'] ? 'badge-error' : 'badge-info'; ?>">
                                    <i class="fas <?php echo $user['is_admin'] ? 'fa-crown' : 'fa-user'; ?> ml-1"></i>
                                    <?php echo $user['is_admin'] ? 'مدیر' : 'کاربر'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-base-content/50 ml-2"></i>
                                    <?php echo en2fa(jalali_date('Y/m/d', strtotime($user['created_at']))); ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>