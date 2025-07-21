    </main>
    
    <!-- Footer -->
    <footer class="footer footer-center p-10 bg-base-200 text-base-content border-t border-base-300 mt-auto">
        <div class="grid grid-flow-col gap-4">
            <a href="#" class="link link-hover">درباره ما</a>
            <a href="#" class="link link-hover">تماس با ما</a>
            <a href="#" class="link link-hover">حریم خصوصی</a>
            <a href="#" class="link link-hover">شرایط استفاده</a>
        </div>
        <div>
            <div class="grid grid-flow-col gap-4">
                <a href="#" class="text-2xl hover:text-primary transition-colors">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-2xl hover:text-primary transition-colors">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-2xl hover:text-primary transition-colors">
                    <i class="fab fa-telegram"></i>
                </a>
                <a href="#" class="text-2xl hover:text-primary transition-colors">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
        <div>
            <p class="text-sm text-base-content/70">
                © <?php echo date('Y'); ?> پاسخگو رایانه. تمامی حقوق محفوظ است.
            </p>
            <p class="text-xs text-base-content/50 mt-1">
                طراحی شده با ❤️ برای ارائه بهترین خدمات
            </p>
        </div>
    </footer>
    
    <!-- Toast notifications container -->
    <div id="toast-container" class="toast toast-top toast-end z-50"></div>
    
    <!-- Loading overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-base-100 rounded-lg p-6 flex items-center gap-4">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <span class="text-base-content">در حال بارگذاری...</span>
        </div>
    </div>
    
    <!-- Common JavaScript functions -->
    <script>
        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let alertClass = 'alert-info';
            let icon = 'fa-info-circle';
            
            switch(type) {
                case 'success':
                    alertClass = 'alert-success';
                    icon = 'fa-check-circle';
                    break;
                case 'error':
                    alertClass = 'alert-error';
                    icon = 'fa-exclamation-circle';
                    break;
                case 'warning':
                    alertClass = 'alert-warning';
                    icon = 'fa-exclamation-triangle';
                    break;
            }
            
            toast.className = `alert ${alertClass} shadow-lg slide-in`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas ${icon}"></i>
                    <span>${message}</span>
                </div>
                <div class="flex-none">
                    <button class="btn btn-sm btn-ghost" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
        
        // Loading overlay functions
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }
        
        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }
        
        // Confirm dialog
        function confirmDialog(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('input-error');
                    isValid = false;
                } else {
                    input.classList.remove('input-error');
                }
            });
            
            return isValid;
        }
        
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
        
        // Enhanced number formatting for Persian
        function formatPersianNumber(num) {
            const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
            return num.toString().replace(/\d/g, x => persianDigits[x]);
        }
        
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('کپی شد!', 'success');
            }).catch(() => {
                showToast('خطا در کپی کردن', 'error');
            });
        }
    </script>
</body>
</html>