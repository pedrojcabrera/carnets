(function () {
    function parseNumber(value, fallback) {
        const parsed = Number.parseInt(String(value ?? ''), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function initFaceEditor(editor) {
        const face = editor.dataset.face;
        const fileInput = editor.querySelector(`#fondo-input-${face}`);
        const previewImg = editor.querySelector('.preview-fondo');
        const previewWrap = editor.querySelector('.preview-wrapper');
        const previewEmpty = editor.querySelector('.preview-empty');
        const canvasWidthInput = editor.querySelector(`#fondo-width-${face}`);
        const canvasHeightInput = editor.querySelector(`#fondo-height-${face}`);
        const markers = Array.from(editor.querySelectorAll('.preview-marker'));
        const qrImageInput = editor.querySelector(`[name="${face}_qr_image"]`);
        const qrClearInput = editor.querySelector(`[name="${face}_qr_clear_image"]`);
        let qrTempObjectUrl = '';

        if (!fileInput || !previewImg || !previewWrap || !previewEmpty || !canvasWidthInput || !canvasHeightInput) {
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
                top: editor.querySelector(`[name="${face}_${field}_top"]`),
                left: editor.querySelector(`[name="${face}_${field}_left"]`),
                width: editor.querySelector(`[name="${face}_${field}_width"]`),
                height: editor.querySelector(`[name="${face}_${field}_height"]`),
                maxWidth: editor.querySelector(`[name="${face}_${field}_max_width"]`),
                align: editor.querySelector(`[name="${face}_${field}_align"]`),
                text: editor.querySelector(`[name="${face}_${field}_text"]`),
                visible: editor.querySelector(`[name="${face}_${field}_visible"]`),
            });
        });

        function setCanvasSize(width, height) {
            const nextWidth = Math.max(1, parseNumber(width, 360));
            const nextHeight = Math.max(1, parseNumber(height, 540));

            canvasWidthInput.value = String(nextWidth);
            canvasHeightInput.value = String(nextHeight);
            previewWrap.style.width = `${nextWidth}px`;
            previewWrap.style.height = `${nextHeight}px`;
        }

        function updatePreviewVisibility() {
            const hasBackground = Boolean(previewImg.getAttribute('src'));
            const hasVisibleMarker = Array.from(inputs.values()).some((fieldSet) => fieldSet.visible && fieldSet.visible.checked);

            previewWrap.style.display = hasBackground || hasVisibleMarker ? 'inline-block' : 'none';
            previewEmpty.style.display = hasBackground || hasVisibleMarker ? 'none' : 'block';
            previewImg.style.display = hasBackground ? 'block' : 'none';
        }

        function syncMarker(field) {
            const marker = markerByField.get(field);
            const fields = inputs.get(field);

            if (!marker || !fields) {
                return;
            }

            const isVisible = !fields.visible || fields.visible.checked;
            marker.classList.toggle('is-hidden', !isVisible);

            const top = parseNumber(fields.top && fields.top.value, 0);
            const left = parseNumber(fields.left && fields.left.value, 0);
            const width = fields.width ? parseNumber(fields.width.value, 80) : null;
            const height = fields.height ? parseNumber(fields.height.value, 80) : null;
            const maxWidth = fields.maxWidth ? parseNumber(fields.maxWidth.value, 220) : null;
            const align = fields.align && fields.align.value ? String(fields.align.value) : 'left';
            const isTextMarker = marker.dataset.markerType === 'text';

            marker.style.top = `${top}px`;
            marker.style.left = `${left}px`;
            marker.style.justifyContent = align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start';
            marker.style.textAlign = align === 'justify' ? 'justify' : align;

            if (width !== null) {
                marker.style.width = `${width}px`;
            }

            if (height !== null) {
                marker.style.height = `${height}px`;
            }

            if (maxWidth !== null) {
                marker.style.width = `${maxWidth}px`;
                marker.style.height = height !== null ? `${height}px` : '26px';
            }

            if (isTextMarker) {
                const previewTextNode = marker.querySelector('.preview-marker-text');
                const fallbackText = String(marker.dataset.previewDefault || field).trim();
                const rawText = fields.text && typeof fields.text.value === 'string' ? fields.text.value : '';
                const finalText = rawText.trim() !== '' ? rawText : fallbackText;

                if (previewTextNode) {
                    previewTextNode.textContent = finalText;
                }
            }

            updatePreviewVisibility();
        }

        function syncAllMarkers() {
            markerByField.forEach((_, field) => syncMarker(field));
        }

        function syncQrImageMarker() {
            const qrMarker = markerByField.get('qr');
            if (!qrMarker) {
                return;
            }

            let qrPreview = qrMarker.querySelector('.preview-marker-qr-image');
            if (!qrPreview) {
                qrPreview = document.createElement('img');
                qrPreview.className = 'preview-marker-qr-image';
                qrPreview.alt = 'Previsualización QR';
                qrMarker.insertBefore(qrPreview, qrMarker.firstChild || null);
            }

            let source = '';
            const clearChecked = Boolean(qrClearInput && qrClearInput.checked);
            const file = qrImageInput && qrImageInput.files && qrImageInput.files[0] ? qrImageInput.files[0] : null;

            if (!clearChecked && file && String(file.type || '').startsWith('image/')) {
                if (qrTempObjectUrl !== '') {
                    URL.revokeObjectURL(qrTempObjectUrl);
                }

                qrTempObjectUrl = URL.createObjectURL(file);
                source = qrTempObjectUrl;
            } else if (!clearChecked) {
                source = String(qrMarker.dataset.imageUrl || '').trim();
            }

            if (source !== '') {
                qrPreview.src = source;
                qrPreview.style.display = 'block';
                qrMarker.classList.add('has-inline-image');
            } else {
                qrPreview.removeAttribute('src');
                qrPreview.style.display = 'none';
                qrMarker.classList.remove('has-inline-image');
            }
        }

        setCanvasSize(canvasWidthInput.value, canvasHeightInput.value);
        syncAllMarkers();
        syncQrImageMarker();
        updatePreviewVisibility();

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
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            const fileUrl = URL.createObjectURL(file);
            previewImg.onload = function () {
                setCanvasSize(previewImg.naturalWidth || 360, previewImg.naturalHeight || 540);
                updatePreviewVisibility();
            };
            previewImg.src = fileUrl;
        });

        if (qrImageInput) {
            qrImageInput.addEventListener('change', function () {
                if (qrClearInput && qrImageInput.files && qrImageInput.files.length > 0) {
                    qrClearInput.checked = false;
                }

                syncQrImageMarker();
            });
        }

        if (qrClearInput) {
            qrClearInput.addEventListener('change', function () {
                syncQrImageMarker();
            });
        }

        let activeDrag = null;

        function startDrag(marker, event) {
            const field = marker.dataset.positionField;
            const fields = field ? inputs.get(field) : null;

            if (!field || !fields || !fields.top || !fields.left || marker.classList.contains('is-hidden')) {
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
    }

    document.querySelectorAll('.plantilla-face-editor[data-face]').forEach(initFaceEditor);
})();