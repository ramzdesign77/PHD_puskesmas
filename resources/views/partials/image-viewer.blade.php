<div id="imageViewer" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
    role="dialog" aria-modal="true" aria-label="Image viewer" aria-hidden="true">
    <div class="absolute inset-x-0 top-0 flex items-center justify-center p-4">
        <div class="flex items-center gap-1 rounded-xl border border-white/10 bg-black/50 p-1.5 text-white shadow-lg backdrop-blur-md">
            <button id="imageViewerZoomOut" type="button" aria-label="Perkecil gambar" title="Zoom out"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-xl font-semibold transition-colors hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                −
            </button>
            <span id="imageViewerZoomLevel" class="min-w-14 px-1 text-center text-sm font-medium tabular-nums">100%</span>
            <button id="imageViewerZoomIn" type="button" aria-label="Perbesar gambar" title="Zoom in"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-xl font-semibold transition-colors hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                +
            </button>
            <span class="mx-1 h-6 w-px bg-white/20"></span>
            <button id="imageViewerReset" type="button" aria-label="Reset zoom" title="Reset ke 100%"
                class="rounded-lg px-3 py-2 text-xs font-semibold transition-colors hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                1:1
            </button>
        </div>
        <button id="imageViewerClose" type="button" aria-label="Tutup image viewer" title="Tutup"
            class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
            &times;
        </button>
    </div>

    <div id="imageViewerStage" class="flex h-full w-full items-center justify-center overflow-hidden">
        <img id="imageViewerImage" src="" alt="" draggable="false"
            class="max-h-[80vh] max-w-[90vw] select-none object-contain touch-none">
    </div>
    <p id="imageViewerCaption" class="absolute inset-x-16 bottom-4 text-center text-sm text-white/90"></p>
</div>

<script>
(() => {
    const viewer = document.getElementById('imageViewer');
    const image = document.getElementById('imageViewerImage');
    const stage = document.getElementById('imageViewerStage');
    const zoomLevel = document.getElementById('imageViewerZoomLevel');
    const caption = document.getElementById('imageViewerCaption');
    const closeButton = document.getElementById('imageViewerClose');
    let scale = 1;
    let offsetX = 0;
    let offsetY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let lastFocusedElement = null;
    let previousBodyOverflow = '';

    function updateImageTransform() {
        image.style.transform = `translate3d(${offsetX}px, ${offsetY}px, 0) scale(${scale})`;
        image.style.cursor = scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default';
        zoomLevel.textContent = `${Math.round(scale * 100)}%`;
    }

    function setZoom(nextScale) {
        scale = Math.min(4, Math.max(0.5, Math.round(nextScale * 100) / 100));
        if (scale <= 1) {
            offsetX = 0;
            offsetY = 0;
        }
        updateImageTransform();
    }

    function resetImageViewer() {
        scale = 1;
        offsetX = 0;
        offsetY = 0;
        isDragging = false;
        updateImageTransform();
    }

    window.openImageViewer = function(imageUrl, imageCaption) {
        if (!imageUrl) {
            return;
        }

        lastFocusedElement = document.activeElement;
        previousBodyOverflow = document.body.style.overflow;
        image.src = imageUrl;
        image.alt = imageCaption || 'Foto Bukti';
        caption.textContent = imageCaption || 'Foto Bukti';
        resetImageViewer();
        viewer.classList.remove('hidden');
        viewer.classList.add('flex');
        viewer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    };

    window.closeImageViewer = function() {
        viewer.classList.add('hidden');
        viewer.classList.remove('flex');
        viewer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousBodyOverflow;
        image.removeAttribute('src');
        if (lastFocusedElement instanceof HTMLElement) {
            lastFocusedElement.focus();
        }
    };

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-image-viewer-trigger]');
        if (trigger) {
            window.openImageViewer(trigger.dataset.imageUrl, trigger.dataset.imageCaption);
        }
    });

    viewer.addEventListener('click', event => {
        if (event.target === viewer || event.target === stage) {
            window.closeImageViewer();
        }
    });

    document.getElementById('imageViewerZoomIn').addEventListener('click', () => setZoom(scale + 0.25));
    document.getElementById('imageViewerZoomOut').addEventListener('click', () => setZoom(scale - 0.25));
    document.getElementById('imageViewerReset').addEventListener('click', resetImageViewer);
    closeButton.addEventListener('click', window.closeImageViewer);

    stage.addEventListener('wheel', event => {
        if (viewer.classList.contains('hidden')) {
            return;
        }
        event.preventDefault();
        setZoom(scale + (event.deltaY < 0 ? 0.25 : -0.25));
    }, { passive: false });

    image.addEventListener('pointerdown', event => {
        if (scale <= 1) {
            return;
        }
        isDragging = true;
        dragStartX = event.clientX - offsetX;
        dragStartY = event.clientY - offsetY;
        image.setPointerCapture(event.pointerId);
        event.preventDefault();
        updateImageTransform();
    });

    image.addEventListener('pointermove', event => {
        if (!isDragging) {
            return;
        }
        offsetX = event.clientX - dragStartX;
        offsetY = event.clientY - dragStartY;
        updateImageTransform();
    });

    function stopDragging() {
        isDragging = false;
        updateImageTransform();
    }

    image.addEventListener('pointerup', stopDragging);
    image.addEventListener('pointercancel', stopDragging);

    document.addEventListener('keydown', event => {
        if (viewer.classList.contains('hidden')) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.closeImageViewer();
        } else if (event.key === '+' || event.key === '=') {
            event.preventDefault();
            setZoom(scale + 0.25);
        } else if (event.key === '-') {
            event.preventDefault();
            setZoom(scale - 0.25);
        }
    });

    updateImageTransform();
})();
</script>
