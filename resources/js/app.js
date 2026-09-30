import { cart } from './cart.js';
import { initThumbs } from './thumbs.js';
import { initHero } from './hero.js';
import { initBuilder } from './builder.js';
import { initCartPage } from './cart-page.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

// Mobile menu.
$('[data-menu-toggle]')?.addEventListener('click', () => $('[data-menu]')?.classList.toggle('hidden'));
$$('[data-menu] a').forEach((a) => a.addEventListener('click', () => $('[data-menu]')?.classList.add('hidden')));

// Sections slide in as they come into view.
const io = new IntersectionObserver((entries) => {
    for (const e of entries) {
        if (e.isIntersecting) {
            e.target.classList.add('in');
            io.unobserve(e.target);
        }
    }
}, { rootMargin: '0px 0px -8% 0px' });
$$('.reveal').forEach((el) => io.observe(el));

// Filters apply as soon as they change.
$$('form[data-autosubmit]').forEach((f) => f.addEventListener('change', () => f.submit()));

if (window.XONCA?.clearCart) cart.clear();
cart.renderCount();

initHero();
initThumbs();
initBuilder();
initCartPage();
