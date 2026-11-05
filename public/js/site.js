// Progressive enhancement only: every page works without JavaScript.
const root = document.documentElement;
root.classList.add('js');
const reduced = matchMedia('(prefers-reduced-motion: reduce)');

// Mobile navigation: toggle, Esc closes and returns focus.
document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
    const nav = document.getElementById(button.getAttribute('aria-controls'));
    const set = (open) => { nav.classList.toggle('open', open); button.setAttribute('aria-expanded', String(open)); };
    button.addEventListener('click', () => set(!nav.classList.contains('open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && nav.classList.contains('open')) { set(false); button.focus(); } });
});

// Header: only needed where scroll-driven animations are missing; one observer, no scroll listener.
const sentinel = document.querySelector('[data-header-sentinel]');
const bar = document.querySelector('.header-bar');
if (sentinel && bar && 'IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => bar.classList.toggle('is-scrolled', !entry.isIntersecting)).observe(sentinel);
}

// Hero carousel: autoplay only when motion is allowed; always pausable; pauses while focused or hovered.
document.querySelectorAll('[data-hero]').forEach((hero) => {
    const slides = [...hero.querySelectorAll('.hero-slide')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (slides.length < 2 || !controls) return;
    const toggle = hero.querySelector('[data-hero-toggle]');
    const icon = hero.querySelector('[data-hero-icon]');
    const counter = hero.querySelector('[data-hero-current]');
    const list = hero.querySelector('.hero-slides');
    let index = 0; let timer = null; let userPaused = reduced.matches;

    const show = (i) => {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => { s.classList.toggle('is-active', n === index); s.toggleAttribute('inert', n !== index); s.setAttribute('aria-hidden', String(n !== index)); });
        counter.textContent = new Intl.NumberFormat(document.documentElement.lang).format(index + 1);
    };
    const stop = () => { clearInterval(timer); timer = null; list.setAttribute('aria-live', 'polite'); };
    const start = () => { if (userPaused || timer) return; list.setAttribute('aria-live', 'off'); timer = setInterval(() => show(index + 1), 7000); };
    const render = () => {
        icon.textContent = userPaused ? '▶' : '❚❚';
        toggle.setAttribute('aria-label', toggle.dataset[userPaused ? 'labelPlay' : 'labelPause']);
    };

    controls.hidden = false;
    show(0); render(); start();
    hero.querySelector('[data-hero-prev]').addEventListener('click', () => { show(index - 1); });
    hero.querySelector('[data-hero-next]').addEventListener('click', () => { show(index + 1); });
    toggle.addEventListener('click', () => { userPaused = !userPaused; userPaused ? stop() : start(); render(); });
    hero.addEventListener('mouseenter', stop); hero.addEventListener('mouseleave', start);
    hero.addEventListener('focusin', stop); hero.addEventListener('focusout', start);
    hero.addEventListener('keydown', (e) => {
        const rtl = getComputedStyle(hero).direction === 'rtl';
        if (e.key === 'ArrowRight') show(index + (rtl ? -1 : 1));
        if (e.key === 'ArrowLeft') show(index + (rtl ? 1 : -1));
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
});

// Background video: plays only while visible, never with reduced motion or data saver, always pausable.
document.querySelectorAll('[data-atmosphere]').forEach((section) => {
    const video = section.querySelector('[data-atmosphere-video]');
    const button = section.querySelector('[data-atmosphere-toggle]');
    if (!video || !button || reduced.matches || navigator.connection?.saveData) return;
    let paused = false; let visible = false;
    const sync = () => {
        const play = visible && !paused && !document.hidden;
        play ? video.play().catch(() => {}) : video.pause();
        button.textContent = button.dataset[paused ? 'labelPlay' : 'labelPause'];
    };
    button.hidden = false;
    button.addEventListener('click', () => { paused = !paused; sync(); });
    new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; sync(); }, { threshold: 0.15 }).observe(section);
    document.addEventListener('visibilitychange', sync);
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
