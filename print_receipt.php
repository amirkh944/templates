<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$requestId = $_GET['id'];
$request = getRequest($requestId);

if (!$request) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسید درخواست - <?php echo $request['tracking_code']; ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Vazir', sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: white;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            margin-left: 10px;
            color: #555;
            min-width: 120px;
        }
        .info-value {
            color: #333;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .tracking-code {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-processing {
            background: #fef3c7;
            color: #92400e;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        @media print {
            body { background: white; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">پاسخگو رایانه</div>
            <div class="receipt-title">رسید ثبت درخواست</div>
        </div>

        <div class="info-section">
            <div class="info-title">اطلاعات مشتری</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">نام:</span>
                    <span class="info-value"><?php echo $request['customer_name']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">تلفن:</span>
                    <span class="info-value"><?php echo en2fa($request['customer_phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ایمیل:</span>
                    <span class="info-value"><?php echo $request['customer_email'] ?: 'ندارد'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ ثبت:</span>
                    <span class="info-value"><?php echo en2fa($request['registration_date']); ?></span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-title">اطلاعات درخواست</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">عنوان:</span>
                    <span class="info-value"><?php echo $request['title']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">مدل دستگاه:</span>
                    <span class="info-value"><?php echo $request['device_model'] ?: 'ندارد'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">IMEI اول:</span>
                    <span class="info-value"><?php echo $request['imei1'] ? en2fa($request['imei1']) : 'ندارد'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">IMEI دوم:</span>
                    <span class="info-value"><?php echo $request['imei2'] ? en2fa($request['imei2']) : 'ندارد'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">مدت زمان احتمالی:</span>
                    <span class="info-value"><?php echo $request['estimated_duration'] ?: 'تعیین نشده'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">هزینه:</span>
                    <span class="info-value"><?php echo en2fa(formatNumber($request['cost'])); ?> تومان</span>
                </div>
            </div>
            
            <?php if ($request['problem_description']): ?>
            <div class="info-item" style="margin-top: 15px;">
                <span class="info-label">شرح مشکل:</span>
                <span class="info-value"><?php echo nl2br($request['problem_description']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($request['actions_required']): ?>
            <div class="info-item" style="margin-top: 15px;">
                <span class="info-label">اقدامات قابل انجام:</span>
                <span class="info-value"><?php echo nl2br($request['actions_required']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <div class="info-title">وضعیت درخواست</div>
            <div style="text-align: center;">
                <span class="status-badge <?php 
                    echo $request['status'] == 'تکمیل شده' ? 'status-completed' : 
                        ($request['status'] == 'لغو شده' ? 'status-cancelled' : 'status-processing'); 
                ?>">
                    <?php echo $request['status']; ?>
                </span>
            </div>
        </div>

        <div class="qr-section">
            <div class="tracking-code">کد رهگیری: <?php echo en2fa($request['tracking_code']); ?></div>
            <div id="qrcode"></div>
        </div>

        <div class="footer">
            <p>این رسید به منزله تایید ثبت درخواست شما می‌باشد</p>
            <p>برای پیگیری درخواست خود از کد رهگیری استفاده کنید</p>
            <p>تاریخ چاپ: <?php echo en2fa(jalali_date('Y/m/d')); ?></p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">چاپ رسید</button>
        <button onclick="window.history.back()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">بازگشت</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // تولید QR کد
        QRCode.toCanvas(document.getElementById('qrcode'), '<?php echo $request['tracking_code']; ?>', {
            width: 150,
            height: 150,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
    </script>
</body>
</html>