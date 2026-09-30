import { cart, money, post } from './cart.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

const MODES = { ours: 'Ustalarımızın dolduruşu', custom: 'Öz seçimim', empty: 'Boş xonça' };

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
            const shown = l.mode === 'custom' ? l.fill : l.shownFill || [];
            const fill = shown.map((f) => `${items[f.id]?.name || '?'} × ${f.qty}`).join(', ');
            const q = quote?.lines?.[i];
            const price = q ? q.price + q.fill_total : l.estimate;
            const taken = q && q.free !== null && q.free !== undefined && lines.slice(0, i + 1).filter((x) => x.xonca_id === l.xonca_id).length > q.free;
            const warn = q && q.free !== null && q.free !== undefined
                ? `<div class="caps mt-3 !text-[9px] ${taken ? 'text-wine' : 'text-gold-deep'}">${taken ? 'Bu tarixə boş deyil' : 'Tarixə boşdur'}</div>`
                : '';
            const pic = l.photo
                ? `<img src="${l.photo}" alt="" class="size-full object-cover">`
                : `<span class="grid size-full place-items-center bg-sand text-gold"><svg viewBox="0 0 48 48" class="w-3/5" fill="none" stroke="currentColor" stroke-width=".8" stroke-linecap="round"><path d="M8 34c0-12 7-22 16-22s16 10 16 22"/><ellipse cx="24" cy="35" rx="18" ry="4"/><path d="M24 12c-2-3-6-3-5-1s3 1 5 1c2-3 6-3 5-1s-3 1-5 1"/><path d="M14 39l-1 2m22-2l1 2"/></svg></span>`;
            return `<li class="flex gap-5 border-b border-ink/10 py-7 first:pt-0">
                <a href="/xonca/${l.slug}?edit=${l.uid}" class="block h-32 w-26 shrink-0 overflow-hidden sm:h-36 sm:w-28">${pic}</a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <a href="/xonca/${l.slug}?edit=${l.uid}" class="font-display text-2xl leading-tight hover:text-gold-deep">${l.name}</a>
                        <div class="font-display text-xl whitespace-nowrap">${money(price)}</div>
                    </div>
                    <div class="caps mt-2 flex items-center gap-2 !text-[9px] text-taupe">
                        <span class="inline-block size-2.5 rounded-full ring-1 ring-black/10" style="background:${l.ribbon}"></span> ${MODES[l.mode]}
                    </div>
                    ${fill ? `<div class="mt-2 line-clamp-2 text-sm text-mist">${fill}</div>` : ''}
                    ${warn}
                    <div class="mt-4 flex gap-6">
                        <a href="/xonca/${l.slug}?edit=${l.uid}" class="link-line !text-[9px]">Dəyiş</a>
                        <button type="button" data-dup="${l.uid}" class="link-line !text-[9px] text-taupe">Eynisindən</button>
                        <button type="button" data-del="${l.uid}" class="link-line !text-[9px] text-mist">Sil</button>
                    </div>
                </div>
            </li>`;
        }).join('');
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
                ? 'Çatdırılma pulsuzdur.'
                : `Daha ${money(X.freeDeliveryFrom - sub)} — çatdırılma pulsuz olacaq`;
        } else {
            free.classList.add('hidden');
        }
        const availNote = $('[data-avail-note]', form);
        if (val('event_date')) {
            const lines = cart.lines();
            const bad = data.lines.some((q, i) => q.free !== null && lines.slice(0, i + 1).filter((x) => x.xonca_id === lines[i].xonca_id).length > q.free);
            availNote.classList.remove('hidden');
            availNote.className = `mt-4 text-sm ${bad ? 'text-wine' : 'text-gold-deep'}`;
            availNote.textContent = bad ? 'Bəzi xonçalar bu tarixə boş deyil — onları dəyişin və ya başqa tarix seçin.' : 'Bütün xonçalar bu tarixə boşdur.';
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
        $$('.field', form).forEach((i) => i.classList.remove('!border-wine'));
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
        Object.keys(errors).forEach((k) => form.elements[k]?.classList?.add('!border-wine'));
        box.innerHTML = Object.values(errors).flat().map((m) => `<div>${m}</div>`).join('');
        box.classList.remove('hidden');
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    toggleDelivery();
    render();
    refresh();
}
