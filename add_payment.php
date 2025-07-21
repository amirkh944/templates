<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

if (!isset($_GET['request_id'])) {
    header('Location: requests.php');
    exit;
}

$requestId = $_GET['request_id'];
$request = getRequest($requestId);

if (!$request) {
    header('Location: requests.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $receiptImage = null;
    
    // آپلود تصویر رسید
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] == 0) {
        $uploadDir = 'uploads/receipts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . $_FILES['receipt_image']['name'];
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $uploadPath)) {
            $receiptImage = $fileName;
        }
    }
    
    try {
        addPayment($request['customer_id'], $requestId, $amount, $description, $receiptImage);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                      پرداخت با موفقیت ثبت شد.
                    </div>';
        
        // پاک کردن فرم
        $_POST = array();
        
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                      خطا در ثبت پرداخت: ' . $e->getMessage() . '
                    </div>';
    }
}

// دریافت پرداخت‌های قبلی
$payments = getCustomerPayments($request['customer_id']);
$balance = getCustomerBalance($request['customer_id']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت پرداخت - مدیریت درخواست پاسخگو رایانه</title>
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
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">ثبت پرداخت</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">درخواست: <?php echo $request['title']; ?> - کد رهگیری: <?php echo en2fa($request['tracking_code']); ?></p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php echo $message; ?>
                
                <!-- اطلاعات مشتری و موجودی -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">اطلاعات مشتری</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-700">نام:</span>
                            <span class="text-sm text-gray-900"><?php echo $request['customer_name']; ?></span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700">تلفن:</span>
                            <span class="text-sm text-gray-900"><?php echo $request['customer_phone']; ?></span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700">موجودی:</span>
                            <span class="text-sm font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo en2fa(formatNumber($balance)); ?> تومان
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- فرم ثبت پرداخت -->
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-900 mb-4">اطلاعات پرداخت</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">مبلغ پرداخت (تومان) *</label>
                                <input type="number" name="amount" step="0.01" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2"
                                       value="<?php echo $_POST['amount'] ?? ''; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">تصویر رسید</label>
                                <input type="file" name="receipt_image" accept="image/*" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                                <textarea name="description" rows="3" 
                                          class="w-full border border-gray-300 rounded-md px-3 py-2"
                                          placeholder="توضیحات پرداخت..."><?php echo $_POST['description'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 space-x-reverse">
                        <a href="requests.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">انصراف</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">ثبت پرداخت</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- تاریخچه پرداخت‌ها -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">تاریخچه پرداخت‌ها</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">لیست پرداخت‌های این مشتری</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">توضیحات</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رسید</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo en2fa(jalali_date('Y/m/d', strtotime($payment['created_at']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php echo $payment['payment_type'] == 'واریز' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $payment['payment_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo en2fa(formatNumber($payment['amount'])); ?> تومان
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $payment['description']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($payment['receipt_image']): ?>
                                    <a href="uploads/receipts/<?php echo $payment['receipt_image']; ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-image"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">ندارد</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>