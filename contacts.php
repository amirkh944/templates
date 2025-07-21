<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$message = '';
$customers = getAllCustomers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerName = $_POST['customer_name'];
    $customerPhone = $_POST['customer_phone'];
    $customerEmail = $_POST['customer_email'] ?? '';
    
    try {
        // بررسی وجود مشتری با همین شماره تلفن
        $existingCustomer = getCustomerByPhone($customerPhone);
        if ($existingCustomer) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                          مشتری با این شماره تلفن قبلاً ثبت شده است.
                        </div>';
        } else {
            $customerId = createCustomer($customerName, $customerPhone, $customerEmail);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                          مشتری جدید با موفقیت ثبت شد.
                        </div>';
            
            // بارگذاری مجدد لیست مشتریان
            $customers = getAllCustomers();
            
            // پاک کردن فرم
            $_POST = array();
        }
        
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                      خطا در ثبت مشتری: ' . $e->getMessage() . '
                    </div>';
    }
}

// کنترل تم (قالب)
$theme = $_GET['theme'] ?? 'light';
$isDark = $theme === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت مشتری جدید - مدیریت درخواست پاسخگو رایانه</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazir', sans-serif; }
        <?php if ($isDark): ?>
        .dark-theme {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
            color: #e5e7eb;
        }
        .dark-card {
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid #374151;
        }
        .dark-input {
            background: #374151;
            border: 1px solid #4b5563;
            color: #e5e7eb;
        }
        .dark-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo $isDark ? 'dark-theme min-h-screen' : 'bg-gray-100'; ?>">
    <!-- Navigation -->
    <nav class="<?php echo $isDark ? 'bg-gray-800 shadow-lg' : 'bg-white shadow-lg'; ?>">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php?theme=<?php echo $theme; ?>" class="text-xl font-bold <?php echo $isDark ? 'text-white' : 'text-gray-800'; ?>">
                        مدیریت درخواست پاسخگو رایانه
                    </a>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="dashboard.php?theme=<?php echo $theme; ?>" class="<?php echo $isDark ? 'text-gray-300 hover:text-white' : 'text-gray-700 hover:text-gray-900'; ?>">
                        داشبورد
                    </a>
                    
                    <!-- Theme Toggle -->
                    <div class="flex space-x-2">
                        <a href="?theme=light" class="px-3 py-1 rounded <?php echo !$isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>">
                            <i class="fas fa-sun"></i>
                        </a>
                        <a href="?theme=dark" class="px-3 py-1 rounded <?php echo $isDark ? 'bg-blue-500 text-white' : 'bg-gray-600 text-gray-300'; ?>">
                            <i class="fas fa-moon"></i>
                        </a>
                    </div>
                    
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">خروج</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- فرم ثبت مشتری جدید -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    ثبت مشتری جدید
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    فرم ثبت اطلاعات مشتری جدید در سیستم
                </p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php echo $message; ?>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                                نام و نام خانوادگی *
                            </label>
                            <input type="text" name="customer_name" required 
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                                   value="<?php echo $_POST['customer_name'] ?? ''; ?>"
                                   placeholder="نام کامل مشتری">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                                شماره تلفن *
                            </label>
                            <input type="text" name="customer_phone" required 
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                                   value="<?php echo $_POST['customer_phone'] ?? ''; ?>"
                                   placeholder="09XXXXXXXXX"
                                   pattern="09[0-9]{9}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">
                                آدرس ایمیل (اختیاری)
                            </label>
                            <input type="email" name="customer_email" 
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                                   value="<?php echo $_POST['customer_email'] ?? ''; ?>"
                                   placeholder="customer@example.com">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-user-plus ml-2"></i>
                            ثبت مشتری
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- لیست مشتریان -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    لیست مشتریان
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    تمام مشتریان ثبت شده در سیستم
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                شناسه
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                نام مشتری
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                شماره تلفن
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                ایمیل
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                تاریخ ثبت
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                موجودی
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">
                                عملیات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="<?php echo $isDark ? 'bg-gray-800 divide-gray-600' : 'bg-white divide-gray-200'; ?> divide-y">
                        <?php foreach ($customers as $customer): ?>
                        <?php $balance = getCustomerBalance($customer['id']); ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo en2fa($customer['id']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo $customer['name']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <span class="font-mono"><?php echo $customer['phone']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo $customer['email'] ?: 'ثبت نشده'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d', strtotime($customer['created_at']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <span class="<?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo en2fa(formatNumber($balance)); ?> تومان
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2 space-x-reverse">
                                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition duration-200" title="مشاهده جزئیات">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="print_customer.php?id=<?php echo $customer['id']; ?>" 
                                       class="text-purple-600 hover:text-purple-900 transition duration-200" title="چاپ گزارش">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="new_request.php?customer_id=<?php echo $customer['id']; ?>&theme=<?php echo $theme; ?>" 
                                       class="text-green-600 hover:text-green-900 transition duration-200" title="درخواست جدید">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($customers)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl <?php echo $isDark ? 'text-gray-400' : 'text-gray-300'; ?> mb-4"></i>
                    <p class="<?php echo $isDark ? 'text-gray-400' : 'text-gray-500'; ?>">هنوز هیچ مشتری ثبت نشده است</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>