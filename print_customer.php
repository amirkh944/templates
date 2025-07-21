<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

if (!isset($_GET['id'])) {
    header('Location: customers.php');
    exit;
}

$customerId = $_GET['id'];
$customer = getCustomer($customerId);

if (!$customer) {
    header('Location: customers.php');
    exit;
}

$requests = getCustomerRequests($customerId);
$payments = getCustomerPayments($customerId);
$balance = getCustomerBalance($customerId);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اطلاعات مشتری - <?php echo $customer['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Vazir', sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: white;
            font-size: 14px;
        }
        .customer-report {
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
        .report-title {
            font-size: 18px;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
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
        .balance-positive {
            color: #059669;
            font-weight: bold;
        }
        .balance-negative {
            color: #dc2626;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-processing {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .payment-deposit {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .payment-debt {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body { background: white; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="customer-report">
        <div class="header">
            <div class="company-name">پاسخگو رایانه</div>
            <div class="report-title">گزارش کامل مشتری</div>
        </div>

        <!-- اطلاعات مشتری -->
        <div class="section">
            <div class="section-title">اطلاعات مشتری</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">شناسه:</span>
                    <span class="info-value"><?php echo en2fa($customer['id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">نام:</span>
                    <span class="info-value"><?php echo $customer['name']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">تلفن:</span>
                    <span class="info-value"><?php echo en2fa($customer['phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ایمیل:</span>
                    <span class="info-value"><?php echo $customer['email'] ?: 'ندارد'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ ثبت:</span>
                    <span class="info-value"><?php echo en2fa(jalali_date('Y/m/d', strtotime($customer['created_at']))); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">موجودی:</span>
                    <span class="info-value <?php echo $balance >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                        <?php echo en2fa(formatNumber($balance)); ?> تومان
                    </span>
                </div>
            </div>
        </div>

        <!-- درخواست‌های مشتری -->
        <div class="section">
            <div class="section-title">درخواست‌های مشتری (<?php echo en2fa(count($requests)); ?> درخواست)</div>
            <?php if (!empty($requests)): ?>
            <table>
                <thead>
                    <tr>
                        <th>کد رهگیری</th>
                        <th>عنوان</th>
                        <th>تاریخ ثبت</th>
                        <th>هزینه</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo en2fa($request['tracking_code']); ?></td>
                        <td><?php echo $request['title']; ?></td>
                        <td><?php echo en2fa($request['registration_date']); ?></td>
                        <td><?php echo en2fa(formatNumber($request['cost'])); ?> تومان</td>
                        <td>
                            <span class="<?php echo $request['status'] == 'تکمیل شده' ? 'status-completed' : 
                                ($request['status'] == 'لغو شده' ? 'status-cancelled' : 'status-processing'); ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>هیچ درخواستی ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        <!-- تاریخچه پرداخت‌ها -->
        <div class="section">
            <div class="section-title">تاریخچه پرداخت‌ها (<?php echo en2fa(count($payments)); ?> پرداخت)</div>
            <?php if (!empty($payments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>نوع</th>
                        <th>مبلغ</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo en2fa(jalali_date('Y/m/d', strtotime($payment['created_at']))); ?></td>
                        <td>
                            <span class="<?php echo $payment['payment_type'] == 'واریز' ? 'payment-deposit' : 'payment-debt'; ?>">
                                <?php echo $payment['payment_type']; ?>
                            </span>
                        </td>
                        <td><?php echo en2fa(formatNumber($payment['amount'])); ?> تومان</td>
                        <td><?php echo $payment['description']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>هیچ پرداختی ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        <!-- خلاصه مالی -->
        <div class="section">
            <div class="section-title">خلاصه مالی</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">کل واریزی:</span>
                    <span class="info-value balance-positive">
                        <?php 
                        $totalPaid = array_sum(array_column(array_filter($payments, function($p) { return $p['payment_type'] == 'واریز'; }), 'amount'));
                        echo en2fa(formatNumber($totalPaid)); 
                        ?> تومان
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">کل بدهکاری:</span>
                    <span class="info-value balance-negative">
                        <?php 
                        $totalDebt = array_sum(array_column(array_filter($payments, function($p) { return $p['payment_type'] == 'بدهکاری'; }), 'amount'));
                        echo en2fa(formatNumber($totalDebt)); 
                        ?> تومان
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">موجودی نهایی:</span>
                    <span class="info-value <?php echo $balance >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                        <?php echo en2fa(formatNumber($balance)); ?> تومان
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">تعداد درخواست‌ها:</span>
                    <span class="info-value"><?php echo en2fa(count($requests)); ?></span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>این گزارش شامل تمام اطلاعات مشتری تا تاریخ <?php echo en2fa(jalali_date('Y/m/d')); ?> می‌باشد</p>
            <p>تاریخ تهیه گزارش: <?php echo en2fa(jalali_date('Y/m/d H:i')); ?></p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">چاپ گزارش</button>
        <button onclick="window.history.back()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">بازگشت</button>
    </div>
</body>
</html>