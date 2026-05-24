<div id="appConfirmModal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle">
    <div class="absolute inset-0" data-confirm-cancel></div>
    <div class="relative w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl shadow-slate-950/20">
        <div class="flex items-start gap-3">
            <div id="appConfirmIcon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </div>
            <div class="min-w-0">
                <h2 id="appConfirmTitle" class="text-base font-bold text-slate-900">Lanjutkan aksi?</h2>
                <p id="appConfirmMessage" class="mt-1 text-sm leading-relaxed text-slate-500">
                    Aksi ini akan diproses oleh sistem.
                </p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">
            <button type="button" data-confirm-cancel data-confirm-cancel-button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Batal
            </button>
            <button type="button" id="appConfirmButton" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700">
                Lanjut
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('appConfirmModal');
    const title = document.getElementById('appConfirmTitle');
    const message = document.getElementById('appConfirmMessage');
    const icon = document.getElementById('appConfirmIcon');
    const confirmButton = document.getElementById('appConfirmButton');
    const cancelButton = modal?.querySelector('[data-confirm-cancel-button]');
    let pendingForm = null;
    let focusedTrigger = null;
    let pendingResolver = null;

    if (!modal || !title || !message || !icon || !confirmButton) return;

    const toneClasses = {
        danger: {
            icon: 'bg-red-50 text-red-600',
            button: 'bg-red-600 hover:bg-red-700 shadow-red-600/20',
        },
        primary: {
            icon: 'bg-blue-50 text-blue-700',
            button: 'bg-blue-700 hover:bg-blue-800 shadow-blue-700/20',
        },
    };

    const setTone = (tone = 'danger') => {
        const classes = toneClasses[tone] || toneClasses.danger;
        icon.className = `flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${classes.icon}`;
        confirmButton.className = `rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-lg transition ${classes.button}`;
    };

    const openConfirm = (options = {}) => {
        title.textContent = options.title || 'Lanjutkan aksi?';
        message.textContent = options.message || 'Aksi ini akan diproses oleh sistem.';
        confirmButton.textContent = options.confirmText || 'Lanjut';
        icon.innerHTML = `<i class="${options.icon || 'fa-solid fa-triangle-exclamation'}"></i>`;
        setTone(options.tone || 'danger');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        cancelButton?.focus();
    };

    const closeConfirm = (confirmed = false) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        if (pendingResolver) {
            const resolve = pendingResolver;
            pendingResolver = null;
            resolve(confirmed);
        }

        if (!confirmed) {
            pendingForm = null;
        }

        focusedTrigger?.focus();
        focusedTrigger = null;
    };

    window.presensiConfirm = (options = {}) => new Promise((resolve) => {
        pendingResolver = resolve;
        openConfirm(options);
    });

    document.querySelectorAll('[data-confirm-form], [data-logout-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingForm = form;
            focusedTrigger = event.submitter || form.querySelector('button[type="submit"]');

            if (form.matches('[data-logout-form]')) {
                openConfirm({
                    title: form.dataset.confirmTitle || 'Keluar dari akun?',
                    message: form.dataset.confirmMessage || 'Sesi kamu akan ditutup dan perlu login lagi untuk masuk ke aplikasi.',
                    confirmText: form.dataset.confirmButton || 'Logout',
                    icon: form.dataset.confirmIcon || 'fa-solid fa-arrow-right-from-bracket',
                    tone: 'danger',
                });
                return;
            }

            openConfirm({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirmMessage,
                confirmText: form.dataset.confirmButton,
                icon: form.dataset.confirmIcon,
                tone: form.dataset.confirmTone,
            });
        });
    });

    document.querySelectorAll('[data-confirm-cancel]').forEach((button) => {
        button.addEventListener('click', () => closeConfirm(false));
    });

    confirmButton.addEventListener('click', () => {
        if (pendingForm) {
            const form = pendingForm;
            pendingForm = null;
            form.dataset.confirmed = 'true';
            closeConfirm(true);
            form.requestSubmit();
            return;
        }

        closeConfirm(true);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeConfirm(false);
        }
    });
})();
</script>
