// Admin behaviour (no inline scripts: the admin CSP only allows files from this origin).
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print]')) {
        window.print();
    }
});

window.addEventListener('DOMContentLoaded', () => {
    if (!window.Jodit) {
        return; // editor files not installed: fields stay plain textareas (content is still sanitized on the server)
    }

    document.querySelectorAll('textarea.rich').forEach((area) => {
        Jodit.make(area, {
            direction: 'rtl',
            language: 'fa',
            height: 380,
            toolbarAdaptive: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            askBeforePasteHTML: false,
            askBeforePasteFromWord: false,
            defaultActionOnPaste: 'insert_clear_html',
            uploader: { insertImageAsBase64URI: false },
            buttons: ['bold', 'italic', 'underline', 'strikethrough', '|', 'paragraph', 'ul', 'ol', 'align', '|', 'link', 'image', 'table', 'hr', '|', 'undo', 'redo', 'eraser', 'source'],
        });
    });
});
