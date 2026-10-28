<div x-data="confirmDialogComponent()" 
     x-cloak
     x-show="isOpen" 
     @confirm-action.window="open($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop Blur & Fade -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="cancel()"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <!-- Centered Modal Card -->
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @keydown.escape.window="cancel()"
             class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-center shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-slate-200/90">
            
            <!-- Centered Circular Icon with Ring Accent -->
            <div class="mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center shadow-2xs transition"
                 :class="{
                     'bg-rose-100 text-rose-600 ring-8 ring-rose-50': type === 'danger',
                     'bg-amber-100 text-amber-600 ring-8 ring-amber-50': type === 'warning',
                     'bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50': type === 'success' || type === 'info'
                 }">
                
                <!-- Danger Icon (Trash) -->
                <template x-if="type === 'danger'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </template>

                <!-- Warning Icon (Exclamation Triangle) -->
                <template x-if="type === 'warning'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>

                <!-- Success / Info Icon (Checkmark) -->
                <template x-if="type === 'success' || type === 'info'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>

            <!-- Title & Short Message -->
            <h3 class="text-base font-bold text-slate-900 leading-tight" x-text="title"></h3>

            <!-- To-the-point Message (Max 1-2 lines) -->
            <p class="text-xs text-slate-500 mt-2 leading-relaxed px-1" x-text="message"></p>

            <!-- 2 Compact Grid Action Buttons -->
            <div class="grid grid-cols-2 gap-2.5 mt-5">
                <button type="button" 
                        @click="cancel()" 
                        class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200/80 rounded-xl transition cursor-pointer"
                        x-text="cancelText">
                </button>

                <button type="button" 
                        @click="confirm()" 
                        class="w-full py-2.5 px-3 text-xs font-semibold text-white rounded-xl shadow-xs transition cursor-pointer"
                        :class="{
                            'bg-rose-600 hover:bg-rose-700': type === 'danger',
                            'bg-amber-600 hover:bg-amber-700': type === 'warning',
                            'bg-emerald-600 hover:bg-emerald-700': type === 'success' || type === 'info'
                        }">
                    <span x-text="confirmText"></span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function confirmDialogComponent() {
        return {
            isOpen: false,
            title: 'Konfirmasi Tindakan',
            targetName: '',
            message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            type: 'danger',
            confirmText: 'Konfirmasi',
            cancelText: 'Batal',
            onConfirmCallback: null,
            targetForm: null,

            open(options = {}) {
                this.title = options.title || 'Konfirmasi Tindakan';
                this.targetName = options.targetName || '';
                this.message = options.message || 'Apakah Anda yakin ingin melanjutkan?';
                this.type = options.type || 'danger';
                this.confirmText = options.confirmText || (this.type === 'danger' ? 'Ya, Hapus' : (this.type === 'warning' ? 'Ya, Nonaktifkan' : 'Lanjutkan'));
                this.cancelText = options.cancelText || 'Batal';
                this.onConfirmCallback = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                this.targetForm = options.form || null;
                this.isOpen = true;
            },

            confirm() {
                this.isOpen = false;
                if (this.onConfirmCallback) {
                    this.onConfirmCallback();
                } else if (this.targetForm) {
                    this.targetForm.submit();
                }
            },

            cancel() {
                this.isOpen = false;
                this.onConfirmCallback = null;
                this.targetForm = null;
            }
        };
    }

    // Global Helper
    window.confirmAction = function(options) {
        window.dispatchEvent(new CustomEvent('confirm-action', { detail: options }));
    };
</script>
