// Admin behaviour (no inline scripts: the admin CSP only allows files from this origin).
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

// Hamburger drawer for the side menu on small screens (Esc, backdrop and a link click close it).
const navToggle = document.querySelector('[data-nav-toggle]');
if (navToggle) {
    const backdrop = document.querySelector('.nav-backdrop');
    const setNav = (open) => {
        document.body.classList.toggle('nav-open', open);
        navToggle.setAttribute('aria-expanded', String(open));
        backdrop.hidden = !open;
        if (!open) navToggle.focus();
    };
    navToggle.addEventListener('click', () => setNav(!document.body.classList.contains('nav-open')));
    backdrop.addEventListener('click', () => setNav(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && document.body.classList.contains('nav-open')) setNav(false); });
    document.querySelector('#sidebar').addEventListener('click', (e) => { if (e.target.closest('a')) document.body.classList.remove('nav-open'); });
}

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

// Media picker: one shared <dialog>; a field opens it, a click on a thumbnail (or a fresh upload) fills the field.
(() => {
    const dialog = document.getElementById('media-picker');
    if (!dialog) {
        return;
    }
    const grid = dialog.querySelector('.picker-grid');
    const search = dialog.querySelector('[name=q]');
    const more = dialog.querySelector('[data-picker-more]');
    const empty = dialog.querySelector('[data-picker-empty]');
    const error = dialog.querySelector('[data-picker-error]');
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    let field = null;
    let page = 1;
    let timer;

    const tile = (asset) => {
        const item = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.title = asset.name;
        if (asset.image) {
            const img = document.createElement('img');
            img.src = asset.url;
            img.alt = asset.alt || asset.name;
            img.loading = 'lazy';
            button.append(img);
        } else {
            button.textContent = asset.name;
        }
        button.addEventListener('click', () => choose(asset));
        item.append(button);
        return item;
    };

    const choose = (asset) => {
        const preview = field.querySelector('[data-media-preview]');
        field.querySelector('input[type=hidden]').value = asset.id;
        if (asset.image) {
            const img = document.createElement('img');
            img.src = asset.url;
            img.alt = asset.alt || '';
            preview.replaceChildren(img);
        } else {
            preview.textContent = asset.name;
        }
        field.querySelector('[data-media-clear]').hidden = false;
        dialog.close();
    };

    const load = async (reset) => {
        if (reset) {
            page = 1;
            grid.replaceChildren();
        }
        const params = new URLSearchParams({ kind: field.dataset.kind || '', q: search.value, page });
        const response = await fetch(`${dialog.dataset.list}?${params}`, { headers: { Accept: 'application/json' } });
        const body = await response.json();
        body.data.forEach((asset) => grid.append(tile(asset)));
        more.hidden = !body.next;
        empty.hidden = grid.children.length > 0;
    };

    const pane = (name) => {
        dialog.querySelectorAll('[data-picker-tab]').forEach((tab) => tab.setAttribute('aria-selected', tab.dataset.pickerTab === name));
        dialog.querySelectorAll('[data-picker-pane]').forEach((section) => { section.hidden = section.dataset.pickerPane !== name; });
    };

    document.addEventListener('click', (event) => {
        const open = event.target.closest('[data-media-open]');
        const clear = event.target.closest('[data-media-clear]');
        if (open) {
            field = open.closest('[data-media-field]');
            search.value = '';
            error.hidden = true;
            pane('library');
            dialog.showModal();
            load(true);
        } else if (clear) {
            const box = clear.closest('[data-media-field]');
            box.querySelector('input[type=hidden]').value = '';
            box.querySelector('[data-media-preview]').textContent = 'چیزی انتخاب نشده';
            clear.hidden = true;
        }
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-picker-close]')) {
            dialog.close();
        }
        const tab = event.target.closest('[data-picker-tab]');
        if (tab) {
            pane(tab.dataset.pickerTab);
        }
    });
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => load(true), 250);
    });
    more.addEventListener('click', () => { page += 1; load(false); });

    dialog.querySelector('[data-picker-upload]').addEventListener('click', async () => {
        const file = dialog.querySelector('[name=file]');
        const body = new FormData();
        body.append('file', file.files[0] || '');
        body.append('alt', dialog.querySelector('[name=alt]').value);
        error.hidden = true;
        const response = await fetch(dialog.dataset.upload, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' } });
        const result = await response.json();
        if (response.ok) {
            file.value = '';
            choose(result);
        } else {
            error.textContent = Object.values(result.errors || {}).flat()[0] || 'بارگذاری ناموفق بود.';
            error.hidden = false;
        }
    });
})();

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
