import { cart, money, toast, t } from './cart.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];


/** The item's picture: the owner's photo if there is one, else its line drawing. */
function picture(item) {
    return item.image
        ? `<img src="${item.image}" alt="" class="size-11 shrink-0 rounded-full object-cover ring-1 ring-gold/25">`
        : `<span class="grid size-11 shrink-0 place-items-center rounded-full bg-pearl text-gold ring-1 ring-gold/25">${item.icon}</span>`;
}

export function initBuilder() {
    const root = $('[data-builder]');
    if (!root) return;

    const X = window.XONCA;
    const items = X.items || {};
    const xonca = X.xonca;
    const editing = new URLSearchParams(location.search).get('edit');
    const existing = editing ? cart.get(editing) : null;

    const state = {
        mode: existing?.mode || 'ours',
        ribbon: existing?.ribbon || xonca.ribbonColor,
        fill: new Map((existing?.mode === 'custom' ? existing.fill : xonca.fill).map((f) => [f.id, f.qty])),
        cat: 'all',
    };
    if (state.mode === 'custom' && !xonca.fillAllowed) state.mode = 'ours';

    const currentFill = () => {
        if (state.mode === 'empty') return [];
        if (state.mode === 'ours') return xonca.fill;
        return [...state.fill].filter(([, q]) => q > 0).map(([id, qty]) => ({ id, qty }));
    };
    const fillTotal = () => currentFill().reduce((s, f) => s + (items[f.id]?.price || 0) * f.qty, 0);
    const xoncaPrice = () => (state.mode === 'empty' ? Math.max(0, xonca.price - xonca.emptyDiscount) : xonca.price);

    /* ---------- gallery */
    $$('[data-gallery-thumb]', root).forEach((t, i, all) => {
        t.toggleAttribute('data-on', i === 0);
        t.addEventListener('click', () => {
            all.forEach((o) => o.toggleAttribute('data-on', o === t));
            $$('[data-gallery-slide]', root).forEach((s) => s.classList.toggle('opacity-0', s.dataset.gallerySlide !== t.dataset.galleryThumb));
        });
    });

    /* ---------- ribbon */
    function renderRibbon() {
        $$('[data-ribbon]', root).forEach((b) => b.toggleAttribute('data-on', b.dataset.ribbon === state.ribbon));
        const name = t(`ribbon.${state.ribbon.toLowerCase()}`);
        $('[data-ribbon-name]', root).textContent = name.startsWith('ribbon.') ? '' : name;
    }
    $$('[data-ribbon]', root).forEach((b) =>
        b.addEventListener('click', () => {
            state.ribbon = b.dataset.ribbon;
            renderRibbon();
        }),
    );

    /* ---------- modes */
    function renderMode() {
        $$('[data-mode]', root).forEach((b) => b.toggleAttribute('data-on', b.dataset.mode === state.mode));
        $$('[data-pane]', root).forEach((p) => p.classList.toggle('hidden', p.dataset.pane !== state.mode));
    }
    $$('[data-mode]', root).forEach((b) =>
        b.addEventListener('click', () => {
            state.mode = b.dataset.mode;
            renderMode();
            renderGrid();
            renderSummary();
        }),
    );

    /* ---------- the customer's own filling */
    const grid = $('[data-item-grid]', root);
    function renderGrid() {
        if (!grid) return;
        $$('[data-cat]', root).forEach((b) => b.toggleAttribute('data-on', b.dataset.cat === String(state.cat)));
        const list = Object.values(items).filter((it) => state.cat === 'all' || String(it.category) === String(state.cat));
        grid.innerHTML = list.map((it) => {
            const q = state.fill.get(it.id) || 0;
            return `<li class="flex items-center gap-4 border-b border-ink/5 py-3">
                ${picture(it)}
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm ${q ? 'text-ink' : 'text-taupe'}">${it.name}</span>
                    <span class="caps !text-[9px] text-mist">${money(it.price)}</span>
                </span>
                <span class="flex items-center">
                    <button type="button" data-dec="${it.id}" class="grid size-8 place-items-center text-lg text-taupe transition hover:text-ink ${q ? '' : 'invisible'}" aria-label="Azalt">−</button>
                    <span class="w-6 text-center font-display text-lg ${q ? 'text-gold-deep' : 'text-mist/60'}">${q}</span>
                    <button type="button" data-inc="${it.id}" class="grid size-8 place-items-center text-lg text-taupe transition hover:text-ink" aria-label="Artır">+</button>
                </span>
            </li>`;
        }).join('');
    }
    grid?.addEventListener('click', (e) => {
        const b = e.target.closest('[data-inc],[data-dec]');
        if (!b) return;
        const id = Number(b.dataset.inc || b.dataset.dec);
        const it = items[id];
        const q = state.fill.get(id) || 0;
        if (b.dataset.inc) {
            if (q >= it.max) return toast(t('max', { name: it.name, n: it.max }));
            state.fill.set(id, q + 1);
        } else {
            q <= 1 ? state.fill.delete(id) : state.fill.set(id, q - 1);
        }
        renderGrid();
        renderSummary();
    });
    $$('[data-cat]', root).forEach((b) =>
        b.addEventListener('click', () => {
            state.cat = b.dataset.cat;
            renderGrid();
        }),
    );
    $('[data-fill-reset]', root)?.addEventListener('click', () => {
        state.fill = new Map(xonca.fill.map((f) => [f.id, f.qty]));
        renderGrid();
        renderSummary();
    });
    $('[data-fill-clear]', root)?.addEventListener('click', () => {
        state.fill.clear();
        renderGrid();
        renderSummary();
    });

    /* ---------- summary */
    function renderSummary() {
        $$('[data-sum-xonca]').forEach((el) => (el.textContent = money(xoncaPrice())));
        $$('[data-sum-fill]').forEach((el) => (el.textContent = state.mode === 'empty' ? money(0) : money(fillTotal())));
        $$('[data-sum-total]').forEach((el) => (el.textContent = money(xoncaPrice() + fillTotal())));
    }

    /* ---------- date */
    const dateInput = $('[data-date]', root);
    const note = $('[data-date-note]', root);
    const say = (text, tone) => {
        note.className = `mt-3 text-sm ${tone === 'ok' ? 'text-gold-deep' : tone === 'bad' ? 'text-wine' : 'text-mist'}`;
        note.textContent = text;
    };
    async function checkDate() {
        const d = dateInput.value;
        if (!d) return;
        try {
            localStorage.setItem('xonca.date', d);
        } catch {
            /* ignore */
        }
        if (d < X.earliest) return say(t('too_soon'), 'bad');
        if ((X.busy || []).includes(d)) return say(t('taken'), 'bad');
        say(t('checking'));
        const res = await fetch(`${X.routes.availability}?xonca=${xonca.id}&date=${d}`, { headers: { Accept: 'application/json' } }).then((r) => r.json()).catch(() => null);
        if (!res) return;
        res.free ? say(t('free'), 'ok') : say(t('taken'), 'bad');
    }
    if (dateInput) {
        let saved = '';
        try {
            saved = localStorage.getItem('xonca.date') || '';
        } catch {
            /* ignore */
        }
        if (saved >= X.earliest) dateInput.value = saved;
        dateInput.addEventListener('change', checkDate);
        if (dateInput.value) checkDate();
    }

    /* ---------- add to cart */
    $$('[data-add]').forEach((b) =>
        b.addEventListener('click', () => {
            if (state.mode === 'custom' && !currentFill().length) {
                toast(t('nothing_chosen'));
                return;
            }
            const line = {
                xonca_id: xonca.id,
                slug: xonca.slug,
                name: xonca.name,
                photo: xonca.photo,
                mode: state.mode,
                ribbon: state.ribbon,
                fill: state.mode === 'custom' ? currentFill() : [],
                shownFill: currentFill(),
                estimate: xoncaPrice() + fillTotal(),
            };
            if (existing) {
                cart.replace(existing.uid, line);
                location.href = X.routes.cart;
                return;
            }
            cart.add(line);
            toast(`${t('added', { name: xonca.name })} <a href="${X.routes.cart}" class="ml-5 border-b border-gold-soft pb-0.5 text-gold-soft">${t('to_cart')}</a>`, 5000);
        }),
    );
    if (existing) $$('[data-add]').forEach((b) => (b.textContent = t('save_changes')));

    renderRibbon();
    renderMode();
    renderGrid();
    renderSummary();
}
