<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

checkLogin();

$pageTitle = 'مدیریت درخواست‌ها - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'مدیریت درخواست‌ها']
];

// فیلترها
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'DESC';

// پیجینیشن
$page = $_GET['page'] ?? 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// ساخت کوئری
$whereConditions = [];
$params = [];

if ($statusFilter) {
    $whereConditions[] = "r.status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $whereConditions[] = "(r.title LIKE ? OR r.tracking_code LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// شمارش کل
$countQuery = "SELECT COUNT(*) FROM requests r JOIN customers c ON r.customer_id = c.id $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalRequests = $stmt->fetchColumn();
$totalPages = ceil($totalRequests / $perPage);

// دریافت درخواست‌ها
$query = "
    SELECT r.*, c.name as customer_name, c.phone as customer_phone
    FROM requests r
    JOIN customers c ON r.customer_id = c.id
    $whereClause
    ORDER BY r.$sortBy $sortOrder
    LIMIT $perPage OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// آمار سریع
$stats = getStats();

include 'includes/header.php';
?>

<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-base-content flex items-center gap-3">
                <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-primary-content text-xl"></i>
                </div>
                مدیریت درخواست‌ها
            </h1>
            <p class="text-base-content/70 mt-2">
                مشاهده، جستجو و مدیریت تمامی درخواست‌های ثبت شده
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="new_request.php" class="btn btn-primary">
                <i class="fas fa-plus ml-2"></i>
                درخواست جدید
            </a>
            <a href="search_requests.php" class="btn btn-outline btn-secondary">
                <i class="fas fa-search ml-2"></i>
                جستجوی پیشرفته
            </a>
        </div>
    </div>
    
    <!-- آمار سریع -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat bg-base-100 shadow-lg border border-base-300 rounded-2xl">
            <div class="stat-figure text-primary">
                <i class="fas fa-clipboard-list text-3xl"></i>
            </div>
            <div class="stat-title">کل درخواست‌ها</div>
            <div class="stat-value text-primary"><?php echo en2fa($stats['total_requests']); ?></div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg border border-base-300 rounded-2xl">
            <div class="stat-figure text-warning">
                <i class="fas fa-clock text-3xl"></i>
            </div>
            <div class="stat-title">در حال پردازش</div>
            <div class="stat-value text-warning"><?php echo en2fa($stats['pending_requests']); ?></div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg border border-base-300 rounded-2xl">
            <div class="stat-figure text-success">
                <i class="fas fa-check-circle text-3xl"></i>
            </div>
            <div class="stat-title">تکمیل شده</div>
            <div class="stat-value text-success"><?php echo en2fa($stats['completed_requests']); ?></div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg border border-base-300 rounded-2xl">
            <div class="stat-figure text-info">
                <i class="fas fa-filter text-3xl"></i>
            </div>
            <div class="stat-title">فیلتر شده</div>
            <div class="stat-value text-info"><?php echo en2fa($totalRequests); ?></div>
        </div>
    </div>
    
    <!-- فیلترها و جستجو -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <i class="fas fa-filter text-secondary ml-2"></i>
                فیلترها و جستجو
            </h2>
            
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- جستجو -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">جستجو</span>
                        </label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                               placeholder="عنوان، کد رهگیری، نام مشتری..." 
                               class="input input-bordered w-full">
                    </div>
                    
                    <!-- فیلتر وضعیت -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">وضعیت</span>
                        </label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">همه</option>
                            <option value="در حال پردازش" <?php echo $statusFilter == 'در حال پردازش' ? 'selected' : ''; ?>>در حال پردازش</option>
                            <option value="تکمیل شده" <?php echo $statusFilter == 'تکمیل شده' ? 'selected' : ''; ?>>تکمیل شده</option>
                            <option value="لغو شده" <?php echo $statusFilter == 'لغو شده' ? 'selected' : ''; ?>>لغو شده</option>
                        </select>
                    </div>
                    
                    <!-- مرتب‌سازی -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">مرتب‌سازی بر اساس</span>
                        </label>
                        <select name="sort" class="select select-bordered w-full">
                            <option value="created_at" <?php echo $sortBy == 'created_at' ? 'selected' : ''; ?>>تاریخ ثبت</option>
                            <option value="title" <?php echo $sortBy == 'title' ? 'selected' : ''; ?>>عنوان</option>
                            <option value="status" <?php echo $sortBy == 'status' ? 'selected' : ''; ?>>وضعیت</option>
                            <option value="cost" <?php echo $sortBy == 'cost' ? 'selected' : ''; ?>>هزینه</option>
                        </select>
                    </div>
                    
                    <!-- ترتیب -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">ترتیب</span>
                        </label>
                        <select name="order" class="select select-bordered w-full">
                            <option value="DESC" <?php echo $sortOrder == 'DESC' ? 'selected' : ''; ?>>نزولی</option>
                            <option value="ASC" <?php echo $sortOrder == 'ASC' ? 'selected' : ''; ?>>صعودی</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search ml-2"></i>
                        جستجو
                    </button>
                    <a href="requests.php" class="btn btn-ghost">
                        <i class="fas fa-times ml-2"></i>
                        پاک کردن فیلترها
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- جدول درخواست‌ها -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body p-0">
            
            <!-- هدر جدول -->
            <div class="p-6 border-b border-base-300">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">
                        <i class="fas fa-table text-accent ml-2"></i>
                        لیست درخواست‌ها
                    </h2>
                    <div class="text-sm text-base-content/70">
                        نمایش <?php echo en2fa($offset + 1); ?> تا <?php echo en2fa(min($offset + $perPage, $totalRequests)); ?> از <?php echo en2fa($totalRequests); ?> درخواست
                    </div>
                </div>
            </div>
            
            <?php if (empty($requests)): ?>
            <!-- پیام خالی -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-base-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-4xl text-base-content/30"></i>
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">درخواستی یافت نشد</h3>
                <p class="text-base-content/70 mb-6">
                    <?php if ($searchQuery || $statusFilter): ?>
                        با فیلترهای انتخاب شده، درخواستی پیدا نشد. لطفاً فیلترها را تغییر دهید.
                    <?php else: ?>
                        هنوز هیچ درخواستی ثبت نشده است. اولین درخواست را ایجاد کنید.
                    <?php endif; ?>
                </p>
                <a href="new_request.php" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    ثبت درخواست جدید
                </a>
            </div>
            
            <?php else: ?>
            
            <!-- جدول -->
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>کد رهگیری</th>
                            <th>عنوان</th>
                            <th>مشتری</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>هزینه</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-2">
                                    <code class="bg-primary/10 text-primary px-2 py-1 rounded font-bold">
                                        <?php echo en2fa($request['tracking_code']); ?>
                                    </code>
                                    <button onclick="copyToClipboard('<?php echo $request['tracking_code']; ?>')" 
                                            class="btn btn-ghost btn-xs">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            
                            <td>
                                <div class="max-w-xs">
                                    <div class="font-medium text-base-content">
                                        <?php echo htmlspecialchars($request['title']); ?>
                                    </div>
                                    <?php if ($request['device_model']): ?>
                                    <div class="text-sm text-base-content/60">
                                        <?php echo htmlspecialchars($request['device_model']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                            <span class="text-xs"><?php echo mb_substr($request['customer_name'], 0, 1); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-base-content">
                                            <?php echo htmlspecialchars($request['customer_name']); ?>
                                        </div>
                                        <div class="text-sm text-base-content/60">
                                            <?php echo $request['customer_phone']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="badge <?php 
                                    switch($request['status']) {
                                        case 'تکمیل شده':
                                            echo 'badge-success';
                                            break;
                                        case 'لغو شده':
                                            echo 'badge-error';
                                            break;
                                        default:
                                            echo 'badge-warning';
                                    }
                                    ?>">
                                    <?php echo $request['status']; ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium"><?php echo en2fa(jalali_date('Y/m/d', strtotime($request['created_at']))); ?></div>
                                    <div class="text-base-content/60"><?php echo en2fa(date('H:i', strtotime($request['created_at']))); ?></div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="font-bold text-lg">
                                    <?php echo en2fa(number_format($request['cost'])); ?>
                                    <span class="text-sm font-normal text-base-content/60">تومان</span>
                                </div>
                            </td>
                            
                            <td>
                                <div class="flex justify-center gap-1">
                                    <div class="dropdown dropdown-end">
                                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                            <li>
                                                <a href="view_request.php?id=<?php echo $request['id']; ?>" class="text-sm">
                                                    <i class="fas fa-eye"></i>
                                                    مشاهده جزئیات
                                                </a>
                                            </li>
                                            <li>
                                                <a href="edit_request.php?id=<?php echo $request['id']; ?>" class="text-sm">
                                                    <i class="fas fa-edit"></i>
                                                    ویرایش
                                                </a>
                                            </li>
                                            <li>
                                                <a href="print_receipt.php?id=<?php echo $request['id']; ?>" class="text-sm">
                                                    <i class="fas fa-print"></i>
                                                    چاپ رسید
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a href="add_payment.php?request_id=<?php echo $request['id']; ?>" class="text-sm">
                                                    <i class="fas fa-credit-card"></i>
                                                    افزودن پرداخت
                                                </a>
                                            </li>
                                            <li>
                                                <a href="send_sms.php?request_id=<?php echo $request['id']; ?>" class="text-sm">
                                                    <i class="fas fa-sms"></i>
                                                    ارسال پیامک
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a onclick="deleteRequest(<?php echo $request['id']; ?>)" class="text-sm text-error">
                                                    <i class="fas fa-trash"></i>
                                                    حذف
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
    
    <!-- پیجینیشن -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center">
        <div class="join">
            <!-- صفحه قبل -->
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
               class="join-item btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
            
            <!-- شماره صفحات -->
            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                   class="join-item btn <?php echo $i == $page ? 'btn-active' : ''; ?>">
                    <?php echo en2fa($i); ?>
                </a>
            <?php endfor; ?>
            
            <!-- صفحه بعد -->
            <?php if ($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
               class="join-item btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<!-- مودال تایید حذف -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">
            <i class="fas fa-exclamation-triangle ml-2"></i>
            تایید حذف
        </h3>
        <p class="py-4">آیا از حذف این درخواست اطمینان دارید؟ این عمل قابل بازگشت نیست.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">انصراف</button>
            </form>
            <button id="confirmDelete" class="btn btn-error">
                <i class="fas fa-trash ml-1"></i>
                حذف
            </button>
        </div>
    </div>
</dialog>

<script>
    let deleteRequestId = null;
    
    function deleteRequest(id) {
        deleteRequestId = id;
        document.getElementById('deleteModal').showModal();
    }
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteRequestId) {
            // ارسال درخواست حذف
            fetch('delete_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: deleteRequestId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('درخواست با موفقیت حذف شد', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('خطا در حذف درخواست', 'error');
                }
            })
            .catch(error => {
                showToast('خطا در ارتباط با سرور', 'error');
            });
            
            document.getElementById('deleteModal').close();
        }
    });
    
    // تابع ایجاد درخواست فوری
    function createQuickRequest() {
        // فتح یک مودال یا صفحه جدید برای ایجاد سریع درخواست
        window.open('new_request.php?quick=1', '_blank', 'width=800,height=600');
    }
</script>

<?php include 'includes/footer.php'; ?>