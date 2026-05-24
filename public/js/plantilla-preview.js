(function () {
    const fileInput = document.getElementById('fondo-input');
    const previewImg = document.getElementById('preview-fondo');
    const previewWrap = document.getElementById('preview-wrapper');
    const previewEmpty = document.getElementById('preview-empty');
    const canvasWidthInput = document.getElementById('fondo-width');
    const canvasHeightInput = document.getElementById('fondo-height');
    const markers = Array.from(document.querySelectorAll('.preview-marker'));

    if (!fileInput || !previewImg || !previewWrap || !previewEmpty) {
        return;
    }

    const inputs = new Map();
    const markerByField = new Map();

    markers.forEach((marker) => {
        const field = marker.dataset.positionField;
        if (!field) {
            return;
        }

        markerByField.set(field, marker);
        inputs.set(field, {
            top: document.querySelector(`[name="${field}_top"]`),
            left: document.querySelector(`[name="${field}_left"]`),
            width: document.querySelector(`[name="${field}_width"]`),
            height: document.querySelector(`[name="${field}_height"]`),
            maxWidth: document.querySelector(`[name="${field}_max_width"]`),
            align: document.querySelector(`[name="${field}_align"]`),
        });
    });

    function parseNumber(value, fallback = 0) {
        const parsed = Number.parseInt(String(value ?? ''), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function setCanvasSize(width, height) {
        const nextWidth = Math.max(1, parseNumber(width, 360));
        const nextHeight = Math.max(1, parseNumber(height, 540));

        if (canvasWidthInput) {
            canvasWidthInput.value = String(nextWidth);
        }

        if (canvasHeightInput) {
            canvasHeightInput.value = String(nextHeight);
        }

        previewWrap.style.width = `${nextWidth}px`;
        previewWrap.style.height = `${nextHeight}px`;
    }

    function syncMarker(field) {
        const marker = markerByField.get(field);
        const fields = inputs.get(field);

        if (!marker || !fields) {
            return;
        }

        const top = parseNumber(fields.top && fields.top.value, 0);
        const left = parseNumber(fields.left && fields.left.value, 0);
        const width = fields.width ? parseNumber(fields.width.value, 80) : null;
        const height = fields.height ? parseNumber(fields.height.value, 80) : null;
        const maxWidth = fields.maxWidth ? parseNumber(fields.maxWidth.value, 220) : null;
        const align = fields.align && fields.align.value ? String(fields.align.value) : 'left';

        marker.style.top = `${top}px`;
        marker.style.left = `${left}px`;
        marker.style.justifyContent = align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start';

        if (width !== null) {
            marker.style.width = `${width}px`;
        }

        if (height !== null) {
            marker.style.height = `${height}px`;
        }

        if (maxWidth !== null) {
            marker.style.width = `${maxWidth}px`;
        }
    }

    function syncAllMarkers() {
        markerByField.forEach((_, field) => syncMarker(field));
    }

    syncAllMarkers();
    setCanvasSize(
        canvasWidthInput ? canvasWidthInput.value : 360,
        canvasHeightInput ? canvasHeightInput.value : 540
    );

    inputs.forEach((fieldSet, field) => {
        Object.values(fieldSet).forEach((input) => {
            if (!input) {
                return;
            }

            input.addEventListener('input', () => syncMarker(field));
            input.addEventListener('change', () => syncMarker(field));
        });
    });

    fileInput.addEventListener('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            return;
        }

        const fileUrl = URL.createObjectURL(file);
        previewImg.onload = function () {
            setCanvasSize(previewImg.naturalWidth || 360, previewImg.naturalHeight || 540);
        };
        previewImg.src = fileUrl;
        previewWrap.style.display = 'inline-block';
        previewEmpty.style.display = 'none';
    });

    let activeDrag = null;

    function startDrag(marker, event) {
        const field = marker.dataset.positionField;
        const fields = field ? inputs.get(field) : null;

        if (!field || !fields || !fields.top || !fields.left) {
            return;
        }

        event.preventDefault();

        const wrapperRect = previewWrap.getBoundingClientRect();
        const markerRect = marker.getBoundingClientRect();

        activeDrag = {
            marker,
            fields,
            offsetX: event.clientX - markerRect.left,
            offsetY: event.clientY - markerRect.top,
            wrapperRect,
        };

        marker.style.zIndex = '999';

        if (typeof marker.setPointerCapture === 'function' && event.pointerId !== undefined) {
            try {
                marker.setPointerCapture(event.pointerId);
            } catch (error) {
                // Ignorar si el navegador rechaza la captura.
            }
        }
    }

    function moveDrag(event) {
        if (!activeDrag) {
            return;
        }

        const { marker, fields, offsetX, offsetY, wrapperRect } = activeDrag;
        const markerWidth = marker.offsetWidth || 80;
        const markerHeight = marker.offsetHeight || 24;

        const x = event.clientX - wrapperRect.left - offsetX;
        const y = event.clientY - wrapperRect.top - offsetY;

        const maxLeft = Math.max(0, wrapperRect.width - markerWidth);
        const maxTop = Math.max(0, wrapperRect.height - markerHeight);

        const nextLeft = Math.min(Math.max(0, Math.round(x)), maxLeft);
        const nextTop = Math.min(Math.max(0, Math.round(y)), maxTop);

        fields.left.value = String(nextLeft);
        fields.top.value = String(nextTop);

        marker.style.left = `${nextLeft}px`;
        marker.style.top = `${nextTop}px`;
    }

    function endDrag() {
        if (!activeDrag) {
            return;
        }

        activeDrag.marker.style.zIndex = '10';
        activeDrag = null;
    }

    markers.forEach((marker) => {
        marker.addEventListener('pointerdown', (event) => startDrag(marker, event));
        marker.addEventListener('mousedown', (event) => startDrag(marker, event));
    });

    document.addEventListener('pointermove', moveDrag);
    document.addEventListener('mousemove', moveDrag);
    document.addEventListener('pointerup', endDrag);
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('pointercancel', endDrag);
    document.addEventListener('mouseleave', endDrag);
})();
