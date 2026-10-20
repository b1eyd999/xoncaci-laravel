/*
 * <xonca-spin-maker> in the admin: the owner films one turn of the xonça on
 * a turntable, picks the video here, marks where the turn starts and ends,
 * and the browser cuts it into evenly spaced frames, shows the 360° result
 * and uploads it. Photos taken one by one work too.
 */
import { createSpin } from './spin.js';

const css = `
.xs{font:14px/1.5 inherit;display:grid;gap:14px}
.xs .row{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.xs button{border-radius:8px;padding:8px 14px;font-weight:600;background:#8a1c2b;color:#fff}
.xs button.ghost{background:transparent;color:inherit;border:1px solid #8884}
.xs button:disabled{opacity:.4}
.xs .stage{position:relative;width:100%;max-width:520px;aspect-ratio:4/5;background:#f3ece1;border-radius:12px;overflow:hidden}
.xs .thumbs{display:grid;grid-template-columns:1fr 1fr;gap:10px;max-width:520px}
.xs .thumbs figure{margin:0}.xs .thumbs canvas{width:100%;border-radius:8px;background:#0001}
.xs .thumbs figcaption{font-size:12px;opacity:.7}
.xs input[type=range]{width:100%}
.xs .bar{height:6px;background:#8883;border-radius:3px;overflow:hidden;max-width:520px}.xs .bar i{display:block;height:100%;background:#8a1c2b;width:0}
.xs .muted{opacity:.7;font-size:13px}
`;

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

function toBlob(canvas) {
    return new Promise((r) => canvas.toBlob(r, 'image/webp', 0.86));
}

/** Draw a source into a canvas no wider than 1400 px. */
function snap(source, w, h) {
    const k = Math.min(1, 1400 / w);
    const c = document.createElement('canvas');
    c.width = Math.round(w * k);
    c.height = Math.round(h * k);
    c.getContext('2d').drawImage(source, 0, 0, c.width, c.height);
    return c;
}

class SpinMaker extends HTMLElement {
    connectedCallback() {
        if (this.ready) return;
        this.ready = true;
        const existing = JSON.parse(this.dataset.existing || '[]');
        this.innerHTML = `<style>${css}</style>
        <div class="xs">
            <div class="row">
                <label><button type="button" class="ghost" data-pick>Video və ya şəkillər seç</button>
                <input type="file" accept="video/*,image/*" multiple hidden data-file></label>
                <label class="row muted">Kadr sayı
                    <select data-count><option>24</option><option selected>36</option><option>48</option><option>72</option></select></label>
            </div>
            <div data-video hidden>
                <video muted playsinline preload="auto" hidden></video>
                <p class="muted">Xonçanın tam bir dəfə fırlandığı hissəni seçin: başlanğıc və son kadr eyni görünməlidir.</p>
                <div class="thumbs">
                    <figure><canvas data-a></canvas><figcaption>Başlanğıc: <b data-at></b> san</figcaption><input type="range" data-start step="0.01"></figure>
                    <figure><canvas data-b></canvas><figcaption>Son: <b data-bt></b> san</figcaption><input type="range" data-end step="0.01"></figure>
                </div>
            </div>
            <div class="row"><button type="button" data-cut disabled>Kadrları hazırla</button><span class="muted" data-info></span></div>
            <div class="bar"><i data-progress></i></div>
            <div class="stage" data-stage></div>
            <div class="row">
                <button type="button" data-save disabled>Saytda yadda saxla</button>
                ${existing.length ? '<button type="button" class="ghost" data-remove>360°-ni sil</button>' : ''}
            </div>
        </div>`;
        this.$ = (s) => this.querySelector(s);
        this.blobs = [];
        if (existing.length) {
            this.showSpin(existing);
            this.$('[data-info]').textContent = `Saytda indi ${existing.length} kadrlıq 360° var.`;
        }
        this.$('[data-pick]').onclick = () => this.$('[data-file]').click();
        this.$('[data-file]').onchange = (e) => this.pick([...e.target.files]);
        this.$('[data-cut]').onclick = () => this.cut();
        this.$('[data-save]').onclick = () => this.save();
        this.$('[data-remove]')?.addEventListener('click', () => this.remove());
    }

    showSpin(urls) {
        this.spin?.destroy();
        this.spin = createSpin(this.$('[data-stage]'), urls);
    }

    progress(p) {
        this.$('[data-progress]').style.width = `${Math.round(p * 100)}%`;
    }

    async pick(files) {
        this.blobs = [];
        this.$('[data-save]').disabled = true;
        const video = files.find((f) => f.type.startsWith('video/'));
        if (video) {
            this.mode = 'video';
            const v = this.$('video');
            v.src = URL.createObjectURL(video);
            await new Promise((r) => (v.onloadedmetadata = r));
            // Some recordings carry no length until the end has been read once.
            if (!Number.isFinite(v.duration)) {
                v.currentTime = 1e7;
                await new Promise((r) => (v.ondurationchange = () => Number.isFinite(v.duration) && r()));
                v.currentTime = 0;
            }
            const d = v.duration;
            const [s, e] = [this.$('[data-start]'), this.$('[data-end]')];
            s.max = e.max = d.toFixed(2);
            s.value = 0;
            e.value = d.toFixed(2);
            this.$('[data-video]').hidden = false;
            const show = async (input, canvas, label) => {
                v.currentTime = Math.min(Number(input.value), d - 0.05);
                await new Promise((r) => (v.onseeked = r));
                canvas.width = 480;
                canvas.height = Math.round(480 * v.videoHeight / v.videoWidth);
                canvas.getContext('2d').drawImage(v, 0, 0, canvas.width, canvas.height);
                this.$(label).textContent = Number(input.value).toFixed(1);
            };
            this.showStart = () => show(s, this.$('[data-a]'), '[data-at]');
            this.showEnd = () => show(e, this.$('[data-b]'), '[data-bt]');
            s.oninput = () => this.showStart();
            e.oninput = () => this.showEnd();
            await this.showStart();
            await this.showEnd();
            this.$('[data-info]').textContent = `Video: ${d.toFixed(1)} san.`;
        } else {
            this.mode = 'images';
            this.images = files.filter((f) => f.type.startsWith('image/')).sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true }));
            this.$('[data-video]').hidden = true;
            this.$('[data-info]').textContent = `${this.images.length} şəkil seçildi (adlarına görə sıralanıb).`;
        }
        this.$('[data-cut]').disabled = false;
    }

    async cut() {
        this.$('[data-cut]').disabled = true;
        this.blobs = [];
        if (this.mode === 'video') {
            const v = this.$('video');
            const n = Number(this.$('[data-count]').value);
            const a = Number(this.$('[data-start]').value);
            const b = Number(this.$('[data-end]').value);
            // The last frame of a full turn is the first one again, so it is left out.
            for (let i = 0; i < n; i++) {
                v.currentTime = a + ((b - a) * i) / n;
                await new Promise((r) => (v.onseeked = r));
                this.blobs.push(await toBlob(snap(v, v.videoWidth, v.videoHeight)));
                this.progress((i + 1) / n);
            }
        } else {
            for (const [i, f] of this.images.entries()) {
                const bmp = await createImageBitmap(f);
                this.blobs.push(await toBlob(snap(bmp, bmp.width, bmp.height)));
                this.progress((i + 1) / this.images.length);
            }
        }
        this.showSpin(this.blobs.map((b) => URL.createObjectURL(b)));
        this.$('[data-info]').textContent = `${this.blobs.length} kadr hazırdır. Barmaqla fırladıb yoxlayın, sonra yadda saxlayın.`;
        this.$('[data-cut]').disabled = false;
        this.$('[data-save]').disabled = this.blobs.length < 8;
    }

    async save() {
        const btn = this.$('[data-save]');
        btn.disabled = true;
        const token = Math.random().toString(36).slice(2, 14).padEnd(10, 'x');
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const per = 4;
        for (let i = 0; i < this.blobs.length; i += per) {
            const fd = new FormData();
            fd.append('token', token);
            fd.append('start', i);
            this.blobs.slice(i, i + per).forEach((b, k) => fd.append(`frames[${k}]`, b, `${i + k}.webp`));
            if (i + per >= this.blobs.length) fd.append('finish', '1');
            let ok = false;
            for (let attempt = 0; attempt < 3 && !ok; attempt++) {
                const res = await fetch(this.dataset.url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).catch(() => null);
                ok = !!res?.ok;
                if (!ok) await sleep(1500);
            }
            if (!ok) {
                this.$('[data-info]').textContent = 'Yükləmə alınmadı. İnterneti yoxlayıb yenidən cəhd edin.';
                btn.disabled = false;
                return;
            }
            this.progress(Math.min(1, (i + per) / this.blobs.length));
        }
        this.$('[data-info]').textContent = 'Yadda saxlandı. 360° artıq saytdadır.';
    }

    async remove() {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        await fetch(this.dataset.url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
        this.spin?.destroy();
        this.$('[data-info]').textContent = '360° silindi.';
        this.$('[data-remove]')?.remove();
    }
}

customElements.get('xonca-spin-maker') || customElements.define('xonca-spin-maker', SpinMaker);
