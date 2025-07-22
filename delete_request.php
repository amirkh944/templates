<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
    exit;
}

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'متد نامعتبر']);
    exit;
}

// دریافت داده‌ها
$input = json_decode(file_get_contents('php://input'), true);
$requestId = $input['id'] ?? null;

if (!$requestId) {
    echo json_encode(['success' => false, 'message' => 'شناسه درخواست ارسال نشده']);
    exit;
}

try {
    // بررسی وجود درخواست
    $stmt = $pdo->prepare("SELECT id FROM requests WHERE id = ?");
    $stmt->execute([$requestId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'درخواست یافت نشد']);
        exit;
    }
    
    // حذف پرداخت‌های مرتبط
    $stmt = $pdo->prepare("DELETE FROM payments WHERE request_id = ?");
    $stmt->execute([$requestId]);
    
    // حذف ارتباطات مرتبط
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE request_id = ?");
    $stmt->execute([$requestId]);
    
    // حذف درخواست
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$requestId]);
    
    echo json_encode(['success' => true, 'message' => 'درخواست با موفقیت حذف شد']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطا در حذف درخواست: ' . $e->getMessage()]);
}
?>