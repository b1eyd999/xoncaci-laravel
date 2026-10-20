/*
 * 360° view: real photographs of one full turn of the xonça, shown frame by
 * frame as the finger or mouse drags. Used on the site and in the admin.
 */
export function createSpin(box, urls, { onFirstLoad, autoTurn = true } = {}) {
    const n = urls.length;
    if (!n) return null;
    let at = 0;
    let shown = -1;
    const img = document.createElement('img');
    img.alt = '';
    img.draggable = false;
    img.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;user-select:none;-webkit-user-drag:none;pointer-events:none';
    box.appendChild(img);
    box.style.touchAction = 'pan-y';
    box.style.cursor = 'grab';

    // Load the first frame at once, the rest in the background.
    const frames = urls.map(() => null);
    const load = (i) => {
        if (frames[i]) return frames[i];
        const im = new Image();
        im.decoding = 'async';
        im.src = urls[i];
        frames[i] = im;
        return im;
    };
    load(0).onload = () => {
        draw();
        onFirstLoad?.();
        urls.forEach((_, i) => i && load(i));
    };

    function draw() {
        const i = ((Math.round(at) % n) + n) % n;
        if (i === shown) return;
        const f = frames[i];
        if (f && f.complete && f.naturalWidth) {
            img.src = f.src;
            shown = i;
        }
    }

    // Drag: a full turn per ~1.4 widths of the box.
    let dragging = false, lastX = 0, velocity = 0, raf = 0, idleTurn = autoTurn;
    const step = () => n / (box.clientWidth * 1.4);
    box.addEventListener('pointerdown', (e) => {
        dragging = true;
        idleTurn = false;
        lastX = e.clientX;
        velocity = 0;
        box.setPointerCapture(e.pointerId);
        box.style.cursor = 'grabbing';
    });
    box.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const dx = e.clientX - lastX;
        lastX = e.clientX;
        velocity = -dx * step();
        at += velocity;
        draw();
    });
    const end = () => {
        dragging = false;
        box.style.cursor = 'grab';
    };
    box.addEventListener('pointerup', end);
    box.addEventListener('pointercancel', end);

    // Gentle momentum after letting go, and one slow turn as a hint.
    let turned = 0;
    function tick() {
        raf = requestAnimationFrame(tick);
        if (!dragging && Math.abs(velocity) > 0.01) {
            at += velocity;
            velocity *= 0.92;
            draw();
        } else if (idleTurn && !dragging) {
            at += n / 360 * 0.9;
            turned += n / 360 * 0.9;
            if (turned >= n) idleTurn = false;
            draw();
        }
    }
    raf = requestAnimationFrame(tick);

    return {
        destroy() {
            cancelAnimationFrame(raf);
            img.remove();
        },
    };
}
