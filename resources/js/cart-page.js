import { cart, money, post } from './cart.js';
import { thumbnail } from './thumbs.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

const MODES = { ours: 'Hazır dolduruş', custom: 'Öz seçimim', empty: 'Boş — özüm dolduracağam' };

export function initCartPage() {
    const root = $('[data-cart-page]');
    if (!root) return;
    const X = window.XONCA;
    const items = X.items || {};
    const form = $('[data-cart-form]', root);
    const list = $('[data-cart-lines]', root);
    let quote = null;

    function render() {
        const lines = cart.lines();
        $('[data-cart-empty]', root).classList.toggle('hidden', !!lines.length);
        form.classList.toggle('hidden', !lines.length);
        form.classList.toggle('grid', !!lines.length);
        if (!lines.length) return;

        list.innerHTML = lines.map((l, i) => {
            const fill = (l.mode === 'custom' ? l.fill : l.cfg?.fill || []).map((f) => `${items[f.id]?.name || '?'} × ${f.qty}`).join(', ');
            const q = quote?.lines?.[i];
            const price = q ? q.price + q.fill_total : l.estimate;
            const warn = q && q.free !== null && q.free !== undefined
                ? (lines.slice(0, i + 1).filter((x) => x.xonca_id === l.xonca_id).length > q.free
                    ? '<div class="mt-2 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700">Bu tarixə boş deyil</div>'
                    : '<div class="mt-2 text-xs font-semibold text-emerald-700">✓ Tarixə boşdur</div>')
                : '';
            return `<li class="card flex gap-3 p-3 sm:gap-4 sm:p-4">
                <a href="/xonca/${l.slug}?edit=${l.uid}" class="stage relative size-24 shrink-0 overflow-hidden rounded-2xl sm:size-28">
                    <img data-line-thumb="${i}" alt="" class="absolute inset-0 size-full object-contain opacity-0 transition">
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <a href="/xonca/${l.slug}?edit=${l.uid}" class="font-display text-lg leading-tight font-semibold hover:text-wine-700">${l.name}</a>
                        <div class="font-bold whitespace-nowrap text-wine-700">${money(price)}</div>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-xs text-ink/55">
                        <span class="inline-block size-3 rounded-full ring-1 ring-black/10" style="background:${l.ribbon}"></span> ${MODES[l.mode]}
                    </div>
                    ${fill ? `<div class="mt-1 line-clamp-2 text-xs text-ink/50">${fill}</div>` : ''}
                    ${warn}
                    <div class="mt-2 flex gap-4 text-xs font-bold">
                        <a href="/xonca/${l.slug}?edit=${l.uid}" class="text-wine-600 hover:underline">Dəyiş</a>
                        <button type="button" data-dup="${l.uid}" class="text-ink/60 hover:underline">+ Eynisindən</button>
                        <button type="button" data-del="${l.uid}" class="text-ink/40 hover:text-red-600">Sil</button>
                    </div>
                </div>
            </li>`;
        }).join('');

        lines.forEach((l, i) => {
            if (!l.cfg) return;
            thumbnail(l.cfg, items).then((url) => {
                const img = $(`[data-line-thumb="${i}"]`, list);
                if (img) {
                    img.src = url;
                    img.classList.remove('opacity-0');
                }
            });
        });
    }

    list.addEventListener('click', (e) => {
        const del = e.target.closest('[data-del]');
        const dup = e.target.closest('[data-dup]');
        if (del) cart.remove(del.dataset.del);
        if (dup) cart.duplicate(dup.dataset.dup);
        if (del || dup) {
            quote = null;
            render();
            refresh();
        }
    });

    const val = (n) => form.elements[n]?.value;
    const deliveryType = () => form.querySelector('[name=delivery_type]:checked')?.value || 'delivery';

    async function refresh() {
        if (!cart.lines().length) return;
        const { ok, data } = await post(X.routes.quote, { cart: cart.payload(), delivery_type: deliveryType(), event_date: val('event_date') || null });
        if (!ok) return;
        quote = data;
        for (const k of ['xonca_total', 'fill_total', 'delivery_fee', 'total', 'deposit_total']) {
            const el = $(`[data-q="${k}"]`, form);
            if (el) el.textContent = k === 'delivery_fee' && data[k] === 0 ? (deliveryType() === 'pickup' ? '—' : 'Pulsuz') : money(data[k]);
        }
        const free = $('[data-free-note]', form);
        const sub = data.xonca_total + data.fill_total;
        if (deliveryType() === 'delivery' && X.freeDeliveryFrom > 0) {
            free.classList.remove('hidden');
            free.textContent = sub >= X.freeDeliveryFrom
                ? '🎉 Çatdırılma pulsuzdur!'
                : `Daha ${money(X.freeDeliveryFrom - sub)} — çatdırılma pulsuz olacaq`;
        } else {
            free.classList.add('hidden');
        }
        const availNote = $('[data-avail-note]', form);
        if (val('event_date')) {
            const lines = cart.lines();
            const bad = data.lines.some((q, i) => q.free !== null && lines.slice(0, i + 1).filter((x) => x.xonca_id === lines[i].xonca_id).length > q.free);
            availNote.classList.remove('hidden');
            availNote.className = `mt-3 text-sm font-semibold ${bad ? 'text-red-600' : 'text-emerald-700'}`;
            availNote.textContent = bad ? 'Bəzi xonçalar bu tarixə boş deyil — onları dəyişin və ya başqa tarix seçin.' : '✓ Bütün xonçalar bu tarixə boşdur';
        }
        render();
    }

    // Delivery fields only matter for delivery.
    function toggleDelivery() {
        const d = deliveryType() === 'delivery';
        $$('[data-delivery-only]', form).forEach((el) => el.classList.toggle('hidden', !d));
    }
    form.addEventListener('change', (e) => {
        if (e.target.name === 'delivery_type') toggleDelivery();
        if (['delivery_type', 'event_date'].includes(e.target.name)) refresh();
        if (e.target.name === 'event_date') {
            try {
                localStorage.setItem('xonca.date', e.target.value);
            } catch {
                /* ignore */
            }
        }
    });

    // Remember who is ordering, so a second order is quicker.
    const remembered = ['customer_name', 'phone', 'district', 'address'];
    try {
        const saved = JSON.parse(localStorage.getItem('xonca.customer') || '{}');
        remembered.forEach((k) => saved[k] && form.elements[k] && (form.elements[k].value = saved[k]));
        const d = localStorage.getItem('xonca.date');
        if (d && d >= X.earliest) form.elements.event_date.value = d;
    } catch {
        /* ignore */
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const box = $('[data-errors]', form);
        const btn = form.querySelector('[type=submit]');
        box.classList.add('hidden');
        $$('.input', form).forEach((i) => i.classList.remove('!border-red-400'));
        btn.disabled = true;
        btn.textContent = 'Göndərilir…';
        const body = Object.fromEntries(new FormData(form).entries());
        body.cart = cart.payload();
        const { ok, status, data } = await post(X.routes.store, body);
        if (ok && data.redirect) {
            try {
                localStorage.setItem('xonca.customer', JSON.stringify(Object.fromEntries(remembered.map((k) => [k, body[k] || '']))));
            } catch {
                /* ignore */
            }
            cart.clear();
            location.href = data.redirect;
            return;
        }
        btn.disabled = false;
        btn.textContent = 'Sifarişi göndər';
        const errors = data.errors || { _: [status === 429 ? 'Çox tez-tez cəhd edirsiniz, bir dəqiqə gözləyin.' : 'Xəta baş verdi, yenidən cəhd edin.'] };
        Object.keys(errors).forEach((k) => form.elements[k]?.classList?.add('!border-red-400'));
        box.innerHTML = Object.values(errors).flat().map((m) => `<div>• ${m}</div>`).join('');
        box.classList.remove('hidden');
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    toggleDelivery();
    render();
    refresh();
}
