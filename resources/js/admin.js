/*
 * 3D previews inside the admin. <xonca-preview> reads the form it sits in
 * (through Livewire) and redraws as the owner changes a colour, a shape or
 * the filling — so he sees the xonça the customer will see before saving.
 *
 *   data-mode="xonca"   the xonça form: tray, cover, ribbon, default filling
 *   data-mode="item"    the filling-item form: one item on a small tray
 *   data-mode="static"  a fixed config (orders): data-config holds it
 */
import '../css/admin.css';

let viewerModule = null;
const load = () => (viewerModule ||= import('./xonca3d/viewer.js'));

function livewireOf(el) {
    const host = el.closest('[wire\\:id]');
    return host && window.Livewire ? window.Livewire.find(host.getAttribute('wire:id')) : null;
}

const pick = (v, d) => (v === null || v === undefined || v === '' ? d : v);

class XoncaPreview extends HTMLElement {
    async connectedCallback() {
        if (this.viewer) return;
        this.style.display = 'block';
        const box = document.createElement('div');
        box.style.cssText = `width:100%;height:${this.dataset.height || 360}px;border-radius:14px;overflow:hidden;background:radial-gradient(ellipse at 50% 35%,#fff 0%,#fbf1e4 45%,#f2e2cf 100%)`;
        this.appendChild(box);
        this.items = JSON.parse(this.dataset.items || '{}');
        const { createViewer } = await load();
        this.viewer = createViewer(box, { autoRotate: true });
        this.last = '';
        this.update();
        if (this.dataset.mode !== 'static') this.timer = setInterval(() => this.update(), 500);
    }

    disconnectedCallback() {
        clearInterval(this.timer);
        this.viewer?.dispose();
        this.viewer = null;
    }

    config() {
        const mode = this.dataset.mode;
        if (mode === 'static') {
            const cfg = JSON.parse(this.dataset.config || '{}');
            return { cfg, fill: cfg.fill || [], items: this.items };
        }
        const wire = livewireOf(this);
        const d = wire?.get('data') || {};
        if (mode === 'item') {
            const item = {
                id: 'preview',
                model: d.model || 'gift_box',
                color: pick(d.color, '#c0392b'),
                color2: d.color2 || null,
                scale: Number(pick(d.scale, 1)),
            };
            return {
                cfg: { shape: 'round', trayColor: '#c9a14a', rimColor: '#e8c77a', coverType: 'none', ribbonColor: '#b3122e' },
                fill: [{ id: 'preview', qty: Math.max(1, Number(this.dataset.qty || 1)) }],
                items: { preview: item },
            };
        }
        const fills = Object.values(d.defaultFills || {})
            .filter((f) => f && f.fill_item_id)
            .map((f) => ({ id: Number(f.fill_item_id), qty: Math.max(1, Number(f.qty) || 1) }));
        return {
            cfg: {
                shape: pick(d.shape, 'round'),
                trayColor: pick(d.tray_color, '#c9a14a'),
                rimColor: pick(d.rim_color, '#e8c77a'),
                coverType: pick(d.cover_type, 'tulle'),
                coverColor: pick(d.cover_color, '#fff4f4'),
                ribbonColor: pick(d.ribbon_color, '#b3122e'),
            },
            fill: fills,
            items: this.items,
        };
    }

    update() {
        if (!this.viewer) return;
        const { cfg, fill, items } = this.config();
        const key = JSON.stringify([cfg, fill, this.dataset.mode === 'item' ? items : 0]);
        if (key === this.last) return;
        const shapeKey = JSON.stringify(cfg);
        const onlyFill = this.lastShape === shapeKey;
        this.last = key;
        this.lastShape = shapeKey;
        if (onlyFill && this.dataset.mode === 'xonca') this.viewer.setFill(fill, items);
        else this.viewer.load(cfg, fill, items);
    }
}

customElements.get('xonca-preview') || customElements.define('xonca-preview', XoncaPreview);
