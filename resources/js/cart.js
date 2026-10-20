/*
 * The cart lives in the browser until the order is sent; the server prices
 * it again from ids alone, so nothing here decides what is charged.
 */
const KEY = 'xonca.cart.v1';

function read() {
    try {
        return JSON.parse(localStorage.getItem(KEY)) || [];
    } catch {
        return [];
    }
}

function write(lines) {
    try {
        localStorage.setItem(KEY, JSON.stringify(lines));
    } catch {
        /* private mode: the cart lasts for this page only */
    }
    cart.renderCount(true);
}

export const cart = {
    lines: read,
    add(line) {
        const lines = read();
        lines.push({ ...line, uid: Math.random().toString(36).slice(2, 10) });
        write(lines);
    },
    replace(uid, line) {
        write(read().map((l) => (l.uid === uid ? { ...line, uid } : l)));
    },
    remove(uid) {
        write(read().filter((l) => l.uid !== uid));
    },
    duplicate(uid) {
        const lines = read();
        const i = lines.findIndex((l) => l.uid === uid);
        if (i >= 0) lines.splice(i + 1, 0, { ...lines[i], uid: Math.random().toString(36).slice(2, 10) });
        write(lines);
    },
    get(uid) {
        return read().find((l) => l.uid === uid);
    },
    clear() {
        write([]);
    },
    /** What the server needs: ids and quantities only. */
    payload() {
        return read().map((l) => ({ xonca_id: l.xonca_id, fill_mode: l.mode, ribbon: l.ribbon, fill: l.mode === 'custom' ? l.fill : [] }));
    },
    renderCount(bump = false) {
        const n = read().length;
        document.querySelectorAll('[data-cart-count]').forEach((el) => {
            el.textContent = `(${n})`;
            el.classList.toggle('hidden', !n);
        });
    },
};

export function money(v) {
    const n = Number(v) || 0;
    return (Number.isInteger(n) ? n.toString() : n.toFixed(2)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₼';
}

export function toast(html, ms = 3500) {
    const host = document.querySelector('[data-toast]');
    if (!host) return;
    const el = document.createElement('div');
    el.className = 'hero-in pointer-events-auto flex items-center bg-onyx px-7 py-4 text-sm text-ivory shadow-2xl';
    el.innerHTML = html;
    host.replaceChildren(el);
    setTimeout(() => el.remove(), ms);
}

export async function post(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
        },
        body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    return { ok: res.ok, status: res.status, data };
}

/** A sentence in the page's language, with :name style placeholders filled. */
export function t(key, vars = {}) {
    let text = (window.XONCA?.t || {})[key] ?? key;
    for (const [k, v] of Object.entries(vars)) text = text.replaceAll(`:${k}`, v);
    return text;
}
