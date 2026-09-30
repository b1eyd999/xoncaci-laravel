import { cart, money, toast } from './cart.js';
import { itemIcon } from './thumbs.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

const RIBBON_NAMES = {
    '#b3122e': 'Tünd qırmızı', '#d4af37': 'Qızılı', '#f2c6d0': 'Çəhrayı', '#ffffff': 'Ağ', '#1f6f5c': 'Zümrüd',
    '#6b1f7a': 'Bənövşəyi', '#8a1c2b': 'Bordo', '#c2185b': 'Moruq', '#7b1e3a': 'Albalı', '#a3162a': 'Nar rəngi',
};

const icons = {};

/**
 * The item's picture: its photo if the owner uploaded one, otherwise a
 * still of its 3D shape (a soft colour chip shows until that is drawn).
 */
function swatch(item) {
    if (item.image) return `<img src="${item.image}" alt="" class="size-11 shrink-0 rounded-xl object-cover">`;
    const c2 = item.color2 || item.color;
    const bg = `background:radial-gradient(circle at 50% 40%, #fff, #f6ecdf 70%)`;
    if (icons[item.id]) return `<img src="${icons[item.id]}" alt="" class="size-11 shrink-0 rounded-xl object-cover" style="${bg}">`;
    itemIcon(item).then((url) => {
        icons[item.id] = url;
        document.querySelectorAll(`[data-icon-for="${item.id}"]`).forEach((el) => el.replaceWith(Object.assign(document.createElement('img'), { src: url, alt: '', className: 'size-11 shrink-0 rounded-xl object-cover', style: bg })));
    });
    return `<span data-icon-for="${item.id}" class="relative grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl" style="background:linear-gradient(135deg, ${item.color}, ${c2})">
        <span class="absolute inset-0" style="background:radial-gradient(circle at 30% 25%, #ffffff88, transparent 55%)"></span></span>`;
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
        date: localStorage.getItem('xonca.date') || '',
    };
    if (state.mode === 'custom' && !xonca.fillAllowed) state.mode = 'ours';

    let viewer = null;
    const viewerEl = $('[data-viewer]', root);
    import('./xonca3d/viewer.js').then(({ createViewer }) => {
        viewer = createViewer(viewerEl, { autoRotate: true });
        viewer.load({ ...xonca, ribbonColor: state.ribbon }, currentFill(), items);
    });

    const currentFill = () => {
        if (state.mode === 'empty') return [];
        if (state.mode === 'ours') return xonca.fill;
        return [...state.fill].filter(([, q]) => q > 0).map(([id, qty]) => ({ id, qty }));
    };

    const fillTotal = () => currentFill().reduce((s, f) => s + (items[f.id]?.price || 0) * f.qty, 0);
    const xoncaPrice = () => (state.mode === 'empty' ? Math.max(0, xonca.price - xonca.emptyDiscount) : xonca.price);

    function sync3d() {
        viewer?.setFill(currentFill());
    }

    /* ---------- ribbon */
    function renderRibbon() {
        $$('[data-ribbon]', root).forEach((b) => b.toggleAttribute('data-on', b.dataset.ribbon === state.ribbon));
        $('[data-ribbon-name]', root).textContent = RIBBON_NAMES[state.ribbon.toLowerCase()] || state.ribbon;
    }
    $$('[data-ribbon]', root).forEach((b) =>
        b.addEventListener('click', () => {
            state.ribbon = b.dataset.ribbon;
            viewer?.setRibbon(state.ribbon);
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
            sync3d();
        }),
    );

    /* ---------- our filling */
    $('[data-ours-list]', root).innerHTML = xonca.fill.length
        ? xonca.fill.map((f) => {
              const it = items[f.id];
              if (!it) return '';
              return `<li class="flex items-center gap-3 p-3">${swatch(it)}
                <span class="min-w-0 flex-1"><span class="block text-sm font-bold">${it.name}</span><span class="text-xs text-ink/50">${money(it.price)} × ${f.qty}</span></span>
                <span class="text-sm font-bold">${money(it.price * f.qty)}</span></li>`;
          }).join('')
        : '<li class="p-4 text-sm text-ink/50">Bu xonça dolduruşsuz təklif olunur.</li>';

    /* ---------- custom filling */
    const grid = $('[data-item-grid]', root);
    function renderGrid() {
        if (!grid) return;
        $$('[data-cat]', root).forEach((b) => b.classList.toggle('chip-active', b.dataset.cat === String(state.cat)));
        const list = Object.values(items).filter((it) => state.cat === 'all' || String(it.category) === String(state.cat));
        grid.innerHTML = list.map((it) => {
            const q = state.fill.get(it.id) || 0;
            return `<div class="group relative flex items-center gap-2.5 rounded-2xl bg-white p-2.5 ring-1 transition ${q ? 'ring-2 ring-wine-600' : 'ring-wine-900/10 hover:ring-wine-600/40'}">
                <button type="button" data-inc="${it.id}" class="flex min-w-0 flex-1 items-center gap-2.5 text-left">
                    ${swatch(it)}
                    <span class="min-w-0"><span class="block truncate text-[13px] leading-tight font-bold">${it.name}</span><span class="text-xs text-ink/50">${money(it.price)}</span></span>
                </button>
                ${q ? `<div class="absolute -top-3 right-1.5 flex items-center overflow-hidden rounded-full bg-wine-600 text-white shadow-lg">
                    <button type="button" data-dec="${it.id}" class="grid size-7 place-items-center text-base font-bold hover:bg-wine-700" aria-label="Azalt">−</button>
                    <span class="min-w-5 text-center text-xs font-bold">${q}</span>
                    <button type="button" data-inc="${it.id}" class="grid size-7 place-items-center text-base font-bold hover:bg-wine-700" aria-label="Artır">+</button>
                </div>` : ''}
            </div>`;
        }).join('');
    }
    grid?.addEventListener('click', (e) => {
        const inc = e.target.closest('[data-inc]');
        const dec = e.target.closest('[data-dec]');
        if (!inc && !dec) return;
        const id = Number((inc || dec).dataset.inc || (inc || dec).dataset.dec);
        const it = items[id];
        const q = state.fill.get(id) || 0;
        if (inc) {
            if (q >= it.max) {
                toast(`«${it.name}» ən çox ${it.max} ədəd ola bilər`);
                return;
            }
            state.fill.set(id, q + 1);
        } else {
            q <= 1 ? state.fill.delete(id) : state.fill.set(id, q - 1);
        }
        renderGrid();
        renderSummary();
        sync3d();
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
        sync3d();
    });
    $('[data-fill-clear]', root)?.addEventListener('click', () => {
        state.fill.clear();
        renderGrid();
        renderSummary();
        sync3d();
    });

    /* ---------- summary */
    function renderSummary() {
        const f = fillTotal();
        $$('[data-sum-xonca]').forEach((el) => (el.textContent = money(xoncaPrice())));
        $$('[data-sum-fill]').forEach((el) => (el.textContent = state.mode === 'empty' ? '—' : money(f)));
        $$('[data-sum-total]').forEach((el) => (el.textContent = money(xoncaPrice() + f)));
    }

    /* ---------- date */
    const dateInput = $('[data-date]', root);
    const note = $('[data-date-note]', root);
    async function checkDate() {
        const d = dateInput.value;
        state.date = d;
        if (!d) return;
        try {
            localStorage.setItem('xonca.date', d);
        } catch {
            /* ignore */
        }
        if (d < X.earliest) {
            note.className = 'mt-2 text-sm font-semibold text-red-600';
            note.textContent = 'Bu tarixə çatdırmağa artıq vaxt qalmır — daha gec bir gün seçin.';
            return;
        }
        if ((X.busy || []).includes(d)) {
            note.className = 'mt-2 text-sm font-semibold text-red-600';
            note.textContent = '✗ Bu xonça həmin günə artıq tutulub. Başqa tarix və ya başqa xonça seçin.';
            return;
        }
        note.className = 'mt-2 text-sm text-ink/50';
        note.textContent = 'Yoxlanılır…';
        const res = await fetch(`${X.routes.availability}?xonca=${xonca.id}&date=${d}`, { headers: { Accept: 'application/json' } }).then((r) => r.json()).catch(() => null);
        if (!res) return;
        note.className = `mt-2 text-sm font-semibold ${res.free ? 'text-emerald-700' : 'text-red-600'}`;
        note.textContent = res.free ? `✓ Boşdur — bu günə ${res.free} ədəd var` : '✗ Bu xonça həmin günə artıq tutulub.';
    }
    if (dateInput) {
        if (state.date && state.date >= X.earliest) dateInput.value = state.date;
        dateInput.addEventListener('change', checkDate);
        if (dateInput.value) checkDate();
    }

    /* ---------- viewer tools */
    $('[data-toggle-cover]', root)?.addEventListener('click', (e) => {
        if (!viewer) return;
        viewer.showCover(!viewer.cover);
        e.currentTarget.textContent = viewer.cover ? 'Örtüyü gizlət' : 'Örtüyü göstər';
    });
    $('[data-reset-view]', root)?.addEventListener('click', () => viewer?.resetView());
    $('[data-fullscreen]', root)?.addEventListener('click', () => {
        const stage = viewerEl.parentElement;
        document.fullscreenElement ? document.exitFullscreen() : stage.requestFullscreen?.();
    });

    /* ---------- add to cart */
    $$('[data-add]').forEach((b) =>
        b.addEventListener('click', () => {
            if (state.mode === 'custom' && !currentFill().length) {
                toast('Xonçaya heç nə əlavə etməmisiniz — ya əşya seçin, ya da «Boş» rejimini seçin');
                return;
            }
            const line = {
                xonca_id: xonca.id,
                slug: xonca.slug,
                name: xonca.name,
                mode: state.mode,
                ribbon: state.ribbon,
                fill: state.mode === 'custom' ? currentFill() : [],
                cfg: { ...xonca, ribbonColor: state.ribbon, fill: currentFill() },
                estimate: xoncaPrice() + fillTotal(),
                deposit: xonca.deposit,
            };
            if (existing) {
                cart.replace(existing.uid, line);
                location.href = X.routes.cart;
                return;
            }
            cart.add(line);
            toast(`«${xonca.name}» səbətə əlavə olundu <a href="${X.routes.cart}" class="rounded-full bg-gold-400 px-3 py-1 text-wine-900">Səbətə keç →</a>`, 5000);
        }),
    );
    if (existing) $$('[data-add]').forEach((b) => (b.textContent = 'Dəyişikliyi saxla'));

    renderRibbon();
    renderMode();
    renderGrid();
    renderSummary();
}
