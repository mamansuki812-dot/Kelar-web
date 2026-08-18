{{-- GLOBAL MODAL (appConfirm / appAlert) — single source, dipakai layout & POS --}}
<div id="app-modal-backdrop" class="fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4" onclick="if(event.target===this) appModalClose(false)">
    <div id="app-modal-box" class="bg-surface rounded-2xl shadow-2xl w-full max-w-sm transform transition-all duration-200 scale-95 opacity-0">
        <div class="p-6 text-center">
            <div id="app-modal-icon" class="mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center"></div>
            <h3 id="app-modal-title" class="text-lg font-bold text-neutral-dark mb-2"></h3>
            <p id="app-modal-msg" class="text-sm text-muted mb-6"></p>
            <div id="app-modal-actions" class="flex gap-3 justify-center"></div>
        </div>
    </div>
</div>
<script>
(function(){
    var backdrop = document.getElementById('app-modal-backdrop');
    var box      = document.getElementById('app-modal-box');
    var iconEl   = document.getElementById('app-modal-icon');
    var titleEl  = document.getElementById('app-modal-title');
    var msgEl    = document.getElementById('app-modal-msg');
    var actEl    = document.getElementById('app-modal-actions');
    var _resolve = null;

    function showModal(type, title, message, opts) {
        opts = opts || {};
        var iconSvg = '';
        if (type === 'confirm') {
            iconEl.className = 'mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center bg-rose-100';
            iconSvg = '<svg class="w-7 h-7 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } else {
            iconEl.className = 'mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center bg-teal-100';
            iconSvg = '<svg class="w-7 h-7 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        }
        if (type === 'success') {
            iconEl.className = 'mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center bg-emerald-100';
            iconSvg = '<svg class="w-7 h-7 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        }
        if (type === 'error') {
            iconEl.className = 'mx-auto mb-4 w-14 h-14 rounded-full flex items-center justify-center bg-rose-100';
            iconSvg = '<svg class="w-7 h-7 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }
        iconEl.innerHTML = iconSvg;
        titleEl.textContent = title;
        msgEl.textContent = message;

        actEl.innerHTML = '';
        if (type === 'confirm') {
            var btnNo = document.createElement('button');
            btnNo.textContent = opts.cancelText || 'Batal';
            btnNo.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl bg-slate-100 text-muted hover:bg-slate-200 transition-colors flex-1';
            btnNo.onclick = function(){ appModalClose(false); };
            var btnYes = document.createElement('button');
            btnYes.textContent = opts.confirmText || 'Ya, Hapus';
            btnYes.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl bg-rose-700 text-white hover:bg-rose-800 transition-colors flex-1 shadow-md';
            btnYes.onclick = function(){ appModalClose(true); };
            actEl.appendChild(btnNo);
            actEl.appendChild(btnYes);
        } else {
            var btnOk = document.createElement('button');
            btnOk.textContent = 'OK';
            btnOk.className = 'px-8 py-2.5 text-sm font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white transition-colors shadow-md';
            btnOk.onclick = function(){ appModalClose(true); };
            actEl.appendChild(btnOk);
        }

        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        requestAnimationFrame(function(){
            box.classList.remove('scale-95','opacity-0');
            box.classList.add('scale-100','opacity-100');
        });
    }

    window.appModalClose = function(result) {
        box.classList.add('scale-95','opacity-0');
        box.classList.remove('scale-100','opacity-100');
        setTimeout(function(){
            backdrop.classList.add('hidden');
            backdrop.classList.remove('flex');
            if (_resolve) { _resolve(result); _resolve = null; }
        }, 150);
    };

    window.appConfirm = function(title, message, opts) {
        return new Promise(function(resolve) {
            _resolve = resolve;
            showModal('confirm', title, message, opts || {});
        });
    };

    window.appAlert = function(title, message, opts) {
        return new Promise(function(resolve) {
            _resolve = resolve;
            showModal(opts && opts.type || 'alert', title, message, opts || {});
        });
    };
})();
</script>
