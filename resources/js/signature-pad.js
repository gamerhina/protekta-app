import { getStroke } from 'perfect-freehand';

// Prevent multiple registrations of event listeners if script is re-executed (e.g. AJAX navigation)
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

    window.openPdfUrl = (url) => {
        if (url) {
            window.open(url, '_blank', 'noopener');
        }
    };

    const initAllSignaturePads = () => {
        // Dynamic discovery of signature pads
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

    const setupInitialSignaturePads = () => {
        initAllSignaturePads();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('SignaturePad: DOMContentLoaded');
            setupInitialSignaturePads();
        });
    } else {
        console.log('SignaturePad: Already loaded');
        setupInitialSignaturePads();
    }

    window.addEventListener('page-loaded', () => {
        console.log('SignaturePad: page-loaded event');
        initAllSignaturePads();
    });

    window.addEventListener('app:init', () => {
        console.log('SignaturePad: app:init event');
        initAllSignaturePads();
    });

    function initSignaturePadByType(type) {
        const wrapper = document.querySelector(`.signature-pad-wrapper-${type}`);
        if (!wrapper) {
            return;
        }

        const canvas = wrapper.querySelector(`.signature-canvas-${type}`);
        const input = wrapper.querySelector(`.signature-input-${type}`);
        const toggleBtn = wrapper.querySelector(`.toggle-signature-btn-${type}`);
        const clearBtn = wrapper.querySelector(`.clear-signature-btn-${type}`);
        const container = wrapper.querySelector(`.signature-pad-container-${type}`);

        if (wrapper.dataset.signatureInitialized === 'true') {
            return;
        }

        if (!canvas || !input || !toggleBtn || !clearBtn || !container) {
            console.warn(`SignaturePad: Missing elements for type ${type}`, { canvas, input, toggleBtn, clearBtn, container });
            return;
        }

        console.log(`SignaturePad: Initializing type ${type}`);

        wrapper.dataset.signatureInitialized = 'true';

        prepareCanvas(canvas);
        const ctx = canvas.getContext('2d');
        const paths = [];
        let isDrawing = false;

        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setTimeout(toggleSignaturePad, 10);
        });

        function toggleSignaturePad() {
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
        }

        clearBtn.addEventListener('click', () => {
            paths.length = 0;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            input.value = '';
        });

        const syncedResize = () => {
            prepareCanvas(canvas);
            redraw(ctx, paths);
        };

        window.addEventListener('resize', syncedResize);

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
            if (event.pointerType === 'touch') return;
            if (!isDrawing) return;

            event.preventDefault();
            const currentPath = paths[paths.length - 1];
            addPointToPath(event, canvas, currentPath);
            redraw(ctx, paths);
        });

        const stopDrawing = (event) => {
            if (event.pointerType === 'touch') return;
            if (!isDrawing) return;

            event.preventDefault();
            isDrawing = false;
            canvas.releasePointerCapture?.(event.pointerId);
            updateInput(canvas, input, paths);
        };

        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);
        canvas.addEventListener('pointercancel', stopDrawing);

        canvas.addEventListener('touchstart', (event) => {
            event.preventDefault();
            if (event.touches.length > 1) return;
            
            isDrawing = true;
            const path = [];
            paths.push(path);
            
            const touch = event.touches[0];
            addPointToPath(touch, canvas, path);
            redraw(ctx, paths);
        }, { passive: false });

        canvas.addEventListener('touchmove', (event) => {
            event.preventDefault();
            if (!isDrawing) return;
            
            const touch = event.touches[0];
            const currentPath = paths[paths.length - 1];
            addPointToPath(touch, canvas, currentPath);
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

            const clientX = event.clientX;
            const clientY = event.clientY;
            let pressure = 0.5;

            if (typeof event.pressure === 'number' && event.pressure > 0) {
                pressure = event.pressure;
            } else if (typeof event.force === 'number' && event.force > 0) {
                pressure = event.force;
            }

            const x = (clientX - rect.left) * scaleX;
            const y = (clientY - rect.top) * scaleY;
            
            const timestamp = Date.now();
            const lastPoint = path[path.length - 1];

            if (lastPoint) {
                const dx = x - lastPoint[0];
                const dy = y - lastPoint[1];
                const distance = Math.hypot(dx, dy);

                if (distance > MIN_SEGMENT_LENGTH) {
                    const segments = Math.floor(distance / MIN_SEGMENT_LENGTH);
                    for (let i = 1; i <= segments; i += 1) {
                        const t = i / segments;
                        path.push([
                            lastPoint[0] + dx * t,
                            lastPoint[1] + dy * t,
                            pressure,
                            timestamp,
                        ]);
                    }
                    return;
                }
            }

            path.push([x, y, pressure, timestamp]);
        }

        function redraw(ctx, paths) {
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.fillStyle = '#0f172a';

            paths.forEach((path) => {
                drawPath(ctx, path);
            });
        }

        function drawPath(ctx, path) {
            if (!path.length) return;

            if (path.length === 1) {
                const [x, y] = path[0];
                ctx.beginPath();
                ctx.arc(x, y, 1, 0, Math.PI * 2);
                ctx.fill();
                return;
            }

            const stroke = getStroke(path, STROKE_OPTIONS);
            if (!stroke.length) return;

            ctx.beginPath();
            ctx.moveTo(stroke[0][0], stroke[0][1]);
            for (let i = 1; i < stroke.length; i += 1) {
                ctx.lineTo(stroke[i][0], stroke[i][1]);
            }
            ctx.closePath();
            ctx.fill();
        }

        function updateInput(canvas, input, paths) {
            if (!paths.length) {
                input.value = '';
                return;
            }
            input.value = canvas.toDataURL('image/png');
        }
    }
}
