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

// Page layout editor: drag a row by its grip, or use the arrows (also for keyboard users). Order is the DOM order of the rows.
document.querySelectorAll('[data-sortable]').forEach((list) => {
    let dragged = null;
    list.querySelectorAll('[data-move-box]').forEach((box) => { box.hidden = false; });

    list.addEventListener('dragstart', (event) => {
        dragged = event.target.closest('[data-row]');
        if (dragged) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', '');
            event.dataTransfer.setDragImage(dragged, 0, 0);
            dragged.classList.add('dragging');
        }
    });
    list.addEventListener('dragover', (event) => {
        if (!dragged) {
            return;
        }
        event.preventDefault();
        const next = [...list.querySelectorAll('[data-row]:not(.dragging)')].find((row) => {
            const box = row.getBoundingClientRect();
            return event.clientY < box.top + box.height / 2;
        });
        list.insertBefore(dragged, next || null);
    });
    list.addEventListener('dragend', () => {
        dragged?.classList.remove('dragging');
        dragged = null;
    });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-move]');
        const row = button?.closest('[data-row]');
        if (!row) {
            return;
        }
        const up = Number(button.dataset.move) < 0;
        const neighbour = up ? row.previousElementSibling : row.nextElementSibling;
        if (neighbour) {
            list.insertBefore(row, up ? neighbour : neighbour.nextElementSibling);
            button.focus();
        }
    });
});

// Form tabs: the bar is hidden in the markup so the form still works without JavaScript.
document.querySelectorAll('.form-tabs').forEach((bar) => {
    const tabs = [...bar.querySelectorAll('[role=tab]')];
    const show = (active) => tabs.forEach((tab) => {
        const on = tab === active;
        tab.setAttribute('aria-selected', on);
        document.getElementById(tab.getAttribute('aria-controls')).hidden = !on;
    });
    bar.hidden = false;
    tabs.forEach((tab) => tab.addEventListener('click', () => show(tab)));
    show(tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0]);
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

// Session lasts 20 minutes of inactivity; while someone is typing in a form, renew it at most every 5 minutes.
let lastPing = 0;
document.addEventListener('input', () => {
    if (!document.querySelector('form.form') || Date.now() - lastPing < 300000) {
        return;
    }
    lastPing = Date.now();
    fetch('/admin/ping', { credentials: 'same-origin' }).catch(() => {});
});
