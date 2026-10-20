/*
 * <xonca-video-maker> in the admin: the owner picks a video of the xonça,
 * chooses the moment shown before it plays, and the browser sends the file
 * in small pieces (VideoController) so the host's upload limit never sees
 * the whole film.
 */
const css = `
.xv{font:14px/1.5 inherit;display:grid;gap:14px}
.xv .row{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.xv button{border-radius:8px;padding:8px 14px;font-weight:600;background:#8a1c2b;color:#fff}
.xv button.ghost{background:transparent;color:inherit;border:1px solid #8884}
.xv button:disabled{opacity:.4}
.xv video{width:100%;max-width:520px;aspect-ratio:4/5;object-fit:cover;background:#f3ece1;border-radius:12px}
.xv input[type=range]{width:100%;max-width:520px}
.xv .bar{height:6px;background:#8883;border-radius:3px;overflow:hidden;max-width:520px}.xv .bar i{display:block;height:100%;background:#8a1c2b;width:0}
.xv .muted{opacity:.7;font-size:13px}
`;

const CHUNK = 1536 * 1024;
const MAX = 300 * 1024 * 1024;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const mb = (n) => (n / 1048576).toFixed(1);

class VideoMaker extends HTMLElement {
    connectedCallback() {
        if (this.ready) return;
        this.ready = true;
        const { existing, poster } = this.dataset;
        this.innerHTML = `<style>${css}</style>
        <div class="xv">
            <div class="row">
                <label><button type="button" class="ghost" data-pick>Video seç</button>
                <input type="file" accept="video/mp4,video/quicktime,video/webm,.mp4,.mov,.webm" hidden data-file></label>
                <span class="muted" data-info></span>
            </div>
            <video muted loop playsinline controls preload="metadata" ${existing ? `src="${existing}" poster="${poster || ''}"` : 'hidden'}></video>
            <div data-cover hidden>
                <p class="muted">Video başlamamış görünən kadr (üz qabığı):</p>
                <input type="range" data-at step="0.01" min="0" value="0">
            </div>
            <div class="bar"><i data-progress></i></div>
            <div class="row">
                <button type="button" data-save disabled>Saytda yadda saxla</button>
                ${existing ? '<button type="button" class="ghost" data-remove>Videonu sil</button>' : ''}
            </div>
        </div>`;
        this.$ = (s) => this.querySelector(s);
        this.video = this.$('video');
        if (existing) this.info('Saytda indi bu video var.');
        this.$('[data-pick]').onclick = () => this.$('[data-file]').click();
        this.$('[data-file]').onchange = (e) => e.target.files[0] && this.pick(e.target.files[0]);
        this.$('[data-at]').oninput = (e) => (this.video.currentTime = Number(e.target.value));
        this.$('[data-save]').onclick = () => this.save();
        this.$('[data-remove]')?.addEventListener('click', () => this.remove());
    }

    info(text) {
        this.$('[data-info]').textContent = text;
    }

    progress(p) {
        this.$('[data-progress]').style.width = `${Math.round(p * 100)}%`;
    }

    async pick(file) {
        this.$('[data-save]').disabled = true;
        this.progress(0);
        if (file.size > MAX) return this.info(`Fayl ${mb(file.size)} MB-dır. 300 MB-dan kiçik olmalıdır: videonu qısaldın.`);
        this.file = file;
        const v = this.video;
        v.hidden = false;
        v.removeAttribute('poster');
        v.src = URL.createObjectURL(file);
        const ok = await new Promise((r) => {
            v.onloadeddata = () => r(v.videoWidth > 0);
            v.onerror = () => r(false);
        });
        // An iPhone "High Efficiency" (HEVC) film often has sound but no picture outside Safari.
        if (!ok) {
            return this.info('Bu brauzer videonu göstərə bilmir. iPhone: Parametrlər, Kamera, Formatlar, «Ən uyğun» (Most Compatible) seçib yenidən çəkin.');
        }
        const at = this.$('[data-at]');
        at.max = v.duration.toFixed(2);
        at.value = Math.min(1, v.duration / 2).toFixed(2);
        v.currentTime = Number(at.value);
        this.$('[data-cover]').hidden = false;
        this.info(`${mb(file.size)} MB, ${v.duration.toFixed(0)} san.`);
        this.$('[data-save]').disabled = false;
    }

    async poster() {
        const v = this.video;
        if (v.seeking) await new Promise((r) => (v.onseeked = r));
        const k = Math.min(1, 1400 / v.videoWidth);
        const c = document.createElement('canvas');
        c.width = Math.round(v.videoWidth * k);
        c.height = Math.round(v.videoHeight * k);
        c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
        return new Promise((r) => c.toBlob(r, 'image/webp', 0.86));
    }

    async send(fd) {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        for (let attempt = 0; attempt < 4; attempt++) {
            const res = await fetch(this.dataset.url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).catch(() => null);
            if (res?.ok) return res.json();
            if (res && res.status < 500 && res.status !== 429) return null;
            await sleep(2000 * (attempt + 1));
        }
        return null;
    }

    async save() {
        const btn = this.$('[data-save]');
        btn.disabled = true;
        this.video.pause();
        const cover = await this.poster();
        const token = Math.random().toString(36).slice(2, 14).padEnd(10, 'x');
        const size = this.file.size;
        for (let offset = 0; offset < size; offset += CHUNK) {
            const fd = new FormData();
            fd.append('token', token);
            fd.append('offset', offset);
            fd.append('chunk', this.file.slice(offset, offset + CHUNK), 'part.bin');
            if (offset + CHUNK >= size) {
                fd.append('finish', '1');
                fd.append('poster', cover, 'poster.webp');
            }
            const res = await this.send(fd);
            if (!res) {
                this.info('Yükləmə alınmadı. İnterneti yoxlayıb yenidən «Saytda yadda saxla» basın.');
                btn.disabled = false;
                return;
            }
            this.progress(Math.min(1, (offset + CHUNK) / size));
            this.info(`Yüklənir: ${mb(Math.min(size, offset + CHUNK))} / ${mb(size)} MB`);
        }
        this.info('Yadda saxlandı. Video artıq saytdadır.');
    }

    async remove() {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        await fetch(this.dataset.url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
        this.video.removeAttribute('src');
        this.video.hidden = true;
        this.info('Video silindi.');
        this.$('[data-remove]')?.remove();
    }
}

customElements.get('xonca-video-maker') || customElements.define('xonca-video-maker', VideoMaker);
