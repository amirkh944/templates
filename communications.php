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
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                      تماس با موفقیت ثبت شد.
                    </div>';
        
        // بارگذاری مجدد لیست تماس‌ها
        $contacts = getAllContacts();
        
        // پاک کردن فرم
        $_POST = array();
        
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                      خطا در ثبت تماس: ' . $e->getMessage() . '
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
    <title>مدیریت ارتباطات - مدیریت درخواست پاسخگو رایانه</title>
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
        <!-- فرم ثبت تماس جدید -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    ثبت تماس و ارتباط جدید
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    فرم ثبت تماس‌ها و پیام‌ها با مشتریان
                </p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php echo $message; ?>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">مشتری *</label>
                            <select name="customer_id" required class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2">
                                <option value="">انتخاب مشتری</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" <?php echo ($_POST['customer_id'] ?? '') == $customer['id'] ? 'selected' : ''; ?>>
                                    <?php echo $customer['name']; ?> - <?php echo $customer['phone']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">نوع تماس *</label>
                            <select name="contact_type" required class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2">
                                <option value="">انتخاب نوع تماس</option>
                                <option value="تماس" <?php echo ($_POST['contact_type'] ?? '') == 'تماس' ? 'selected' : ''; ?>>تماس تلفنی</option>
                                <option value="ایمیل" <?php echo ($_POST['contact_type'] ?? '') == 'ایمیل' ? 'selected' : ''; ?>>ایمیل</option>
                                <option value="پیامک" <?php echo ($_POST['contact_type'] ?? '') == 'پیامک' ? 'selected' : ''; ?>>پیامک</option>
                                <option value="واتساپ" <?php echo ($_POST['contact_type'] ?? '') == 'واتساپ' ? 'selected' : ''; ?>>واتساپ</option>
                                <option value="حضوری" <?php echo ($_POST['contact_type'] ?? '') == 'حضوری' ? 'selected' : ''; ?>>مراجعه حضوری</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">موضوع</label>
                            <input type="text" name="subject" 
                                   class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                                   value="<?php echo $_POST['subject'] ?? ''; ?>"
                                   placeholder="موضوع تماس یا ارتباط">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium <?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> mb-2">پیام و توضیحات *</label>
                            <textarea name="message" rows="4" required 
                                      class="w-full <?php echo $isDark ? 'dark-input' : 'border border-gray-300'; ?> rounded-md px-3 py-2"
                                      placeholder="متن پیام، نتیجه تماس یا توضیحات کامل ارتباط"><?php echo $_POST['message'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-phone-alt ml-2"></i>
                            ثبت تماس
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- لیست تماس‌ها -->
        <div class="<?php echo $isDark ? 'dark-card' : 'bg-white'; ?> shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?>">
                    تاریخچه ارتباطات
                </h3>
                <p class="mt-1 max-w-2xl text-sm <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?>">
                    لیست تمام تماس‌ها و ارتباطات ثبت شده
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y <?php echo $isDark ? 'divide-gray-600' : 'divide-gray-200'; ?>">
                    <thead class="<?php echo $isDark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">تاریخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">مشتری</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">نوع تماس</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">موضوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium <?php echo $isDark ? 'text-gray-300' : 'text-gray-500'; ?> uppercase tracking-wider">پیام</th>
                        </tr>
                    </thead>
                    <tbody class="<?php echo $isDark ? 'bg-gray-800 divide-gray-600' : 'bg-white divide-gray-200'; ?> divide-y">
                        <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo en2fa(jalali_date('Y/m/d H:i', strtotime($contact['contact_date']))); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <div class="font-medium"><?php echo $contact['customer_name']; ?></div>
                                <div class="text-gray-500 font-mono text-sm"><?php echo $contact['customer_phone']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php 
                                    switch($contact['contact_type']) {
                                        case 'تماس': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'ایمیل': echo 'bg-green-100 text-green-800'; break;
                                        case 'پیامک': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'واتساپ': echo 'bg-green-100 text-green-800'; break;
                                        case 'حضوری': echo 'bg-orange-100 text-orange-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
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
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <?php echo $contact['subject'] ?: 'بدون موضوع'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm <?php echo $isDark ? 'text-gray-200' : 'text-gray-900'; ?>">
                                <div class="max-w-xs">
                                    <?php echo nl2br(substr($contact['message'], 0, 100)); ?>
                                    <?php if (strlen($contact['message']) > 100): ?>
                                        <span class="text-gray-500">...</span>
                                        <button onclick="showFullMessage('<?php echo addslashes($contact['message']); ?>')" 
                                                class="text-blue-500 hover:text-blue-700 text-xs">
                                            ادامه
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($contacts)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-comments text-4xl <?php echo $isDark ? 'text-gray-400' : 'text-gray-300'; ?> mb-4"></i>
                    <p class="<?php echo $isDark ? 'text-gray-400' : 'text-gray-500'; ?>">هنوز هیچ تماس یا ارتباطی ثبت نشده است</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for full message -->
    <div id="messageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md <?php echo $isDark ? 'bg-gray-800 border-gray-600' : 'bg-white'; ?>">
            <div class="mt-3">
                <h3 class="text-lg font-medium <?php echo $isDark ? 'text-white' : 'text-gray-900'; ?> mb-4">متن کامل پیام</h3>
                <div id="fullMessage" class="<?php echo $isDark ? 'text-gray-200' : 'text-gray-700'; ?> whitespace-pre-wrap"></div>
                <div class="flex justify-end mt-4">
                    <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showFullMessage(message) {
            document.getElementById('fullMessage').textContent = message;
            document.getElementById('messageModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>