/*
 * "Evinizdə baxın": the scanned xonça in 3D, and on a phone in the camera.
 *
 * - Android Chrome: WebXR first (it keeps the size we set), Scene Viewer as
 *   the fallback.
 * - iPhone Safari: AR Quick Look. Without an uploaded .usdz, model-viewer
 *   builds one from the scene in the browser, scale included.
 * - Computer: turn it with the mouse; a QR code opens the same page on the
 *   phone straight into this window (?ar=1).
 *
 * A scan comes out at whatever size the scanning app guessed, so it is
 * scaled here to the diameter the owner entered for the xonça.
 */
const $ = (s, r = document) => r.querySelector(s);

let loading = null;
const loadViewer = () => (loading ||= import('@google/model-viewer'));

export function initAr() {
    const modal = $('[data-ar-modal]');
    if (!modal) return;
    let viewer = null;

    async function build() {
        if (viewer) return viewer;
        await loadViewer();
        viewer = document.createElement('model-viewer');
        Object.entries({
            src: modal.dataset.glb,
            alt: `${modal.dataset.name} 3D`,
            ar: '',
            'ar-modes': 'webxr scene-viewer quick-look',
            'ar-scale': 'fixed',
            'ar-placement': 'floor',
            'camera-controls': '',
            'touch-action': 'pan-y',
            'auto-rotate': '',
            'auto-rotate-delay': '1500',
            'rotation-per-second': '12deg',
            'shadow-intensity': '1',
            'shadow-softness': '0.9',
            exposure: '1.05',
            'environment-image': 'neutral',
            'interaction-prompt': 'none',
            loading: 'eager',
        }).forEach(([k, v]) => viewer.setAttribute(k, v));
        if (modal.dataset.usdz) viewer.setAttribute('ios-src', modal.dataset.usdz);
        viewer.style.cssText = 'width:100%;height:100%;background:radial-gradient(ellipse at 50% 40%,#2a2420 0%,#0f0d0c 75%);--poster-color:transparent';
        // model-viewer draws its own AR button; ours is the one in the panel.
        viewer.innerHTML = '<button slot="ar-button" style="display:none"></button>';

        viewer.addEventListener('load', () => {
            $('[data-ar-loading]', modal)?.remove();
            const d = viewer.getDimensions();
            const real = (Number(modal.dataset.size) || 60) / 100;
            const across = Math.max(d.x, d.z);
            if (across > 0) {
                const k = real / across;
                viewer.scale = `${k} ${k} ${k}`;
                viewer.updateFraming();
            }
            viewer.cameraOrbit = '30deg 70deg auto';
            if (!viewer.canActivateAR) $('[data-ar-unsupported]', modal)?.classList.remove('hidden');
        });
        viewer.addEventListener('error', () => {
            const l = $('[data-ar-loading]', modal);
            if (l) l.textContent = (window.XONCA?.t || {}).model_failed || '3D';
        });
        $('[data-ar-stage]', modal).appendChild(viewer);
        return viewer;
    }

    async function open() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        build();
        const qr = $('[data-ar-qr]', modal);
        if (qr && !qr.firstChild) {
            const { default: QRCode } = await import('qrcode');
            qr.innerHTML = await QRCode.toString(modal.dataset.url, { type: 'svg', margin: 0, color: { dark: '#1a1614', light: '#0000' } });
        }
    }

    function close() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        if (new URLSearchParams(location.search).has('ar')) history.replaceState(null, '', location.pathname);
    }

    document.querySelectorAll('[data-ar-open]').forEach((b) => b.addEventListener('click', open));
    $('[data-ar-close]', modal).addEventListener('click', close);
    addEventListener('keydown', (e) => e.key === 'Escape' && !modal.classList.contains('hidden') && close());

    // The camera can only be opened from a tap, so the phone gets one button.
    $('[data-ar-camera]', modal).addEventListener('click', async () => {
        const v = await build();
        if (!v.loaded) await new Promise((r) => v.addEventListener('load', r, { once: true }));
        if (v.canActivateAR) v.activateAR();
        else $('[data-ar-unsupported]', modal)?.classList.remove('hidden');
    });

    if (new URLSearchParams(location.search).has('ar')) open();
}
