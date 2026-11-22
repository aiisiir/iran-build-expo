// Progressive enhancement only: every page works without JavaScript.
const root = document.documentElement;
root.classList.add('js');
const reduced = matchMedia('(prefers-reduced-motion: reduce)');

// Mobile navigation: toggle, Esc / outside click / link click closes (Esc returns focus).
document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
    const nav = document.getElementById(button.getAttribute('aria-controls'));
    const set = (open) => { nav.classList.toggle('open', open); button.setAttribute('aria-expanded', String(open)); };
    button.addEventListener('click', () => set(!nav.classList.contains('open')));
    nav.addEventListener('click', (e) => { if (e.target.closest('a')) set(false); });
    document.addEventListener('click', (e) => { if (!e.target.closest('.site-header')) set(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && nav.classList.contains('open')) { set(false); button.focus(); } });
    matchMedia('(min-width: 741px)').addEventListener('change', () => set(false));
});

// Hero carousel: autoplay only when motion is allowed; stops while hovered, focused or in a hidden tab; prev/next and arrow keys always work.
document.querySelectorAll('[data-hero]').forEach((hero) => {
    const slides = [...hero.querySelectorAll('.hero-slide')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (slides.length < 2 || !controls) return;
    const list = hero.querySelector('.hero-slides');
    let index = 0; let timer = null;

    const show = (i) => {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => { s.classList.toggle('is-active', n === index); s.toggleAttribute('inert', n !== index); s.setAttribute('aria-hidden', String(n !== index)); });
    };
    const stop = () => { clearInterval(timer); timer = null; list.setAttribute('aria-live', 'polite'); };
    const start = () => { if (reduced.matches || timer) return; list.setAttribute('aria-live', 'off'); timer = setInterval(() => show(index + 1), 7000); };

    controls.hidden = false;
    show(0); start();
    hero.querySelector('[data-hero-prev]').addEventListener('click', () => { show(index - 1); });
    hero.querySelector('[data-hero-next]').addEventListener('click', () => { show(index + 1); });
    hero.addEventListener('mouseenter', stop); hero.addEventListener('mouseleave', start);
    hero.addEventListener('focusin', stop); hero.addEventListener('focusout', start);
    hero.addEventListener('keydown', (e) => {
        const rtl = getComputedStyle(hero).direction === 'rtl';
        if (e.key === 'ArrowRight') show(index + (rtl ? -1 : 1));
        if (e.key === 'ArrowLeft') show(index + (rtl ? 1 : -1));
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
});

// Home news carousel: desktop rotates the cards in place, mobile scrolls a snap track to the chosen card.
document.querySelectorAll('[data-news]').forEach((root) => {
    const track = root.querySelector('.news-track');
    const cards = [...track.children];
    const dots = [...(root.parentElement.querySelectorAll('.carousel-dots button'))];
    const status = root.parentElement.querySelector('.news-update');
    const mobile = matchMedia('(max-width: 740px)');
    let current = 0;
    const mark = () => dots.forEach((dot, i) => (i === current ? dot.setAttribute('aria-current', 'true') : dot.removeAttribute('aria-current')));
    const go = (next) => {
        current = (next + cards.length) % cards.length;
        mark();
        if (mobile.matches) {
            const card = cards[current].getBoundingClientRect(); const box = track.getBoundingClientRect();
            track.scrollBy({ left: card.left - box.left - (box.width - card.width) / 2, behavior: reduced.matches ? 'auto' : 'smooth' });
        } else {
            cards.forEach((_, i) => track.append(cards[(current + i) % cards.length]));
            if (!reduced.matches) track.animate([{ opacity: 0.45 }, { opacity: 1 }], { duration: 230 });
        }
        if (status) status.textContent = cards[current].querySelector('h3').textContent;
    };
    root.querySelectorAll('[data-carousel]').forEach((b) => b.addEventListener('click', () => go(current + (b.dataset.carousel === 'next' ? 1 : -1))));
    dots.forEach((dot) => dot.addEventListener('click', () => go(Number(dot.dataset.slide))));
    mobile.addEventListener('change', () => { cards.forEach((c) => track.append(c)); current = 0; mark(); });
});

document.querySelectorAll('[data-slider]').forEach((slider) => {
    const track = slider.querySelector('.slides');
    const step = (direction) => track.scrollBy({ left: direction * track.clientWidth * 0.9, behavior: reduced.matches ? 'auto' : 'smooth' });
    const rtl = getComputedStyle(slider).direction === 'rtl';

    slider.querySelector('[data-slide-prev]').hidden = false;
    slider.querySelector('[data-slide-next]').hidden = false;
    slider.querySelector('[data-slide-prev]').addEventListener('click', () => step(rtl ? 1 : -1));
    slider.querySelector('[data-slide-next]').addEventListener('click', () => step(rtl ? -1 : 1));
});

// Gallery viewer: a modal <dialog> that grows out of the clicked picture; arrows, Esc, swipe-free and keyboard friendly.
document.querySelectorAll('[data-lightbox]').forEach((grid) => {
    const box = document.querySelector('dialog.lightbox');
    if (!box || typeof box.showModal !== 'function') return; // the links still open the full image
    const links = [...grid.querySelectorAll('a')];
    const img = box.querySelector('img');
    const caption = box.querySelector('figcaption');
    const count = box.querySelector('.lb-count');
    let current = 0; let opener = null;

    const show = (index) => {
        current = (index + links.length) % links.length;
        const link = links[current];
        img.src = link.href;
        img.alt = link.querySelector('img')?.alt || '';
        caption.textContent = link.dataset.caption || '';
        count.textContent = `${current + 1} / ${links.length}`;
    };
    const open = (index, from) => {
        opener = from; box.hidden = false; show(index); box.showModal();
        requestAnimationFrame(() => box.classList.add('is-open'));
    };
    const close = () => {
        box.classList.remove('is-open');
        const done = () => { box.close(); box.hidden = true; opener?.focus(); };
        reduced.matches ? done() : setTimeout(done, 240);
    };

    links.forEach((link, i) => link.addEventListener('click', (e) => { e.preventDefault(); open(i, link); }));
    box.querySelector('[data-lb-prev]').addEventListener('click', () => show(current - 1));
    box.querySelector('[data-lb-next]').addEventListener('click', () => show(current + 1));
    box.querySelector('[data-lb-close]').addEventListener('click', close);
    box.addEventListener('click', (e) => { if (e.target === box) close(); });
    box.addEventListener('cancel', (e) => { e.preventDefault(); close(); });
    box.addEventListener('keydown', (e) => {
        const rtl = getComputedStyle(box).direction === 'rtl';
        if (e.key === 'ArrowLeft') show(current + (rtl ? 1 : -1));
        if (e.key === 'ArrowRight') show(current + (rtl ? -1 : 1));
    });
});
