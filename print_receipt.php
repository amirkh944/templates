<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// بررسی وجود ID درخواست
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$requestId = $_GET['id'];

// دریافت اطلاعات درخواست
$request = getRequest($requestId);
if (!$request) {
    header('Location: dashboard.php');
    exit;
}

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

$pageTitle = 'رسید درخواست ' . $request['tracking_code'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- DaisyUI & Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Vazir Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Vazir', sans-serif;
            background: #f8fafc;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .receipt-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            clip-path: polygon(0 0, 100% 0, 95% 100%, 5% 100%);
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
        }
        
        .receipt-body {
            padding: 40px;
        }
        
        .info-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-right: 4px solid #667eea;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .status-success { background: #d1fae5; color: #065f46; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-error { background: #fee2e2; color: #991b1b; }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .payment-table th,
        .payment-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .payment-table th {
            background: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .receipt-footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 2px dashed #d1d5db;
        }
        
        .qr-code {
            width: 100px;
            height: 100px;
            background: #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .no-print {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        /* استایل چاپ */
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .receipt-container {
                box-shadow: none !important;
                margin: 0 !important;
                border-radius: 0 !important;
                max-width: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .receipt-header::after {
                display: none;
            }
            
            .receipt-body {
                padding: 20px !important;
            }
            
            .info-section {
                break-inside: avoid;
            }
            
            .payment-table {
                break-inside: avoid;
            }
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    
    <!-- دکمه بازگشت و چاپ -->
    <div class="no-print">
        <div class="flex gap-2">
            <button onclick="window.history.back()" class="btn btn-ghost btn-sm">
                <i class="fas fa-arrow-right ml-1"></i>
                بازگشت
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print ml-1"></i>
                چاپ
            </button>
        </div>
    </div>
    
    <div class="receipt-container">
        
        <!-- هدر رسید -->
        <div class="receipt-header">
            <div class="company-logo">
                <i class="fas fa-desktop text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold mb-2">پاسخگو رایانه</h1>
            <p class="text-lg opacity-90">خدمات تخصصی کامپیوتر و موبایل</p>
            <div class="flex justify-center items-center gap-4 mt-4 text-sm opacity-75">
                <span><i class="fas fa-phone ml-1"></i> ۰۲۱-۲۲۳۳۴۴۵۵</span>
                <span><i class="fas fa-envelope ml-1"></i> info@pasokhraya.com</span>
                <span><i class="fas fa-globe ml-1"></i> www.pasokhraya.com</span>
            </div>
        </div>
        
        <!-- بدنه رسید -->
        <div class="receipt-body">
            
            <!-- اطلاعات کلی -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">رسید خدمات</h2>
                    <p class="text-gray-600">تاریخ صدور: <?php echo en2fa(jalali_date('Y/m/d H:i')); ?></p>
                </div>
                <div class="text-left">
                    <div class="text-3xl font-bold text-blue-600 mb-2">
                        <?php echo en2fa($request['tracking_code']); ?>
                    </div>
                    <p class="text-sm text-gray-500">کد رهگیری</p>
                </div>
            </div>
            
            <!-- اطلاعات مشتری -->
            <div class="info-section">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 ml-2"></i>
                    اطلاعات مشتری
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-600 text-sm">نام و نام خانوادگی:</span>
                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">شماره تماس:</span>
                        <div class="font-bold text-gray-800"><?php echo $customer['phone']; ?></div>
                    </div>
                    <?php if ($customer['email']): ?>
                    <div class="md:col-span-2">
                        <span class="text-gray-600 text-sm">ایمیل:</span>
                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($customer['email']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- اطلاعات درخواست -->
            <div class="info-section">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clipboard-list text-green-500 ml-2"></i>
                    جزئیات درخواست
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <span class="text-gray-600 text-sm">عنوان درخواست:</span>
                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($request['title']); ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">وضعیت:</span>
                        <div>
                            <span class="status-badge <?php 
                                switch($request['status']) {
                                    case 'تکمیل شده': echo 'status-success'; break;
                                    case 'لغو شده': echo 'status-error'; break;
                                    default: echo 'status-warning';
                                }
                            ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">تاریخ ثبت:</span>
                        <div class="font-bold text-gray-800"><?php echo en2fa(jalali_date('Y/m/d H:i', strtotime($request['created_at']))); ?></div>
                    </div>
                    <?php if ($request['estimated_duration']): ?>
                    <div>
                        <span class="text-gray-600 text-sm">مدت زمان تخمینی:</span>
                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($request['estimated_duration']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($request['device_model']): ?>
                <div class="mb-4">
                    <span class="text-gray-600 text-sm">مدل دستگاه:</span>
                    <div class="font-bold text-gray-800"><?php echo htmlspecialchars($request['device_model']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($request['imei1'] || $request['imei2']): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <?php if ($request['imei1']): ?>
                    <div>
                        <span class="text-gray-600 text-sm">IMEI 1:</span>
                        <div class="font-bold text-gray-800 font-mono"><?php echo en2fa($request['imei1']); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['imei2']): ?>
                    <div>
                        <span class="text-gray-600 text-sm">IMEI 2:</span>
                        <div class="font-bold text-gray-800 font-mono"><?php echo en2fa($request['imei2']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($request['problem_description']): ?>
                <div class="mb-4">
                    <span class="text-gray-600 text-sm">شرح مشکل:</span>
                    <div class="bg-gray-50 p-3 rounded-lg mt-2 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($request['problem_description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($request['actions_required']): ?>
                <div>
                    <span class="text-gray-600 text-sm">اقدامات انجام شده:</span>
                    <div class="bg-blue-50 p-3 rounded-lg mt-2 leading-relaxed border-right-4 border-blue-400">
                        <?php echo nl2br(htmlspecialchars($request['actions_required'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- اطلاعات مالی -->
            <div class="info-section">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calculator text-purple-500 ml-2"></i>
                    اطلاعات مالی
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo en2fa(number_format($request['cost'])); ?></div>
                        <div class="text-sm text-gray-600">هزینه کل (تومان)</div>
                    </div>
                    
                    <?php 
                    $totalPaid = 0;
                    $totalDebt = 0;
                    foreach ($payments as $payment) {
                        if ($payment['payment_type'] == 'واریز') {
                            $totalPaid += $payment['amount'];
                        } else {
                            $totalDebt += $payment['amount'];
                        }
                    }
                    $remaining = $request['cost'] - $totalPaid + $totalDebt;
                    ?>
                    
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?php echo en2fa(number_format($totalPaid)); ?></div>
                        <div class="text-sm text-gray-600">پرداخت شده (تومان)</div>
                    </div>
                    
                    <div class="text-center p-4 <?php echo $remaining > 0 ? 'bg-red-50' : 'bg-gray-50'; ?> rounded-lg">
                        <div class="text-2xl font-bold <?php echo $remaining > 0 ? 'text-red-600' : 'text-gray-600'; ?>">
                            <?php echo en2fa(number_format(max(0, $remaining))); ?>
                        </div>
                        <div class="text-sm text-gray-600">باقیمانده (تومان)</div>
                    </div>
                </div>
                
                <?php if (!empty($payments)): ?>
                <div>
                    <h4 class="font-bold text-gray-700 mb-3">تاریخچه تراکنش‌ها:</h4>
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>نوع</th>
                                <th>مبلغ (تومان)</th>
                                <th>توضیحات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo en2fa(jalali_date('Y/m/d', strtotime($payment['created_at']))); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $payment['payment_type'] == 'واریز' ? 'status-success' : 'status-error'; ?>">
                                        <?php echo $payment['payment_type']; ?>
                                    </span>
                                </td>
                                <td class="font-bold"><?php echo en2fa(number_format($payment['amount'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['description'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- فوتر رسید -->
        <div class="receipt-footer">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h4 class="font-bold text-gray-800 mb-3">پیگیری آنلاین</h4>
                    <div class="qr-code">
                        <i class="fas fa-qrcode text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-600">
                        کد رهگیری: <span class="font-bold"><?php echo en2fa($request['tracking_code']); ?></span>
                    </p>
                    <p class="text-sm text-gray-600">
                        www.pasokhraya.com
                    </p>
                </div>
                
                <div class="text-center md:text-right">
                    <h4 class="font-bold text-gray-800 mb-3">اطلاعات تماس</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div><i class="fas fa-map-marker-alt ml-2"></i>تهران، خیابان انقلاب، پلاک ۱۲۳</div>
                        <div><i class="fas fa-phone ml-2"></i>۰۲۱-۲۲۳۳۴۴۵۵</div>
                        <div><i class="fas fa-mobile ml-2"></i>۰۹۱۲۳۴۵۶۷۸۹</div>
                        <div><i class="fas fa-envelope ml-2"></i>info@pasokhraya.com</div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-xs text-yellow-800 font-medium">
                            <i class="fas fa-exclamation-triangle ml-1"></i>
                            این رسید تا ۳۰ روز پس از تحویل کار معتبر است
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-300">
                <p class="text-center text-gray-500 text-sm">
                    با تشکر از اعتماد شما - پاسخگو رایانه © <?php echo date('Y'); ?>
                </p>
            </div>
        </div>
        
    </div>
    
    <script>
        // چاپ خودکار در صورت وجود پارامتر auto_print
        if (new URLSearchParams(window.location.search).get('auto_print') === '1') {
            setTimeout(() => {
                window.print();
            }, 1000);
        }
    </script>
    
</body>
</html>