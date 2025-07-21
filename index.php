<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if (login($username, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
} else {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'ورود به سیستم - پاسخگو رایانه';
include 'includes/header.php';
?>

<div class="hero min-h-screen bg-gradient-to-br from-primary/10 to-secondary/10">
    <div class="hero-content flex-col lg:flex-row-reverse">
        
        <!-- تصویر و معرفی -->
        <div class="text-center lg:text-right lg:w-1/2">
            <div class="max-w-md mx-auto lg:mx-0">
                <div class="mb-8">
                    <div class="w-24 h-24 mx-auto lg:mx-0 bg-primary rounded-2xl flex items-center justify-center mb-4">
                        <i class="fas fa-desktop text-4xl text-primary-content"></i>
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-bold text-base-content mb-4">
                        سامانه مدیریت درخواست
                    </h1>
                    <h2 class="text-2xl lg:text-3xl font-bold text-primary mb-6">
                        پاسخگو رایانه
                    </h2>
                    <p class="text-lg text-base-content/70 leading-relaxed">
                        سیستم جامع مدیریت درخواست‌های تعمیرات و خدمات کامپیوتر
                    </p>
                </div>
                
                <!-- ویژگی‌ها -->
                <div class="grid grid-cols-1 gap-4 text-right">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-success rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-success-content text-sm"></i>
                        </div>
                        <span class="text-base-content/80">مدیریت درخواست‌ها</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-info rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-info-content text-sm"></i>
                        </div>
                        <span class="text-base-content/80">مدیریت مشتریان</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-warning rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-warning-content text-sm"></i>
                        </div>
                        <span class="text-base-content/80">گزارش‌گیری پیشرفته</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-error rounded-lg flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-error-content text-sm"></i>
                        </div>
                        <span class="text-base-content/80">پیگیری آنلاین</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- فرم لاگین -->
        <div class="card shrink-0 w-full max-w-md shadow-2xl bg-base-100 border border-base-300">
            <form class="card-body" method="POST">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-base-content">ورود به سیستم</h3>
                    <p class="text-base-content/70 mt-2">لطفاً اطلاعات خود را وارد کنید</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error mb-4">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">
                            <i class="fas fa-user ml-2"></i>نام کاربری
                        </span>
                    </label>
                    <input 
                        type="text" 
                        name="username" 
                        placeholder="نام کاربری خود را وارد کنید" 
                        class="input input-bordered w-full" 
                        required 
                        autofocus
                    />
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">
                            <i class="fas fa-lock ml-2"></i>رمز عبور
                        </span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="رمز عبور خود را وارد کنید" 
                        class="input input-bordered w-full" 
                        required 
                    />
                    <label class="label">
                        <a href="#" class="label-text-alt link link-hover text-primary">
                            رمز عبور را فراموش کرده‌اید؟
                        </a>
                    </label>
                </div>
                
                <div class="form-control mt-6">
                    <button class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-sign-in-alt ml-2"></i>
                        ورود
                    </button>
                </div>
                
                <div class="divider text-base-content/50">یا</div>
                
                <div class="text-center">
                    <a href="public/customer/track.php" class="btn btn-outline btn-secondary w-full">
                        <i class="fas fa-search ml-2"></i>
                        پیگیری درخواست (بدون لاگین)
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- بخش معرفی خدمات -->
<div class="bg-base-200 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-bold text-base-content mb-4">خدمات ما</h3>
            <p class="text-lg text-base-content/70">ارائه دهنده بهترین خدمات تعمیرات کامپیوتر</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- تعمیرات سخت افزار -->
            <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
                <div class="card-body text-center">
                    <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-microchip text-2xl text-primary-content"></i>
                    </div>
                    <h4 class="card-title justify-center text-lg mb-3">تعمیرات سخت افزار</h4>
                    <p class="text-base-content/70">تعمیر انواع قطعات کامپیوتر، لپ تاپ و سرور</p>
                </div>
            </div>
            
            <!-- نصب نرم افزار -->
            <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
                <div class="card-body text-center">
                    <div class="w-16 h-16 bg-info rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-download text-2xl text-info-content"></i>
                    </div>
                    <h4 class="card-title justify-center text-lg mb-3">نصب نرم افزار</h4>
                    <p class="text-base-content/70">نصب و پیکربندی انواع نرم افزارها و سیستم عامل</p>
                </div>
            </div>
            
            <!-- پشتیبانی -->
            <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
                <div class="card-body text-center">
                    <div class="w-16 h-16 bg-success rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-2xl text-success-content"></i>
                    </div>
                    <h4 class="card-title justify-center text-lg mb-3">پشتیبانی ۲۴/۷</h4>
                    <p class="text-base-content/70">پشتیبانی همه روزه و خدمات مشاوره فنی</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>