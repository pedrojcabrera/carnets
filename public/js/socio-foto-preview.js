(function () {
    const fileInput = document.getElementById('foto');
    const previewFrame = document.getElementById('foto-preview-frame');
    const previewImg = document.getElementById('foto-preview-img');
    const statusText = document.getElementById('foto-preview-status');

    if (!fileInput || !previewFrame || !previewImg) {
        return;
    }

    const defaultSrc = (previewImg.getAttribute('data-default-src') || '').trim();

    function setPreview(src, status) {
        previewImg.src = src;
        previewImg.classList.remove('d-none');
        previewFrame.classList.remove('d-none');

        if (statusText) {
            statusText.textContent = status;
        }
    }

    function resetPreview() {
        if (defaultSrc !== '') {
            setPreview(defaultSrc, 'Foto actual.');
            return;
        }

        previewImg.removeAttribute('src');
        previewImg.classList.add('d-none');
        previewFrame.classList.add('d-none');

        if (statusText) {
            statusText.textContent = 'Aun no hay foto seleccionada.';
        }
    }

    fileInput.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;

        if (!file) {
            resetPreview();
            return;
        }

        if (!file.type || !file.type.startsWith('image/')) {
            resetPreview();
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            const result = event && event.target ? event.target.result : null;

            if (typeof result !== 'string' || result.trim() === '') {
                resetPreview();
                return;
            }

            setPreview(result, 'Vista previa de la nueva foto.');
        };

        reader.onerror = function () {
            resetPreview();
        };

        reader.readAsDataURL(file);
    });

    if (defaultSrc !== '') {
        setPreview(defaultSrc, 'Foto actual.');
    } else {
        resetPreview();
    }
})();
