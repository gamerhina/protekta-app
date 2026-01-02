import { getStroke } from 'perfect-freehand';

/**
 * SIGNATURE PAD CORE
 * Dioptimasi untuk bekerja dengan Event Delegation (pusat kendali di app.js).
 */
if (typeof window !== 'undefined' && !window.SIGNATURE_PAD_LOADED) {
    window.SIGNATURE_PAD_LOADED = true;

    const SIGNATURE_TYPES = ['p1', 'p2', 'pembahas', 'mahasiswa'];
    const STROKE_OPTIONS = {
        size: 4.5,
        thinning: 0.8,
        smoothing: 0.92,
        streamline: 0.88,
    };
    const MIN_SEGMENT_LENGTH = 3;

    // Simpan referensi instansi pad agar tombol di app.js bisa memanggilnya
    const activePads = {};

    window.openPdfUrl = (url) => {
        if (url) window.open(url, '_blank', 'noopener');
    };

    /**
     * Inisialisasi Utama
     */
    const initAllSignaturePads = () => {
        const wrappers = document.querySelectorAll('[class*="signature-pad-wrapper-"]');
        wrappers.forEach(wrapper => {
            const match = wrapper.className.match(/signature-pad-wrapper-([a-zA-Z0-9_-]+)/);
            if (match && match[1]) {
                initSignaturePadByType(match[1]);
            }
        });

        SIGNATURE_TYPES.forEach((type) => {
            initSignaturePadByType(type);
        });
    };

    // Dengarkan event dari MutationObserver di app.js (Mencegah console spam)
    window.addEventListener('content:updated', () => {
        initAllSignaturePads();
    });

    // Dengarkan event klik delegasi dari app.js
    window.addEventListener('signature:toggle', (e) => {
        const type = e.detail.type;
        if (activePads[type]) activePads[type].toggle();
    });

    window.addEventListener('signature:clear', (e) => {
        const type = e.detail.type;
        if (activePads[type]) activePads[type].clear();
    });

    function initSignaturePadByType(type) {
        const wrapper = document.querySelector(`.signature-pad-wrapper-${type}`);
        if (!wrapper || wrapper.dataset.signatureInitialized === 'true') return;

        const canvas = wrapper.querySelector(`.signature-canvas-${type}`);
        const input = wrapper.querySelector(`.signature-input-${type}`);
        const container = wrapper.querySelector(`.signature-pad-container-${type}`);

        if (!canvas || !input || !container) return;

        wrapper.dataset.signatureInitialized = 'true';
        prepareCanvas(canvas);
        const ctx = canvas.getContext('2d');
        const paths = [];
        let isDrawing = false;

        // Objek fungsi yang bisa dipanggil dari luar
        const padInstance = {
            toggle: () => {
                const isHidden = container.classList.contains('hidden') || container.style.display === 'none';
                if (isHidden) {
                    container.classList.remove('hidden');
                    container.style.display = 'block';
                    setTimeout(() => {
                        prepareCanvas(canvas);
                        redraw(ctx, paths);
                    }, 50);
                } else {
                    container.classList.add('hidden');
                    container.style.display = 'none';
                }
            },
            clear: () => {
                paths.length = 0;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                input.value = '';
            }
        };

        activePads[type] = padInstance;

        // Event drawing tetap inline di canvas karena sangat spesifik & performa sensitif
        canvas.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'touch') return;
            event.preventDefault();
            isDrawing = true;
            canvas.setPointerCapture?.(event.pointerId);
            const path = [];
            paths.push(path);
            addPointToPath(event, canvas, path);
            redraw(ctx, paths);
        });

        canvas.addEventListener('pointermove', (event) => {
            if (event.pointerType === 'touch' || !isDrawing) return;
            event.preventDefault();
            const currentPath = paths[paths.length - 1];
            addPointToPath(event, canvas, currentPath);
            redraw(ctx, paths);
        });

        const stopDrawing = (event) => {
            if (event.pointerType === 'touch' || !isDrawing) return;
            event.preventDefault();
            isDrawing = false;
            canvas.releasePointerCapture?.(event.pointerId);
            updateInput(canvas, input, paths);
        };

        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);
        canvas.addEventListener('pointercancel', stopDrawing);

        // Touch handlers
        canvas.addEventListener('touchstart', (event) => {
            event.preventDefault();
            if (event.touches.length > 1) return;
            isDrawing = true;
            const path = [];
            paths.push(path);
            addPointToPath(event.touches[0], canvas, path);
            redraw(ctx, paths);
        }, { passive: false });

        canvas.addEventListener('touchmove', (event) => {
            event.preventDefault();
            if (!isDrawing) return;
            addPointToPath(event.touches[0], canvas, paths[paths.length - 1]);
            redraw(ctx, paths);
        }, { passive: false });

        const stopTouch = (event) => {
            event.preventDefault();
            if (!isDrawing) return;
            isDrawing = false;
            updateInput(canvas, input, paths);
        };
        canvas.addEventListener('touchend', stopTouch);
        canvas.addEventListener('touchcancel', stopTouch);

        window.addEventListener('resize', () => {
            prepareCanvas(canvas);
            redraw(ctx, paths);
        });
    }

    function prepareCanvas(canvas) {
        const width = canvas.clientWidth || canvas.parentElement?.clientWidth || canvas.width;
        const height = canvas.clientHeight || canvas.parentElement?.clientHeight || canvas.height;
        if (width && height) {
            canvas.width = width;
            canvas.height = height;
        }
        canvas.style.touchAction = 'none';
    }

    function addPointToPath(event, canvas, path) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const x = (event.clientX - rect.left) * scaleX;
        const y = (event.clientY - rect.top) * scaleY;
        let pressure = event.pressure || event.force || 0.5;

        const timestamp = Date.now();
        const lastPoint = path[path.length - 1];
        if (lastPoint) {
            const dx = x - lastPoint[0], dy = y - lastPoint[1];
            const distance = Math.hypot(dx, dy);
            if (distance > MIN_SEGMENT_LENGTH) {
                const segments = Math.floor(distance / MIN_SEGMENT_LENGTH);
                for (let i = 1; i <= segments; i++) {
                    const t = i / segments;
                    path.push([lastPoint[0] + dx * t, lastPoint[1] + dy * t, pressure, timestamp]);
                }
                return;
            }
        }
        path.push([x, y, pressure, timestamp]);
    }

    function redraw(ctx, paths) {
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.fillStyle = '#0f172a';
        paths.forEach(path => {
            if (!path.length) return;
            if (path.length === 1) {
                ctx.beginPath();
                ctx.arc(path[0][0], path[0][1], 1, 0, Math.PI * 2);
                ctx.fill();
                return;
            }
            const stroke = getStroke(path, STROKE_OPTIONS);
            if (!stroke.length) return;
            ctx.beginPath();
            ctx.moveTo(stroke[0][0], stroke[0][1]);
            for (let i = 1; i < stroke.length; i++) ctx.lineTo(stroke[i][0], stroke[i][1]);
            ctx.closePath();
            ctx.fill();
        });
    }

    function updateInput(canvas, input, paths) {
        input.value = paths.length ? canvas.toDataURL('image/png') : '';
    }
}
