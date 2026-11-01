// Progressive enhancement only: pages work without JavaScript.
document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const open = document.getElementById(button.getAttribute('aria-controls')).classList.toggle('open');
        button.setAttribute('aria-expanded', String(open));
    });
});

document.querySelectorAll('[data-slider]').forEach((slider) => {
    const track = slider.querySelector('.slides');
    const step = (direction) => track.scrollBy({ left: direction * track.clientWidth * 0.9, behavior: 'smooth' });
    const rtl = getComputedStyle(slider).direction === 'rtl';

    slider.querySelector('[data-slide-prev]').hidden = false;
    slider.querySelector('[data-slide-next]').hidden = false;
    slider.querySelector('[data-slide-prev]').addEventListener('click', () => step(rtl ? 1 : -1));
    slider.querySelector('[data-slide-next]').addEventListener('click', () => step(rtl ? -1 : 1));
});
