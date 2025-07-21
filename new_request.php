<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'sms_config.php';

checkLogin();

$pageTitle = 'درخواست جدید - پاسخگو رایانه';
$breadcrumbs = [
    ['title' => 'درخواست جدید']
];

$message = '';
$customers = getAllCustomers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerName = $_POST['customer_name'];
    $customerPhone = $_POST['customer_phone'];
    $customerEmail = $_POST['customer_email'] ?? '';
    $existingCustomerId = $_POST['existing_customer'] ?? '';
    
    $title = $_POST['title'];
    $deviceModel = $_POST['device_model'];
    $imei1 = $_POST['imei1'];
    $imei2 = $_POST['imei2'];
    $problemDescription = $_POST['problem_description'];
    $estimatedDuration = $_POST['estimated_duration'];
    $actionsRequired = $_POST['actions_required'];
    $cost = floatval($_POST['cost']);
    
    try {
        // تعیین مشتری
        if ($existingCustomerId) {
            $customerId = $existingCustomerId;
        } else {
            // بررسی وجود مشتری با همین شماره تلفن
            $existingCustomer = getCustomerByPhone($customerPhone);
            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            } else {
                $customerId = createCustomer($customerName, $customerPhone, $customerEmail);
            }
        }
        
        // ایجاد درخواست
        $requestId = createRequest($customerId, $title, $deviceModel, $imei1, $imei2, $problemDescription, $estimatedDuration, $actionsRequired, $cost);
        
        // دریافت کد رهگیری
        $request = getRequest($requestId);
        $trackingCode = $request['tracking_code'];
        
        $message = "success|درخواست با موفقیت ثبت شد! کد رهگیری: $trackingCode";
        
        // ارسال پیامک
        if ($customerPhone && !empty($customerPhone)) {
            $smsMessage = "درخواست شما در پاسخگو رایانه ثبت شد.\nکد رهگیری: $trackingCode\nجهت پیگیری: pasokhraya.com";
            sendSMS($customerPhone, $smsMessage);
        }
        
    } catch (Exception $e) {
        $message = "error|خطا در ثبت درخواست: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="space-y-8">
    
    <!-- هدر صفحه -->
    <div class="text-center">
        <div class="w-20 h-20 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-plus-circle text-3xl text-primary-content"></i>
        </div>
        <h1 class="text-4xl font-bold text-base-content mb-4">
            ثبت درخواست جدید
        </h1>
        <p class="text-lg text-base-content/70 max-w-2xl mx-auto">
            اطلاعات درخواست و مشتری را با دقت وارد کنید
        </p>
    </div>
    
    <!-- پیام‌های موفقیت/خطا -->
    <?php if ($message): ?>
        <?php 
        $parts = explode('|', $message);
        $type = $parts[0];
        $text = $parts[1];
        ?>
        <div class="alert <?php echo $type === 'success' ? 'alert-success' : 'alert-error'; ?> max-w-4xl mx-auto">
            <i class="fas <?php echo $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo $text; ?></span>
            <?php if ($type === 'success' && isset($trackingCode)): ?>
                <div class="flex gap-2">
                    <button onclick="copyToClipboard('<?php echo $trackingCode; ?>')" class="btn btn-sm">
                        <i class="fas fa-copy"></i>
                        کپی کد
                    </button>
                    <a href="public/customer/track.php" class="btn btn-sm btn-outline">
                        پیگیری آنلاین
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- فرم اصلی -->
    <form method="POST" id="newRequestForm" class="max-w-6xl mx-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- بخش اطلاعات مشتری -->
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-6">
                        <i class="fas fa-user text-primary ml-2"></i>
                        اطلاعات مشتری
                    </h2>
                    
                    <!-- انتخاب نوع مشتری -->
                    <div class="form-control mb-6">
                        <div class="flex gap-4">
                            <label class="label cursor-pointer">
                                <span class="label-text ml-2">مشتری جدید</span>
                                <input type="radio" name="customer_type" value="new" class="radio radio-primary" checked 
                                       onchange="toggleCustomerForm()">
                            </label>
                            <label class="label cursor-pointer">
                                <span class="label-text ml-2">مشتری موجود</span>
                                <input type="radio" name="customer_type" value="existing" class="radio radio-primary" 
                                       onchange="toggleCustomerForm()">
                            </label>
                        </div>
                    </div>
                    
                    <!-- انتخاب مشتری موجود -->
                    <div id="existingCustomerDiv" class="form-control mb-4 hidden">
                        <label class="label">
                            <span class="label-text font-medium">انتخاب مشتری</span>
                        </label>
                        <select name="existing_customer" class="select select-bordered w-full">
                            <option value="">یک مشتری را انتخاب کنید</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo htmlspecialchars($customer['name']) . ' - ' . $customer['phone']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- فرم مشتری جدید -->
                    <div id="newCustomerDiv">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        <span class="text-error">*</span> نام و نام خانوادگی
                                    </span>
                                </label>
                                <input type="text" name="customer_name" placeholder="نام کامل مشتری" 
                                       class="input input-bordered w-full" required>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        <span class="text-error">*</span> شماره تلفن
                                    </span>
                                </label>
                                <input type="tel" name="customer_phone" placeholder="09123456789" 
                                       class="input input-bordered w-full" required>
                            </div>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">ایمیل (اختیاری)</span>
                            </label>
                            <input type="email" name="customer_email" placeholder="email@example.com" 
                                   class="input input-bordered w-full">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- بخش اطلاعات درخواست -->
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-6">
                        <i class="fas fa-clipboard-list text-secondary ml-2"></i>
                        اطلاعات درخواست
                    </h2>
                    
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">
                                <span class="text-error">*</span> عنوان درخواست
                            </span>
                        </label>
                        <input type="text" name="title" placeholder="مثال: تعمیر لپ تاپ" 
                               class="input input-bordered w-full" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">مدل دستگاه</span>
                            </label>
                            <input type="text" name="device_model" placeholder="مثال: Dell Inspiron 15" 
                                   class="input input-bordered w-full">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">مدت زمان تخمینی</span>
                            </label>
                            <select name="estimated_duration" class="select select-bordered w-full">
                                <option value="">انتخاب کنید</option>
                                <option value="1-2 روز">۱-۲ روز</option>
                                <option value="3-5 روز">۳-۵ روز</option>
                                <option value="1 هفته">۱ هفته</option>
                                <option value="2 هفته">۲ هفته</option>
                                <option value="1 ماه">۱ ماه</option>
                                <option value="بیش از 1 ماه">بیش از ۱ ماه</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">IMEI 1 (اختیاری)</span>
                            </label>
                            <input type="text" name="imei1" placeholder="15 رقمی" 
                                   class="input input-bordered w-full font-mono">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">IMEI 2 (اختیاری)</span>
                            </label>
                            <input type="text" name="imei2" placeholder="15 رقمی" 
                                   class="input input-bordered w-full font-mono">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">هزینه (تومان)</span>
                        </label>
                        <input type="number" name="cost" placeholder="0" min="0" 
                               class="input input-bordered w-full" step="1000">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- بخش توضیحات -->
        <div class="card bg-base-100 shadow-xl border border-base-300 mt-8">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">
                    <i class="fas fa-edit text-accent ml-2"></i>
                    توضیحات و جزئیات
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">شرح مشکل</span>
                        </label>
                        <textarea name="problem_description" rows="6" 
                                  placeholder="توضیح کاملی از مشکل دستگاه ارائه دهید..."
                                  class="textarea textarea-bordered w-full resize-none"></textarea>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">اقدامات مورد نیاز</span>
                        </label>
                        <textarea name="actions_required" rows="6" 
                                  placeholder="اقدامات و کارهایی که باید انجام شود..."
                                  class="textarea textarea-bordered w-full resize-none"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- دکمه‌های عملیات -->
        <div class="flex justify-center gap-4 mt-8">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save ml-2"></i>
                ثبت درخواست
            </button>
            <button type="reset" class="btn btn-outline btn-lg">
                <i class="fas fa-undo ml-2"></i>
                پاک کردن
            </button>
            <a href="dashboard.php" class="btn btn-ghost btn-lg">
                <i class="fas fa-times ml-2"></i>
                انصراف
            </a>
        </div>
        
    </form>
</div>

<!-- اسکریپت‌ها -->
<script>
    // تغییر حالت فرم مشتری
    function toggleCustomerForm() {
        const customerType = document.querySelector('input[name="customer_type"]:checked').value;
        const newDiv = document.getElementById('newCustomerDiv');
        const existingDiv = document.getElementById('existingCustomerDiv');
        
        if (customerType === 'new') {
            newDiv.classList.remove('hidden');
            existingDiv.classList.add('hidden');
            
            // فعال کردن validation برای فیلدهای جدید
            document.querySelector('input[name="customer_name"]').required = true;
            document.querySelector('input[name="customer_phone"]').required = true;
            document.querySelector('select[name="existing_customer"]').required = false;
        } else {
            newDiv.classList.add('hidden');
            existingDiv.classList.remove('hidden');
            
            // غیرفعال کردن validation برای فیلدهای جدید
            document.querySelector('input[name="customer_name"]').required = false;
            document.querySelector('input[name="customer_phone"]').required = false;
            document.querySelector('select[name="existing_customer"]').required = true;
        }
    }
    
    // اعتبارسنجی شماره تلفن
    document.querySelector('input[name="customer_phone"]').addEventListener('input', function(e) {
        // فقط اعداد مجاز
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // محدود کردن به 11 رقم
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });
    
    // اعتبارسنجی IMEI
    function setupIMEIValidation(inputName) {
        document.querySelector(`input[name="${inputName}"]`).addEventListener('input', function(e) {
            // فقط اعداد مجاز
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // محدود کردن به 15 رقم
            if (this.value.length > 15) {
                this.value = this.value.slice(0, 15);
            }
        });
    }
    
    setupIMEIValidation('imei1');
    setupIMEIValidation('imei2');
    
    // فرمت کردن قیمت
    document.querySelector('input[name="cost"]').addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = value;
        }
    });
    
    // انتخاب مشتری موجود
    document.querySelector('select[name="existing_customer"]').addEventListener('change', function(e) {
        if (this.value) {
            // پر کردن فیلدهای مشتری از گزینه انتخاب شده
            const option = this.selectedOptions[0];
            const text = option.text;
            const parts = text.split(' - ');
            
            if (parts.length >= 2) {
                document.querySelector('input[name="customer_name"]').value = parts[0];
                document.querySelector('input[name="customer_phone"]').value = parts[1];
            }
        }
    });
    
    // اعتبارسنجی فرم قبل از ارسال
    document.getElementById('newRequestForm').addEventListener('submit', function(e) {
        const customerType = document.querySelector('input[name="customer_type"]:checked').value;
        
        if (customerType === 'new') {
            const name = document.querySelector('input[name="customer_name"]').value.trim();
            const phone = document.querySelector('input[name="customer_phone"]').value.trim();
            
            if (!name || !phone) {
                e.preventDefault();
                showToast('لطفاً اطلاعات مشتری را کامل وارد کنید', 'error');
                return;
            }
            
            if (phone.length !== 11 || !phone.startsWith('09')) {
                e.preventDefault();
                showToast('شماره تلفن باید 11 رقم و با 09 شروع شود', 'error');
                return;
            }
        } else {
            const existingCustomer = document.querySelector('select[name="existing_customer"]').value;
            if (!existingCustomer) {
                e.preventDefault();
                showToast('لطفاً یک مشتری را انتخاب کنید', 'error');
                return;
            }
        }
        
        const title = document.querySelector('input[name="title"]').value.trim();
        if (!title) {
            e.preventDefault();
            showToast('لطفاً عنوان درخواست را وارد کنید', 'error');
            return;
        }
        
        // نمایش loading
        showLoading();
    });
    
    // پاک کردن فرم
    document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
        if (!confirm('آیا مطمئن هستید که می‌خواهید فرم را پاک کنید؟')) {
            e.preventDefault();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>