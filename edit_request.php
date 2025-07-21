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
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                      درخواست با موفقیت به‌روزرسانی شد.
                    </div>';
        
        // بارگذاری مجدد اطلاعات درخواست
        $request = getRequest($requestId);
        
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                      خطا در به‌روزرسانی درخواست: ' . $e->getMessage() . '
                    </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش درخواست - مدیریت درخواست پاسخگو رایانه</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazir', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-xl font-bold text-gray-800">مدیریت درخواست پاسخگو رایانه</a>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="dashboard.php" class="text-gray-700 hover:text-gray-900">داشبورد</a>
                    <a href="requests.php" class="text-gray-700 hover:text-gray-900">درخواست‌ها</a>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">خروج</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">ویرایش درخواست</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">کد رهگیری: <?php echo en2fa($request['tracking_code']); ?></p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php echo $message; ?>
                
                <form method="POST" class="space-y-6">
                    <!-- اطلاعات مشتری -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-900 mb-4">اطلاعات مشتری</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">نام و نام خانوادگی</label>
                                <input type="text" readonly 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
                                       value="<?php echo $request['customer_name']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">شماره تماس</label>
                                <input type="text" readonly 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
                                       value="<?php echo $request['customer_phone']; ?>">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">آدرس ایمیل</label>
                                <input type="email" readonly 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
                                       value="<?php echo $request['customer_email'] ?: 'ندارد'; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات درخواست -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-900 mb-4">اطلاعات درخواست</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">عنوان درخواست *</label>
                                <input type="text" name="title" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['title']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">مدل دستگاه</label>
                                <input type="text" name="device_model" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['device_model']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IMEI اول</label>
                                <input type="text" name="imei1" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['imei1']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IMEI دوم</label>
                                <input type="text" name="imei2" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['imei2']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">مدت زمان احتمالی</label>
                                <input type="text" name="estimated_duration" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['estimated_duration']; ?>">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">شرح مشکل</label>
                                <textarea name="problem_description" rows="3" 
                                          class="w-full border border-gray-300 rounded-md px-3 py-2"><?php echo $request['problem_description']; ?></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">اقدامات قابل انجام</label>
                                <textarea name="actions_required" rows="3" 
                                          class="w-full border border-gray-300 rounded-md px-3 py-2"><?php echo $request['actions_required']; ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">هزینه درخواست (تومان)</label>
                                <input type="number" name="cost" step="0.01" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $request['cost']; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">وضعیت درخواست</label>
                                <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="در حال پردازش" <?php echo $request['status'] == 'در حال پردازش' ? 'selected' : ''; ?>>در حال پردازش</option>
                                    <option value="تکمیل شده" <?php echo $request['status'] == 'تکمیل شده' ? 'selected' : ''; ?>>تکمیل شده</option>
                                    <option value="لغو شده" <?php echo $request['status'] == 'لغو شده' ? 'selected' : ''; ?>>لغو شده</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات ثبت -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-900 mb-4">اطلاعات ثبت</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ ثبت</label>
                                <input type="text" readonly 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
                                       value="<?php echo en2fa($request['registration_date']); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">کد رهگیری</label>
                                <input type="text" readonly 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
                                       value="<?php echo en2fa($request['tracking_code']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 space-x-reverse">
                        <a href="requests.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">انصراف</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">به‌روزرسانی درخواست</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>