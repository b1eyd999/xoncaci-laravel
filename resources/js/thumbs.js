/*
 * Catalogue cards show a still of the 3D xonça. One hidden renderer draws
 * them all in turn (browsers allow only a handful of live 3D canvases), and
 * each still is kept in the browser so the next visit shows it at once.
 */
const VERSION = 'v5';
let viewerPromise = null;
const queue = [];
let busy = false;

function hash(str) {
    let h = 5381;
    for (let i = 0; i < str.length; i++) h = ((h << 5) + h + str.charCodeAt(i)) | 0;
    return (h >>> 0).toString(36);
}

function stash(key, url) {
    try {
        localStorage.setItem(key, url);
    } catch {
        // Full: drop older stills and try once more.
        try {
            Object.keys(localStorage).filter((k) => k.startsWith('xonca.thumb.') || k.startsWith('xonca.icon.')).slice(0, 20).forEach((k) => localStorage.removeItem(k));
            localStorage.setItem(key, url);
        } catch {
            /* keep it in memory only */
        }
    }
}

async function viewer() {
    if (!viewerPromise) {
        viewerPromise = import('./xonca3d/viewer.js').then(({ createViewer }) => {
            const box = document.createElement('div');
            box.style.cssText = 'position:fixed;left:-10000px;top:0;width:560px;height:476px;pointer-events:none';
            document.body.appendChild(box);
            return createViewer(box, { controls: false, autoRotate: false, preserve: true, zoom: 0.92 });
        });
    }
    return viewerPromise;
}

export function thumbnail(cfg, items) {
    const key = `xonca.thumb.${VERSION}.${hash(JSON.stringify(cfg))}`;
    let cached = null;
    try {
        cached = localStorage.getItem(key);
    } catch {
        /* ignore */
    }
    if (cached) return Promise.resolve(cached);
    return new Promise((resolve) => {
        queue.push({ cfg, items, key, resolve });
        pump();
    });
}

/** A still of one filling item on its own. */
export function itemIcon(item) {
    const key = `xonca.icon.${VERSION}.${hash(JSON.stringify([item.model, item.color, item.color2, item.scale]))}`;
    let cached = null;
    try {
        cached = localStorage.getItem(key);
    } catch {
        /* ignore */
    }
    if (cached) return Promise.resolve(cached);
    return new Promise((resolve) => {
        queue.push({ item, key, resolve });
        pump();
    });
}

async function pump() {
    if (busy || !queue.length) return;
    busy = true;
    const v = await viewer();
    while (queue.length) {
        const { cfg, items, item, key, resolve } = queue.shift();
        if (item) {
            v.showItem(item);
        } else {
            v.load(cfg, cfg.fill || [], items);
            v.setAngle(0.35, 1.02);
        }
        const url = v.snapshot('image/webp');
        stash(key, url);
        resolve(url);
        await new Promise((r) => setTimeout(r, 16));
    }
    busy = false;
}

export function initThumbs() {
    const imgs = [...document.querySelectorAll('img[data-thumb]')];
    if (!imgs.length) return;
    const items = window.XONCA?.items || {};
    const show = (img) => {
        const cfg = JSON.parse(img.dataset.thumb);
        thumbnail(cfg, items).then((url) => {
            img.src = url;
            img.classList.remove('opacity-0');
            img.previousElementSibling?.matches('[data-thumb-skeleton]') && img.previousElementSibling.remove();
        });
    };
    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) {
                io.unobserve(e.target);
                show(e.target);
            }
        }
    }, { rootMargin: '300px' });
    imgs.forEach((img) => io.observe(img));
}
