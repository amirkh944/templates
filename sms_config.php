<?php
/**
 * تنظیمات SMS.ir برای ارسال پیامک
 * 
 * برای فعال‌سازی سرویس پیامک، مراحل زیر را دنبال کنید:
 * 1. در سایت sms.ir ثبت‌نام کنید
 * 2. API Key خود را از پنل کاربری دریافت کنید
 * 3. شماره خط اختصاصی خود را از پنل کاربری کپی کنید
 * 4. مقادیر زیر را با اطلاعات واقعی خود جایگزین کنید
 */

// تنظیمات SMS.ir
define('SMS_API_KEY', 'mcaLckCVDt1rftPDh2VGjCpTFEHNO6nH4u7Nuuy9iKglQJ5I'); // کلید API خود را اینجا قرار دهید
define('SMS_LINE_NUMBER', '30007487133827'); // شماره خط خود را اینجا قرار دهید

// تنظیمات عمومی پیامک
define('SMS_ENABLED', true); // برای فعال‌سازی، مقدار را true کنید
define('SMS_SIGNATURE', '');

// الگوهای پیامک (اختیاری - برای استفاده از الگوهای از پیش تعریف شده)
define('SMS_PATTERN_NEW_REQUEST', '712582'); // کد الگوی ثبت درخواست جدید
define('SMS_PATTERN_STATUS_UPDATE', ''); // کد الگوی به‌روزرسانی وضعیت
define('SMS_PATTERN_PAYMENT_RECEIVED', ''); // کد الگوی دریافت پرداخت

/**
 * نمونه الگوهای پیامک:
 * 
 * 1. ثبت درخواست جدید:
 * "درخواست شما با کد رهگیری {{tracking_code}} ثبت شد. {{title}} - {{signature}}"
 * 
 * 2. به‌روزرسانی وضعیت:
 * "وضعیت درخواست {{tracking_code}} به {{status}} تغییر یافت. {{signature}}"
 * 
 * 3. دریافت پرداخت:
 * "پرداخت {{amount}} تومان برای درخواست {{tracking_code}} دریافت شد. {{signature}}"
 */

// تابع کمکی برای بررسی فعال بودن سرویس پیامک
function isSMSEnabled() {
    return SMS_ENABLED && 
           SMS_API_KEY !== 'mcaLckCVDt1rftPDh2VGjCpTFEHNO6nH4u7Nuuy9iKglQJ5I' && 
           SMS_LINE_NUMBER !== '30007487133827';
}

// تابع به‌روزرسانی شده ارسال پیامک
function sendSMSUpdated($phone, $message, $patternCode = null, $parameters = []) {
    // بررسی فعال بودن سرویس
    if (!isSMSEnabled()) {
        return [
            'success' => false, 
            'message' => 'سرویس پیامک غیرفعال است یا تنظیمات ناقص است'
        ];
    }
    
    $apiKey = SMS_API_KEY;
    $lineNumber = SMS_LINE_NUMBER;
    
    if ($patternCode && !empty($parameters)) {
        // ارسال با الگو
        $url = 'https://api.sms.ir/v1/send/verify';
        $data = [
            'patternCode' => $patternCode,
            'mobile' => $phone,
            'parameters' => $parameters
        ];
    } else {
        // ارسال متنی ساده
        $url = 'https://api.sms.ir/v1/send/bulk';
        $data = [
            'lineNumber' => $lineNumber,
            'messageText' => $message,
            'mobiles' => [$phone]
        ];
    }
    
    $headers = [
        'Content-Type: application/json',
        'Accept: text/plain',
        'x-api-key: ' . $apiKey
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false, 
            'message' => 'خطا در اتصال: ' . $error
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode == 200 && isset($result['status']) && $result['status'] == 1) {
        return [
            'success' => true, 
            'message' => 'پیامک با موفقیت ارسال شد',
            'data' => $result
        ];
    } else {
        return [
            'success' => false, 
            'message' => 'خطا در ارسال پیامک: ' . ($result['message'] ?? 'خطای نامشخص'),
            'data' => $result
        ];
    }
}

// تابع ارسال پیامک اطلاع‌رسانی ثبت درخواست
function sendNewRequestSMS($phone, $trackingCode, $title) {
    $message = "درخواست شما با کد رهگیری {$trackingCode} ثبت شد. عنوان: {$title} - " . SMS_SIGNATURE;
    
    // اگر الگو تعریف شده باشد
    if (SMS_PATTERN_NEW_REQUEST) {
        $parameters = [
            ['name' => 'tracking_code', 'value' => $trackingCode],
            ['name' => 'title', 'value' => $title],
            ['name' => 'signature', 'value' => SMS_SIGNATURE]
        ];
        return sendSMSUpdated($phone, '', SMS_PATTERN_NEW_REQUEST, $parameters);
    } else {
        return sendSMSUpdated($phone, $message);
    }
}

// تابع ارسال پیامک اطلاع‌رسانی به‌روزرسانی وضعیت
function sendStatusUpdateSMS($phone, $trackingCode, $status) {
    $message = "وضعیت درخواست {$trackingCode} به {$status} تغییر یافت. " . SMS_SIGNATURE;
    
    if (SMS_PATTERN_STATUS_UPDATE) {
        $parameters = [
            ['name' => 'tracking_code', 'value' => $trackingCode],
            ['name' => 'status', 'value' => $status],
            ['name' => 'signature', 'value' => SMS_SIGNATURE]
        ];
        return sendSMSUpdated($phone, '', SMS_PATTERN_STATUS_UPDATE, $parameters);
    } else {
        return sendSMSUpdated($phone, $message);
    }
}

// تابع ارسال پیامک اطلاع‌رسانی دریافت پرداخت
function sendPaymentReceivedSMS($phone, $trackingCode, $amount) {
    $formattedAmount = formatNumber($amount);
    $message = "پرداخت {$formattedAmount} تومان برای درخواست {$trackingCode} دریافت شد. " . SMS_SIGNATURE;
    
    if (SMS_PATTERN_PAYMENT_RECEIVED) {
        $parameters = [
            ['name' => 'tracking_code', 'value' => $trackingCode],
            ['name' => 'amount', 'value' => $formattedAmount],
            ['name' => 'signature', 'value' => SMS_SIGNATURE]
        ];
        return sendSMSUpdated($phone, '', SMS_PATTERN_PAYMENT_RECEIVED, $parameters);
    } else {
        return sendSMSUpdated($phone, $message);
    }
}
?>