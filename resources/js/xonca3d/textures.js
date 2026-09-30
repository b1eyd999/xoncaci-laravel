import * as THREE from 'three';

const cache = new Map();

function canvas(size = 512) {
    const c = document.createElement('canvas');
    c.width = c.height = size;
    return [c, c.getContext('2d')];
}

/** One buta (paisley) — the motif of kəlağayı and of engraved trays. */
function buta(ctx, x, y, s, rot, fill, stroke) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(rot);
    ctx.scale(s, s);
    ctx.beginPath();
    ctx.moveTo(0, 40);
    ctx.bezierCurveTo(-34, 30, -36, -10, -12, -26);
    ctx.bezierCurveTo(8, -40, 30, -30, 24, -10);
    ctx.bezierCurveTo(20, 4, 4, 2, 6, -10);
    ctx.bezierCurveTo(22, 10, 26, 34, 0, 40);
    ctx.closePath();
    if (fill) {
        ctx.fillStyle = fill;
        ctx.fill();
    }
    if (stroke) {
        ctx.strokeStyle = stroke;
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(-6, 8, 7, 0, Math.PI * 2);
        ctx.stroke();
    }
    ctx.restore();
}

/** Height map of an engraved tray: rings of buta around a rosette. */
export function trayEngraving() {
    if (cache.has('tray')) return cache.get('tray');
    const [c, ctx] = canvas(1024);
    ctx.fillStyle = '#808080';
    ctx.fillRect(0, 0, 1024, 1024);
    ctx.translate(512, 512);
    ctx.strokeStyle = '#d8d8d8';
    ctx.lineWidth = 6;
    for (const r of [120, 250, 262, 420, 432, 500]) {
        ctx.beginPath();
        ctx.arc(0, 0, r, 0, Math.PI * 2);
        ctx.stroke();
    }
    for (let i = 0; i < 16; i++) {
        const a = (i / 16) * Math.PI * 2;
        ctx.save();
        ctx.rotate(a);
        ctx.beginPath();
        ctx.ellipse(0, -60, 22, 58, 0, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }
    for (let i = 0; i < 18; i++) {
        const a = (i / 18) * Math.PI * 2;
        buta(ctx, Math.cos(a) * 340, Math.sin(a) * 340, 1.5, a + Math.PI / 2, null, '#e4e4e4');
    }
    for (let i = 0; i < 48; i++) {
        const a = (i / 48) * Math.PI * 2;
        ctx.beginPath();
        ctx.arc(Math.cos(a) * 466, Math.sin(a) * 466, 9, 0, Math.PI * 2);
        ctx.fillStyle = '#e0e0e0';
        ctx.fill();
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    cache.set('tray', tex);
    return tex;
}

/** A kəlağayı print: buta in rows on a coloured ground. */
export function butaCloth(bg, fg) {
    const key = `cloth${bg}${fg}`;
    if (cache.has(key)) return cache.get(key);
    const [c, ctx] = canvas(512);
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, 512, 512);
    for (let row = 0; row < 5; row++) {
        for (let col = 0; col < 5; col++) {
            const x = col * 110 + (row % 2) * 55 + 20;
            const y = row * 110 + 50;
            buta(ctx, x, y, 0.9, 0.4, fg, null);
        }
    }
    ctx.strokeStyle = fg;
    ctx.lineWidth = 10;
    ctx.strokeRect(8, 8, 496, 496);
    const tex = new THREE.CanvasTexture(c);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    cache.set(key, tex);
    return tex;
}

/** Fine mesh for the tulle cover, used as an alpha map. */
export function tulleMesh() {
    if (cache.has('tulle')) return cache.get('tulle');
    const [c, ctx] = canvas(256);
    ctx.fillStyle = '#4a4a4a';
    ctx.fillRect(0, 0, 256, 256);
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1.6;
    for (let i = -256; i < 512; i += 8) {
        ctx.beginPath();
        ctx.moveTo(i, 0);
        ctx.lineTo(i + 256, 256);
        ctx.moveTo(i + 256, 0);
        ctx.lineTo(i, 256);
        ctx.stroke();
    }
    for (let i = 0; i < 40; i++) {
        buta(ctx, Math.random() * 256, Math.random() * 256, 0.25, Math.random() * 6, '#ffffff', null);
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(10, 5);
    cache.set('tulle', tex);
    return tex;
}

/** Stripes for a rolled fabric. */
export function stripes(a, b) {
    const key = `str${a}${b}`;
    if (cache.has(key)) return cache.get(key);
    const [c, ctx] = canvas(256);
    ctx.fillStyle = a;
    ctx.fillRect(0, 0, 256, 256);
    ctx.fillStyle = b;
    for (let x = 0; x < 256; x += 32) ctx.fillRect(x, 0, 6, 256);
    const tex = new THREE.CanvasTexture(c);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    cache.set(key, tex);
    return tex;
}
