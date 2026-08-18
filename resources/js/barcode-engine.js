import { BarcodeDetector, prepareZXingModule, ZXING_WASM_VERSION, ZXING_WASM_SHA256 } from 'barcode-detector/ponyfill';
import wasmUrl from 'zxing-wasm/reader/zxing_reader.wasm?url';

prepareZXingModule({
    overrides: {
        locateFile: (path, prefix) => {
            if (path.endsWith('.wasm')) return wasmUrl;
            return prefix + path;
        },
    },
});

window.KELARScanner = (function () {
    'use strict';

    var S = null;
    var gen = 0;
    var detector = null;
    var DETECT_FORMATS = ['code_128', 'ean_13', 'qr_code'];
    var CROP_TARGET = 640;
    var REGION = { x: 0.15, y: 0.175, w: 0.7, h: 0.65 };
    var cropCanvas = null;
    var TIPS = [
        'Coba dekatkan kamera 10-15 cm dari barcode',
        'Pastikan barcode tidak silau atau kena pantulan cahaya',
        'Coba nyalakan lampu (tombol Lampu) jika cahaya kurang',
        'Posisikan barcode sejajar horizontal dengan garis scan'
    ];

    function el(id) { return document.getElementById(id); }
    function isDebug() { return !!window.__KELAR_DEBUG__; }

    console.log('[KELAR] barcode-detector (zxing-wasm) v' + ZXING_WASM_VERSION + ' sha256=' + ZXING_WASM_SHA256);

    function open(opts) {
        if (S && S.active) return;
        if (S) close();
        gen++;
        var myGen = gen;
        S = {
            opts: opts,
            active: true,
            stream: null,
            videoEl: null,
            audio: null,
            lastScan: 0,
            torchOn: false,
            tipIdx: -1,
            ready: false,
            failed: false,
            frameEl: null,
            lineEl: null,
            timers: []
        };
        buildUI();
        startCameraEntry(myGen);
    }

    function startCameraEntry(myGen) {
        if (myGen !== gen || !S || !S.active) return;
        setStatus('Memulai kamera...');
        hideScanOverlay();
        ensureDetector().then(function () {
            if (myGen !== gen || !S || !S.active) return;
            startCamera(myGen);
        }).catch(function (err) {
            if (myGen !== gen) return;
            showCameraError(err);
        });
    }

    function ensureDetector() {
        if (detector) return Promise.resolve(detector);
        return BarcodeDetector.getSupportedFormats().then(function (sup) {
            console.log('[KELAR] supported formats: ' + ((sup || []).length || 0));
            var ok = (sup || []).filter(function (f) { return DETECT_FORMATS.indexOf(f) !== -1; });
            detector = new BarcodeDetector({ formats: ok.length ? ok : DETECT_FORMATS });
            return detector;
        });
    }

    function startCamera(myGen) {
        function makeV(facing) {
            var video = {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                advanced: [{ focusMode: 'continuous' }]
            };
            if (facing) video.facingMode = facing;
            return { audio: false, video: video };
        }
        var attempts = [{ exact: 'environment' }, { ideal: 'environment' }, null];
        var idx = 0;
        var lastErr = null;
        function next() {
            if (myGen !== gen || !S || !S.active) return Promise.reject(new Error('scan-closed'));
            if (idx >= attempts.length) {
                console.log('[KELAR] camera: all ' + attempts.length + ' attempts failed, stopping (no restart).');
                return Promise.reject(lastErr || new Error('all-camera-attempts-failed'));
            }
            var facing = attempts[idx];
            var num = idx + 1;
            idx++;
            return navigator.mediaDevices.getUserMedia(makeV(facing)).catch(function (err) {
                lastErr = err;
                console.log('[KELAR] camera attempt ' + num + ' failed: facingMode=' + JSON.stringify(facing || 'default') + ', error: ' + (err && err.message ? err.message : err) + ', trying fallback...');
                return next();
            });
        }
        next().then(function (stream) {
            if (myGen !== gen || !S || !S.active) { if (stream) stream.getTracks().forEach(function (t) { t.stop(); }); return; }
            S.stream = stream;
            console.log('[KELAR] barcode engine: barcode-detector (zxing-wasm)');
            attachVideo(stream);
            setCameraReady();
            setupTorch();
            S.timers.push(setInterval(reapplyFocus, 4000));
            S.timers.push(setInterval(detectTick, 120));
            startFeedback();
        }).catch(function (err) {
            if (myGen !== gen) return;
            showCameraError(err);
        });
    }

    function attachVideo(stream) {
        var v = document.createElement('video');
        v.srcObject = stream;
        v.setAttribute('playsinline', 'true');
        v.setAttribute('autoplay', 'true');
        v.setAttribute('muted', 'true');
        v.style.cssText = 'width:100%;border-radius:12px;display:block;';
        S.opts.containerEl.appendChild(v);
        S.videoEl = v;
        v.play().catch(function () {});
    }

    function detectTick() {
        var s = S;
        if (!s || !s.active) return;
        var v = s.videoEl;
        if (!v || v.readyState < 2 || !v.videoWidth || !v.videoHeight) return;
        var iw = v.videoWidth, ih = v.videoHeight;
        try {
            if (!cropCanvas) cropCanvas = document.createElement('canvas');
            var rw = Math.max(4, Math.round(iw * REGION.w));
            var rh = Math.max(4, Math.round(ih * REGION.h));
            var rx = Math.max(0, Math.min(iw - rw, Math.round(iw * REGION.x)));
            var ry = Math.max(0, Math.min(ih - rh, Math.round(ih * REGION.y)));
            var scale = CROP_TARGET / rw;
            if (scale > 2) scale = 2;
            if (scale < 0.4) scale = 0.4;
            var dw = Math.max(4, Math.round(rw * scale));
            var dh = Math.max(4, Math.round(rh * scale));
            if (cropCanvas.width !== dw) cropCanvas.width = dw;
            if (cropCanvas.height !== dh) cropCanvas.height = dh;
            var ctx = cropCanvas.getContext('2d', { willReadFrequently: true });
            ctx.drawImage(v, rx, ry, rw, rh, 0, 0, dw, dh);
            detector.detect(cropCanvas).then(function (results) {
                if (!s || s !== S || !s.active) return;
                if (isDebug()) console.log('[KELAR][debug] detect(' + iw + 'x' + ih + ' -> crop ' + dw + 'x' + dh + ') @ ' + new Date().toISOString() + ' -> results.length=' + (results ? results.length : 0));
                if (results && results.length && results[0].rawValue) handleSuccess(String(results[0].rawValue).trim());
            }).catch(function (err) {
                if (isDebug()) console.log('[KELAR][debug] detect() threw: ' + (err && err.message ? err.message : err));
            });
        } catch (e) {
            if (isDebug()) console.log('[KELAR][debug] detectTick caught: ' + (e && e.message ? e.message : e));
        }
    }

    function reapplyFocus() {
        if (!S || !S.stream) return;
        var t = S.stream.getVideoTracks()[0];
        if (t && t.readyState === 'live' && t.applyConstraints) t.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }).catch(function () {});
    }

    function setupTorch() {
        var t = S.stream && S.stream.getVideoTracks()[0];
        var caps = t && t.getCapabilities ? t.getCapabilities() : null;
        if (caps && caps.torch && S.opts.torchBtnEl) S.opts.torchBtnEl.style.display = 'inline-flex';
    }

    function setCameraReady() {
        if (!S || !S.active) return;
        S.ready = true;
        S.failed = false;
        hideErrorUI();
        if (S.frameEl) S.frameEl.style.display = 'block';
        if (S.lineEl) S.lineEl.style.display = 'block';
        setStatus('Kamera siap. Arahkan ke barcode.');
    }

    function hideScanOverlay() {
        if (!S) return;
        if (S.frameEl) S.frameEl.style.display = 'none';
        if (S.lineEl) S.lineEl.style.display = 'none';
    }

    function hideErrorUI() {
        var box = el('kelar-error');
        if (box) box.style.display = 'none';
    }

    function showCameraError(err) {
        if (!S || !S.active) return;
        S.ready = false;
        S.failed = true;
        hideScanOverlay();
        var msg = 'Kamera tidak dapat diakses. Gunakan tombol Coba Lagi atau ketik kode barcode secara manual.';
        var isPermission = err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError' || (err.message && /permission|denied/i.test(err.message)));
        if (isPermission) msg = 'Izin kamera ditolak. Aktifkan izin kamera di pengaturan browser, lalu coba lagi.';
        setStatus(msg);
        var msgEl = el('kelar-error-msg');
        if (msgEl) msgEl.textContent = msg;
        var box = el('kelar-error');
        if (box) box.style.display = 'flex';
        if (S.opts && S.opts.torchBtnEl) S.opts.torchBtnEl.style.display = 'none';
        console.log('[KELAR] camera failed: ' + (err && err.message ? err.message : String(err)));
    }

    function retryStart() {
        if (!S || !S.opts) return;
        var opts = S.opts;
        close();
        open(opts);
    }

    function buildUI() {
        var o = S.opts;
        o.containerEl = el(o.containerId);
        o.statusEl = el(o.statusId);
        o.tipsEl = el(o.tipsId);
        o.torchBtnEl = el(o.torchBtnId);
        o.torchIconEl = el(o.torchIconId);
        o.torchTextEl = el(o.torchTextId);
        o.controlsEl = el(o.controlsId);

        if (o.tipsEl) o.tipsEl.style.display = 'none';
        if (o.torchBtnEl) o.torchBtnEl.style.display = 'none';
        if (o.controlsEl) o.controlsEl.innerHTML = '';

        if (o.containerEl) {
            o.containerEl.style.position = 'relative';
            o.containerEl.style.overflow = 'hidden';
            o.containerEl.innerHTML = '';
            var frame = document.createElement('div');
            frame.className = 'kelar-frame';
            frame.style.display = 'none';
            S.frameEl = frame;
            o.containerEl.appendChild(frame);
            var line = document.createElement('div');
            line.className = 'kelar-scanline';
            line.id = 'kelar-scanline';
            line.style.display = 'none';
            S.lineEl = line;
            o.containerEl.appendChild(line);
            var flash = document.createElement('div');
            flash.className = 'kelar-flash';
            flash.id = 'kelar-flash';
            o.containerEl.appendChild(flash);
            var errBox = document.createElement('div');
            errBox.id = 'kelar-error';
            errBox.style.cssText = 'position:absolute;inset:0;z-index:8;display:none;align-items:center;justify-content:center;padding:16px;background:#0f172a;border-radius:12px;';
            var errInner = document.createElement('div');
            errInner.style.cssText = 'text-align:center;max-width:280px;';
            var errMsg = document.createElement('p');
            errMsg.id = 'kelar-error-msg';
            errMsg.style.cssText = 'color:#f8fafc;font-size:13px;line-height:1.5;margin:0 0 12px;';
            var errBtn = document.createElement('button');
            errBtn.type = 'button';
            errBtn.textContent = 'Coba Lagi';
            errBtn.id = 'kelar-retry-btn';
            errBtn.style.cssText = 'padding:8px 16px;border:none;border-radius:10px;background:#FA8F20;color:#0f172a;font-weight:700;font-size:13px;cursor:pointer;';
            errBtn.addEventListener('click', retryStart);
            errInner.appendChild(errMsg);
            errInner.appendChild(errBtn);
            errBox.appendChild(errInner);
            o.containerEl.appendChild(errBox);
        }
        buildControls();
    }

    function buildControls() {
        var o = S.opts;
        if (!o.controlsEl) return;
        var c = o.controlsEl;
        c.innerHTML = '';

        var manRow = document.createElement('div');
        manRow.style.cssText = 'display:flex;gap:8px;';
        var input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Ketik kode barcode manual...';
        input.id = 'kelar-manual-input';
        input.style.cssText = 'flex:1;padding:8px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;min-width:0;';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = 'Pakai';
        btn.style.cssText = 'padding:8px 14px;border:none;border-radius:10px;background:#0E8388;color:#fff;font-weight:600;font-size:13px;cursor:pointer;flex-shrink:0;';
        btn.addEventListener('click', manualSubmit);
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter') manualSubmit(); });
        manRow.appendChild(input);
        manRow.appendChild(btn);
        c.appendChild(manRow);
    }

    function manualSubmit() {
        var inp = el('kelar-manual-input');
        if (!inp || !S || !S.active) return;
        var kode = inp.value.trim();
        if (kode.length >= 3) {
            inp.value = '';
            handleSuccess(kode);
        } else {
            setStatus('Kode barcode minimal 3 karakter');
        }
    }

    function toggleTorch() {
        if (!S || !S.active) return;
        S.torchOn = !S.torchOn;
        var t = S.stream && S.stream.getVideoTracks()[0];
        if (t && t.applyConstraints) t.applyConstraints({ advanced: [{ torch: S.torchOn }] }).catch(function () { S.torchOn = !S.torchOn; });
        updateTorchUI();
    }

    function updateTorchUI() {
        var o = S.opts;
        if (!o.torchIconEl || !o.torchTextEl) return;
        o.torchTextEl.textContent = S.torchOn ? 'Mati' : 'Lampu';
        o.torchIconEl.innerHTML = S.torchOn
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>';
    }

    function startFeedback() {
        if (!S) return;
        S.timers.push(setInterval(rotateTip, 5000));
    }

    function rotateTip() {
        if (!S || !S.opts.tipsEl) return;
        S.tipIdx = (S.tipIdx + 1) % TIPS.length;
        S.opts.tipsEl.textContent = TIPS[S.tipIdx];
        S.opts.tipsEl.style.display = 'block';
    }

    function setStatus(msg) {
        if (S && S.opts && S.opts.statusEl) S.opts.statusEl.textContent = msg;
    }

    function handleSuccess(kode) {
        if (!S || !S.active) return;
        var now = Date.now();
        if (now - S.lastScan < 2000) return;
        S.lastScan = now;
        successFeedback();
        setStatus('Terbaca: ' + kode);
        console.log('[KELAR] barcode detected: ' + kode);
        try {
            window.dispatchEvent(new CustomEvent('pos:barcode-scanned', {
                detail: { kode: kode, sumber: 'kamera' }
            }));
        } catch (e) {}
        if (S.opts.onSuccess) S.opts.onSuccess(kode);
    }

    function successFeedback() {
        try { if (navigator.vibrate) navigator.vibrate(120); } catch (e) {}
        beep();
        var flash = el('kelar-flash');
        if (flash) {
            flash.classList.remove('flash-on');
            void flash.offsetWidth;
            flash.classList.add('flash-on');
            setTimeout(function () { if (flash) flash.classList.remove('flash-on'); }, 600);
        }
    }

    function beep() {
        try {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            if (!S.audio) S.audio = new AC();
            var ctx = S.audio;
            if (ctx.state === 'suspended') ctx.resume();
            var o = ctx.createOscillator();
            var g = ctx.createGain();
            o.type = 'square';
            o.frequency.value = 880;
            g.gain.setValueAtTime(0.06, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.12);
            o.connect(g);
            g.connect(ctx.destination);
            o.start();
            o.stop(ctx.currentTime + 0.12);
            o.onended = function () { o.disconnect(); g.disconnect(); };
        } catch (e) {}
    }

    function close() {
        var s = S;
        if (!s) return;
        S = null;
        s.active = false;
        try {
            s.timers.forEach(clearInterval);
            s.timers = [];
            if (s.frameEl) s.frameEl.style.display = 'none';
            if (s.lineEl) s.lineEl.style.display = 'none';
            if (s.stream) { s.stream.getTracks().forEach(function (t) { t.stop(); }); }
            if (s.opts) {
                if (s.opts.containerEl) s.opts.containerEl.innerHTML = '';
                if (s.opts.torchBtnEl) s.opts.torchBtnEl.style.display = 'none';
                if (s.opts.tipsEl) s.opts.tipsEl.style.display = 'none';
                if (s.opts.controlsEl) s.opts.controlsEl.innerHTML = '';
            }
        } catch (e) {}
    }

    return {
        open: open,
        close: close,
        toggleTorch: toggleTorch
    };
})();
