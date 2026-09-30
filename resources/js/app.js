import { cart } from './cart.js';
import { initBuilder } from './builder.js';
import { initCartPage } from './cart-page.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

// Over the hero photograph the header is transparent; once the page moves
// it settles into ivory.
const header = $('[data-header]');
if (header?.dataset.overlay === '1') {
    const sync = () => header.toggleAttribute('data-solid', window.scrollY > 60);
    sync();
    addEventListener('scroll', sync, { passive: true });
}

// Full-screen menu on phones.
$$('[data-menu-toggle]').forEach((b) =>
    b.addEventListener('click', () => {
        const menu = $('[data-menu]');
        menu.classList.toggle('hidden');
        document.body.style.overflow = menu.classList.contains('hidden') ? '' : 'hidden';
    }),
);
$$('[data-menu] a').forEach((a) =>
    a.addEventListener('click', () => {
        $('[data-menu]').classList.add('hidden');
        document.body.style.overflow = '';
    }),
);

// Sections rise into place as they are reached.
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                io.unobserve(e.target);
            }
        }
    }, { rootMargin: '0px 0px -6% 0px' });
    $$('.reveal').forEach((el) => io.observe(el));
} else {
    $$('.reveal').forEach((el) => el.classList.add('in'));
}

// Testimonials turn over slowly by themselves.
const quotes = $('[data-quotes]');
if (quotes) {
    const items = $$('[data-quote]', quotes);
    const dots = $$('[data-quote-dot]', quotes);
    let at = 0;
    const show = (i) => {
        at = i;
        items.forEach((q, n) => {
            q.classList.toggle('opacity-0', n !== i);
            q.classList.toggle('pointer-events-none', n !== i);
        });
        dots.forEach((d, n) => d.toggleAttribute('data-on', n === i));
    };
    dots.forEach((d, n) => d.addEventListener('click', () => show(n)));
    show(0);
    if (items.length > 1) setInterval(() => show((at + 1) % items.length), 7000);
}

// Filters apply as soon as they change.
$$('form[data-autosubmit]').forEach((f) => f.addEventListener('change', () => f.submit()));

if (window.XONCA?.clearCart) cart.clear();
cart.renderCount();

initBuilder();
initCartPage();
