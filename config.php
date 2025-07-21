<?php
// تنظیمات پایگاه داده
$servername = "localhost";
$username = "pcpasokh_crm";
$password = "Amir137530";
$dbname = "pcpasokh_crm";
// اتصال به پایگاه داده
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// ایجاد جداول در صورت عدم وجود
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    user_id_code VARCHAR(3) UNIQUE NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    device_model VARCHAR(100),
    imei1 VARCHAR(20),
    imei2 VARCHAR(20),
    problem_description TEXT,
    registration_date VARCHAR(20),
    estimated_duration VARCHAR(50),
    actions_required TEXT,
    cost DECIMAL(10,2) DEFAULT 0,
    tracking_code VARCHAR(7) UNIQUE NOT NULL,
    status ENUM('در حال پردازش', 'تکمیل شده', 'لغو شده') DEFAULT 'در حال پردازش',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    request_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('واریز', 'بدهکاری') NOT NULL,
    description TEXT,
    receipt_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (request_id) REFERENCES requests(id)
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    contact_type ENUM('تماس', 'ایمیل', 'پیامک') NOT NULL,
    subject VARCHAR(200),
    message TEXT,
    contact_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
";

$pdo->exec($sql);

// ایجاد کاربر مدیر پیش فرض
$adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'amirkh94'")->fetchColumn();
if (!$adminExists) {
    $hashedPassword = password_hash('Amir137530', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, phone, user_id_code, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['amirkh94', $hashedPassword, 'admin@pasokh.com', '09123456789', '001', true]);
}

// تابع تولید تاریخ شمسی
function jalali_date($format, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    $j_months = array('فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند');
    
    $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $jy = $jm = $jd = 0;
    
    $gy = date('Y', $timestamp);
    $gm = date('n', $timestamp);
    $gd = date('j', $timestamp);
    
    if ($gy <= 1600) {
        $jy = 0;
        $gy -= 621;
    } else {
        $jy = 979;
        $gy -= 1600;
    }
    
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    
    if ($days < 186) {
        $jm = 1 + (int)($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    
    $format = str_replace('Y', $jy, $format);
    $format = str_replace('m', sprintf('%02d', $jm), $format);
    $format = str_replace('d', sprintf('%02d', $jd), $format);
    $format = str_replace('M', $j_months[$jm - 1], $format);
    
    return $format;
}
?>