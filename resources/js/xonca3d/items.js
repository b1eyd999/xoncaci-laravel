import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';
import { butaCloth, stripes } from './textures.js';

/*
 * Every thing that can stand on a xonça, drawn from primitives so the owner
 * adds a new item in the admin by picking its shape and colours — no 3D
 * files to find or upload. Each maker returns a Group standing on y = 0 with
 * userData.r (footprint radius) and userData.h (height). Units: the tray's
 * radius is 1.
 */

const mats = new Map();

export function mat(color, opts = {}) {
    const key = color + JSON.stringify(opts);
    if (mats.has(key)) return mats.get(key);
    const { physical, ...rest } = opts;
    const m = physical
        ? new THREE.MeshPhysicalMaterial({ color, roughness: 0.5, ...rest })
        : new THREE.MeshStandardMaterial({ color, roughness: 0.55, metalness: 0, ...rest });
    mats.set(key, m);
    return m;
}

export const gold = (c = '#d4af37') => mat(c, { metalness: 1, roughness: 0.22 });
export const satin = (c) => mat(c, { physical: true, roughness: 0.5, sheen: 0.35, sheenRoughness: 0.4, envMapIntensity: 0.55, sheenColor: new THREE.Color(c).lerp(new THREE.Color('#ffffff'), 0.25) });
const velvet = (c) => mat(c, { physical: true, roughness: 0.9, sheen: 1, sheenRoughness: 0.8, sheenColor: new THREE.Color(c).offsetHSL(0, 0, 0.25) });
const glass = (c = '#ffffff') => mat(c, { physical: true, roughness: 0.04, transmission: 1, thickness: 0.05, ior: 1.45, transparent: true });
const flameMat = new THREE.MeshBasicMaterial({ color: '#ffcf5a' });
const flameCore = new THREE.MeshBasicMaterial({ color: '#fff6d8' });

function mesh(geo, material, { cast = true } = {}) {
    const m = new THREE.Mesh(geo, material);
    m.castShadow = cast;
    m.receiveShadow = true;
    return m;
}

function done(g, r, h) {
    g.userData.r = r;
    g.userData.h = h;
    return g;
}

function lathe(points, segments = 48) {
    return new THREE.LatheGeometry(points.map(([x, y]) => new THREE.Vector2(x, y)), segments);
}

/** A satin bow; used on items, on the tray rim and on top of the cover. */
export function bow(color, s = 1) {
    const g = new THREE.Group();
    const m = satin(color);
    const loop = new THREE.TorusGeometry(0.06, 0.018, 12, 32);
    for (const side of [-1, 1]) {
        const l = mesh(loop, m);
        l.scale.set(1, 0.6, 0.45);
        l.position.x = side * 0.055;
        l.rotation.z = side * 0.35;
        g.add(l);
        const tail = mesh(new THREE.BoxGeometry(0.03, 0.12, 0.006), m);
        tail.position.set(side * 0.03, -0.06, 0.01);
        tail.rotation.z = side * 0.35;
        g.add(tail);
    }
    const knot = mesh(new THREE.SphereGeometry(0.022, 16, 12), m);
    knot.scale.set(1, 1.1, 0.8);
    g.add(knot);
    g.scale.setScalar(s);
    return g;
}

function flame(parent, y, s = 1) {
    const f = new THREE.Mesh(new THREE.SphereGeometry(0.016 * s, 12, 10), flameMat);
    f.scale.set(1, 2.1, 1);
    f.position.y = y + 0.03 * s;
    f.userData.flame = true;
    const core = new THREE.Mesh(new THREE.SphereGeometry(0.008 * s, 8, 8), flameCore);
    core.scale.set(1, 1.8, 1);
    core.position.y = -0.004;
    f.add(core);
    parent.add(f);
    return f;
}

function candle(color, h = 0.28, r = 0.034) {
    const g = new THREE.Group();
    const holder = mesh(lathe([[0, 0], [r * 1.9, 0], [r * 1.9, 0.012], [r * 1.25, 0.03], [r * 1.25, 0.045], [0, 0.045]]), gold());
    g.add(holder);
    const body = mesh(new THREE.CylinderGeometry(r, r, h, 32), mat(color, { roughness: 0.45 }));
    body.position.y = 0.045 + h / 2;
    g.add(body);
    const wick = mesh(new THREE.CylinderGeometry(0.002, 0.002, 0.02, 6), mat('#222222'), { cast: false });
    wick.position.y = 0.045 + h + 0.01;
    g.add(wick);
    flame(g, 0.045 + h + 0.012);
    return done(g, r * 2, h + 0.1);
}

function plate(r, color = '#d4af37') {
    return mesh(lathe([[0, 0], [r * 0.7, 0], [r, 0.015], [r * 1.02, 0.022], [0, 0.012]]), gold(color));
}

const makers = {
    kelle_qend(it) {
        const g = new THREE.Group();
        const sugar = mesh(new THREE.ConeGeometry(0.15, 0.5, 48), mat('#fbfbf8', { roughness: 0.95 }));
        sugar.position.y = 0.25;
        g.add(sugar);
        const foil = mesh(new THREE.CylinderGeometry(0.128, 0.152, 0.07, 48, 1, true), mat(it.color2 || '#d4af37', { metalness: 1, roughness: 0.22, side: THREE.DoubleSide }));
        foil.position.y = 0.035;
        g.add(foil);
        const band = mesh(new THREE.TorusGeometry(0.105, 0.012, 10, 48), satin(it.color || '#b3122e'));
        band.rotation.x = Math.PI / 2;
        band.position.y = 0.17;
        g.add(band);
        const b = bow(it.color || '#b3122e', 1.2);
        b.position.set(0, 0.17, 0.11);
        g.add(b);
        const top = bow(it.color || '#b3122e', 0.8);
        top.position.y = 0.5;
        g.add(top);
        return done(g, 0.16, 0.56);
    },

    candle: (it) => candle(it.color || '#fff7e6'),

    candles3(it) {
        const g = new THREE.Group();
        g.add(plate(0.13));
        [[0, 0.3], [-0.06, 0.22], [0.06, 0.18]].forEach(([x, h], i) => {
            const c = candle(i === 0 ? it.color : (it.color2 || it.color), h, 0.028);
            c.position.set(x, 0.015, i === 0 ? -0.03 : 0.03);
            g.add(c);
        });
        return done(g, 0.14, 0.42);
    },

    pakhlava(it) {
        const g = new THREE.Group();
        g.add(plate(0.17));
        const shape = new THREE.Shape();
        shape.moveTo(0, -0.045);
        shape.lineTo(0.03, 0);
        shape.lineTo(0, 0.045);
        shape.lineTo(-0.03, 0);
        shape.closePath();
        const geo = new THREE.ExtrudeGeometry(shape, { depth: 0.035, bevelEnabled: true, bevelThickness: 0.006, bevelSize: 0.004, bevelSegments: 2 });
        geo.rotateX(-Math.PI / 2);
        const dough = mat(it.color || '#c98a3c', { roughness: 0.5 });
        const nut = mat(it.color2 || '#5b3a1e', { roughness: 0.7 });
        const spots = [[0, 0], [0.06, 0], [-0.06, 0], [0.03, 0.045], [-0.03, 0.045], [0.03, -0.045], [-0.03, -0.045]];
        spots.forEach(([x, z], i) => {
            const p = mesh(geo, dough);
            p.position.set(x, 0.02, z);
            g.add(p);
            const n = mesh(new THREE.SphereGeometry(0.009, 8, 6), nut);
            n.position.set(x, 0.063, z);
            g.add(n);
            if (i < 3) {
                const top = mesh(geo, dough);
                top.position.set(x, 0.065, z);
                top.rotation.y = 0.3;
                g.add(top);
            }
        });
        return done(g, 0.17, 0.12);
    },

    shekerbura(it) {
        const g = new THREE.Group();
        g.add(plate(0.16));
        const s = new THREE.Shape();
        s.absarc(0, 0, 0.05, Math.PI, 0, true);
        s.absarc(0, 0, 0.05, 0, Math.PI, true);
        const geo = new THREE.ExtrudeGeometry(s, { depth: 0.012, bevelEnabled: true, bevelThickness: 0.012, bevelSize: 0.012, bevelSegments: 5, curveSegments: 20 });
        geo.scale(1, 0.55, 1);
        geo.rotateX(-Math.PI / 2);
        const m = mat(it.color || '#eac78d', { roughness: 0.6 });
        const edge = new THREE.TorusGeometry(0.05, 0.006, 6, 24, Math.PI);
        const em = mat(it.color2 || '#c9954f');
        [[0, 0, 0], [0.07, 0.3, 1], [-0.07, -0.4, 2], [0, 1.2, 3], [0.035, 2, 4]].forEach(([x, rot, i]) => {
            const b = mesh(geo, m);
            b.position.set(x, 0.03 + (i > 2 ? 0.03 : 0), (i % 2 ? 0.05 : -0.04));
            b.rotation.y = rot;
            g.add(b);
            const e = mesh(edge, em);
            e.rotation.x = -Math.PI / 2;
            e.position.copy(b.position).add(new THREE.Vector3(0, 0.012, 0));
            e.rotation.z = rot;
            g.add(e);
        });
        return done(g, 0.16, 0.1);
    },

    sweets_bowl(it) {
        const g = new THREE.Group();
        const bowl = mesh(lathe([[0, 0], [0.04, 0], [0.03, 0.04], [0.06, 0.05], [0.12, 0.09], [0.125, 0.14], [0.118, 0.14], [0.11, 0.095], [0.055, 0.06], [0, 0.06]]), glass('#f4fbff'));
        g.add(bowl);
        const colors = [it.color || '#e84a5f', it.color2 || '#ffd166', '#ffffff'];
        const geo = new THREE.SphereGeometry(0.02, 12, 10);
        for (let i = 0; i < 26; i++) {
            const a = i * 2.4;
            const r = 0.02 + (i % 7) * 0.013;
            const c = mesh(geo, mat(colors[i % 3], { roughness: 0.25 }));
            c.scale.set(1.4, 0.8, 1);
            c.position.set(Math.cos(a) * r, 0.08 + Math.floor(i / 9) * 0.02, Math.sin(a) * r);
            c.rotation.y = a;
            g.add(c);
        }
        return done(g, 0.13, 0.15);
    },

    gift_box(it) {
        const g = new THREE.Group();
        const box = mesh(new RoundedBoxGeometry(0.2, 0.15, 0.2, 3, 0.012), satin(it.color || '#f2c6d0'));
        box.position.y = 0.075;
        g.add(box);
        const lid = mesh(new RoundedBoxGeometry(0.212, 0.04, 0.212, 3, 0.01), satin(it.color || '#f2c6d0'));
        lid.position.y = 0.15;
        g.add(lid);
        const r = satin(it.color2 || '#d4af37');
        const s1 = mesh(new THREE.BoxGeometry(0.216, 0.176, 0.03), r);
        s1.position.y = 0.088;
        g.add(s1);
        const s2 = mesh(new THREE.BoxGeometry(0.03, 0.176, 0.216), r);
        s2.position.y = 0.088;
        g.add(s2);
        const b = bow(it.color2 || '#d4af37', 1.1);
        b.position.y = 0.185;
        b.rotation.x = -0.5;
        g.add(b);
        return done(g, 0.15, 0.23);
    },

    ring_box(it) {
        const g = new THREE.Group();
        const v = velvet(it.color || '#8a1c2b');
        const base = mesh(new RoundedBoxGeometry(0.1, 0.055, 0.1, 3, 0.012), v);
        base.position.y = 0.0275;
        g.add(base);
        const lid = mesh(new RoundedBoxGeometry(0.1, 0.035, 0.1, 3, 0.012), v);
        lid.geometry.translate(0, 0.0175, 0.05);
        lid.position.set(0, 0.055, -0.05);
        lid.rotation.x = -1.9;
        g.add(lid);
        const cushion = mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.07, 16), velvet('#f5f0e6'));
        cushion.rotation.z = Math.PI / 2;
        cushion.position.y = 0.058;
        g.add(cushion);
        const ring = mesh(new THREE.TorusGeometry(0.022, 0.004, 12, 40), gold(it.color2 || '#e6c26b'));
        ring.position.y = 0.075;
        g.add(ring);
        const stone = mesh(new THREE.OctahedronGeometry(0.009), mat('#ffffff', { physical: true, roughness: 0, transmission: 0.9, ior: 2.4, thickness: 0.02 }));
        stone.position.y = 0.101;
        g.add(stone);
        return done(g, 0.08, 0.12);
    },

    henna_bowl(it) {
        const g = new THREE.Group();
        const bowl = mesh(lathe([[0, 0], [0.07, 0], [0.08, 0.02], [0.14, 0.06], [0.16, 0.11], [0.15, 0.11], [0.13, 0.07], [0, 0.07]]), gold(it.color2 || '#d4af37'));
        g.add(bowl);
        const henna = mesh(new THREE.CylinderGeometry(0.145, 0.13, 0.03, 40), mat(it.color || '#5c6b2b', { roughness: 0.95 }));
        henna.position.y = 0.085;
        g.add(henna);
        for (let i = 0; i < 5; i++) {
            const a = (i / 5) * Math.PI * 2;
            const c = candle(i % 2 ? '#fff4d6' : '#f7c6d0', 0.12, 0.016);
            c.children[0].visible = false;
            c.position.set(Math.cos(a) * 0.08, 0.055, Math.sin(a) * 0.08);
            g.add(c);
        }
        const c = candle('#ffffff', 0.18, 0.02);
        c.children[0].visible = false;
        c.position.y = 0.055;
        g.add(c);
        return done(g, 0.17, 0.32);
    },

    mirror(it) {
        const g = new THREE.Group();
        const fr = gold(it.color || '#d4af37');
        const stand = mesh(lathe([[0, 0], [0.07, 0], [0.07, 0.012], [0.02, 0.03], [0.012, 0.09], [0, 0.09]]), fr);
        g.add(stand);
        const face = new THREE.Group();
        face.position.y = 0.23;
        face.rotation.x = -0.12;
        const frame = mesh(new THREE.TorusGeometry(0.13, 0.016, 16, 64), fr);
        frame.scale.y = 1.15;
        face.add(frame);
        for (let i = 0; i < 16; i++) {
            const a = (i / 16) * Math.PI * 2;
            const k = mesh(new THREE.SphereGeometry(0.014, 10, 8), fr);
            k.position.set(Math.cos(a) * 0.15, Math.sin(a) * 0.15 * 1.15, 0);
            face.add(k);
        }
        const glassM = mesh(new THREE.CircleGeometry(0.128, 48), mat('#ffffff', { metalness: 1, roughness: 0.02 }));
        glassM.scale.y = 1.15;
        glassM.position.z = 0.004;
        face.add(glassM);
        const back = mesh(new THREE.CircleGeometry(0.13, 48), velvet(it.color2 || '#8a1c2b'));
        back.scale.y = 1.15;
        back.rotation.y = Math.PI;
        back.position.z = -0.004;
        face.add(back);
        g.add(face);
        return done(g, 0.14, 0.4);
    },

    perfume(it) {
        const g = new THREE.Group();
        const bottle = mesh(new RoundedBoxGeometry(0.07, 0.09, 0.045, 4, 0.015), glass('#ffffff'));
        bottle.position.y = 0.045;
        g.add(bottle);
        const liquid = mesh(new RoundedBoxGeometry(0.058, 0.06, 0.034, 3, 0.012), mat(it.color || '#f0b6c8', { physical: true, transmission: 0.6, roughness: 0.1, thickness: 0.03 }));
        liquid.position.y = 0.036;
        g.add(liquid);
        const neck = mesh(new THREE.CylinderGeometry(0.012, 0.014, 0.02, 16), gold());
        neck.position.y = 0.1;
        g.add(neck);
        const cap = mesh(new THREE.SphereGeometry(0.026, 20, 14), gold(it.color2 || '#d4af37'));
        cap.scale.y = 1.25;
        cap.position.y = 0.13;
        g.add(cap);
        return done(g, 0.05, 0.16);
    },

    fabric(it) {
        const g = new THREE.Group();
        const tex = stripes(it.color || '#7b1e3a', it.color2 || '#d4af37').clone();
        tex.needsUpdate = true;
        tex.repeat.set(3, 1);
        const roll = mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.32, 40), mat('#ffffff', { map: tex, roughness: 0.7 }));
        roll.rotation.z = Math.PI / 2;
        roll.position.y = 0.06;
        g.add(roll);
        for (const x of [-0.09, 0.09]) {
            const tie = mesh(new THREE.TorusGeometry(0.062, 0.006, 8, 40), satin(it.color2 || '#d4af37'));
            tie.rotation.y = Math.PI / 2;
            tie.position.set(x, 0.06, 0);
            g.add(tie);
        }
        const b = bow(it.color2 || '#d4af37', 0.8);
        b.position.set(0, 0.125, 0);
        b.rotation.x = -Math.PI / 2 + 0.3;
        g.add(b);
        return done(g, 0.17, 0.14);
    },

    shawl(it) {
        const g = new THREE.Group();
        const m = mat('#ffffff', { map: butaCloth(it.color || '#ffffff', it.color2 || '#b3122e'), roughness: 0.8, sheen: 1, physical: true });
        for (let i = 0; i < 3; i++) {
            const f = mesh(new RoundedBoxGeometry(0.24 - i * 0.02, 0.028, 0.18 - i * 0.015, 3, 0.012), m);
            f.position.set(i * 0.006, 0.014 + i * 0.028, -i * 0.004);
            f.rotation.y = (i - 1) * 0.06;
            g.add(f);
        }
        const b = bow(it.color2 || '#b3122e', 0.9);
        b.position.set(0, 0.1, 0.02);
        b.rotation.x = -Math.PI / 2 + 0.2;
        g.add(b);
        return done(g, 0.16, 0.12);
    },

    apple(it) {
        const g = new THREE.Group();
        const body = mesh(lathe(Array.from({ length: 17 }, (_, i) => {
            const t = (i / 16) * Math.PI;
            return [Math.sin(t) * 0.062 * (1 - 0.1 * Math.cos(t)), 0.06 - Math.cos(t) * 0.055];
        }), 32), mat(it.color || '#c0392b', { roughness: 0.35 }));
        g.add(body);
        const stem = mesh(new THREE.CylinderGeometry(0.003, 0.004, 0.035, 6), mat('#5a3d22'));
        stem.position.y = 0.118;
        stem.rotation.z = 0.2;
        g.add(stem);
        const leaf = mesh(new THREE.SphereGeometry(0.018, 10, 6), mat(it.color2 || '#3f8f3a'));
        leaf.scale.set(1.6, 0.2, 0.7);
        leaf.position.set(0.018, 0.128, 0);
        leaf.rotation.z = 0.5;
        g.add(leaf);
        return done(g, 0.065, 0.13);
    },

    pomegranate(it) {
        const g = new THREE.Group();
        const body = mesh(new THREE.SphereGeometry(0.072, 32, 24), mat(it.color || '#a3162a', { roughness: 0.4 }));
        body.scale.y = 0.92;
        body.position.y = 0.066;
        g.add(body);
        const crown = mesh(new THREE.CylinderGeometry(0.022, 0.016, 0.03, 8, 1, true), mat(it.color || '#a3162a', { side: THREE.DoubleSide }));
        crown.position.y = 0.143;
        g.add(crown);
        for (let i = 0; i < 6; i++) {
            const a = (i / 6) * Math.PI * 2;
            const t = mesh(new THREE.ConeGeometry(0.007, 0.022, 5), mat(it.color2 || '#7d1020'));
            t.position.set(Math.cos(a) * 0.02, 0.163, Math.sin(a) * 0.02);
            t.rotation.set(Math.sin(a) * 0.5, 0, -Math.cos(a) * 0.5);
            g.add(t);
        }
        return done(g, 0.075, 0.17);
    },

    orange(it) {
        const g = new THREE.Group();
        const geo = new THREE.SphereGeometry(0.066, 40, 28);
        const pos = geo.attributes.position;
        for (let i = 0; i < pos.count; i++) {
            const n = 1 + (Math.sin(pos.getX(i) * 240) * Math.sin(pos.getY(i) * 260) * Math.sin(pos.getZ(i) * 250)) * 0.012;
            pos.setXYZ(i, pos.getX(i) * n, pos.getY(i) * n, pos.getZ(i) * n);
        }
        geo.computeVertexNormals();
        const body = mesh(geo, mat(it.color || '#f28c1b', { roughness: 0.6 }));
        body.position.y = 0.064;
        g.add(body);
        const leaf = mesh(new THREE.SphereGeometry(0.02, 10, 6), mat(it.color2 || '#2f7d32'));
        leaf.scale.set(1.5, 0.2, 0.7);
        leaf.position.set(0.02, 0.13, 0);
        g.add(leaf);
        return done(g, 0.068, 0.135);
    },

    grapes(it) {
        const g = new THREE.Group();
        const geo = new THREE.SphereGeometry(0.02, 14, 10);
        const m = mat(it.color || '#5b2a6e', { roughness: 0.25, physical: true, clearcoat: 0.6 });
        const rows = [6, 5, 4, 3, 2, 1];
        rows.forEach((n, row) => {
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2 + row;
                const r = n > 1 ? 0.012 + n * 0.007 : 0;
                const b = mesh(geo, m);
                b.position.set(Math.cos(a) * r, 0.14 - row * 0.024, Math.sin(a) * r);
                g.add(b);
            }
        });
        g.rotation.x = Math.PI;
        g.position.y = 0;
        const holder = new THREE.Group();
        g.position.y = 0.16;
        holder.add(g);
        const stem = mesh(new THREE.CylinderGeometry(0.004, 0.005, 0.04, 6), mat(it.color2 || '#6b4a2b'));
        stem.position.y = 0.17;
        holder.add(stem);
        return done(holder, 0.08, 0.19);
    },

    nuts_bowl(it) {
        const g = new THREE.Group();
        g.add(mesh(lathe([[0, 0], [0.05, 0], [0.12, 0.05], [0.125, 0.06], [0.115, 0.058], [0.05, 0.02], [0, 0.02]]), gold(it.color2 || '#d4af37')));
        const geo = new THREE.SphereGeometry(0.016, 10, 8);
        const colors = [it.color || '#8b5a2b', '#c49a6c', '#6f8f3a'];
        for (let i = 0; i < 40; i++) {
            const a = i * 2.39;
            const r = Math.sqrt(i / 40) * 0.1;
            const n = mesh(geo, mat(colors[i % 3], { roughness: 0.6 }));
            n.scale.set(1.3, 0.8, 0.9);
            n.position.set(Math.cos(a) * r, 0.04 + (0.1 - r) * 0.25, Math.sin(a) * r);
            n.rotation.y = a;
            g.add(n);
        }
        return done(g, 0.13, 0.08);
    },

    flowers(it) {
        const g = new THREE.Group();
        const wrap = mesh(new THREE.ConeGeometry(0.1, 0.25, 24, 1, true), mat(it.color2 || '#fdf1f3', { physical: true, roughness: 0.4, sheen: 1, side: THREE.DoubleSide }));
        wrap.rotation.x = Math.PI;
        wrap.position.y = 0.2;
        g.add(wrap);
        const tie = bow(it.color2 === '#ffffff' ? '#b3122e' : '#d4af37', 0.7);
        tie.position.set(0, 0.12, 0.05);
        g.add(tie);
        const petal = lathe([[0, 0], [0.018, 0.006], [0.03, 0.02], [0.034, 0.034], [0.03, 0.04]], 16);
        const pm = mat(it.color || '#c2185b', { roughness: 0.6, side: THREE.DoubleSide });
        const heads = [[0, 0.36, 0], [0.05, 0.34, 0.02], [-0.05, 0.34, 0.01], [0.02, 0.33, -0.05], [-0.02, 0.33, 0.05], [0.05, 0.31, -0.04], [-0.05, 0.31, -0.03]];
        heads.forEach(([x, y, z], i) => {
            const rose = new THREE.Group();
            for (let k = 0; k < 3; k++) {
                const p = mesh(petal, pm);
                p.scale.setScalar(1 - k * 0.25);
                p.position.y = k * 0.008;
                p.rotation.y = k;
                rose.add(p);
            }
            rose.position.set(x, y, z);
            rose.rotation.set(x * 4, 0, -x * 4);
            g.add(rose);
            if (i < 4) {
                const leaf = mesh(new THREE.SphereGeometry(0.02, 8, 6), mat('#3e7d3a'));
                leaf.scale.set(1.8, 0.2, 0.8);
                leaf.position.set(x * 1.4, y - 0.03, z * 1.4);
                leaf.rotation.y = i;
                g.add(leaf);
            }
        });
        return done(g, 0.1, 0.42);
    },

    cake(it) {
        const g = new THREE.Group();
        g.add(plate(0.18, '#e8e8e8'));
        const cream = mat(it.color || '#fbf3ea', { roughness: 0.7 });
        const pearl = mat('#ffffff', { roughness: 0.15, metalness: 0.2 });
        let y = 0.015;
        [[0.15, 0.11], [0.105, 0.09], [0.065, 0.075]].forEach(([r, h]) => {
            const tier = mesh(new THREE.CylinderGeometry(r, r, h, 48), cream);
            tier.position.y = y + h / 2;
            g.add(tier);
            const band = mesh(new THREE.TorusGeometry(r, 0.007, 8, 60), satin(it.color2 || '#d4af37'));
            band.rotation.x = Math.PI / 2;
            band.position.y = y + 0.012;
            g.add(band);
            const n = Math.round(r * 120);
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2;
                const p = mesh(new THREE.SphereGeometry(0.006, 8, 6), pearl, { cast: false });
                p.position.set(Math.cos(a) * r, y + h, Math.sin(a) * r);
                g.add(p);
            }
            y += h;
        });
        const top = makers.flowers({ color: it.color2 || '#c2185b', color2: '#ffffff' });
        top.scale.setScalar(0.35);
        top.position.y = y - 0.03;
        g.add(top);
        return done(g, 0.18, y + 0.12);
    },

    chocolate(it) {
        const g = new THREE.Group();
        const s = new THREE.Shape();
        s.moveTo(0, -0.08);
        s.bezierCurveTo(0.03, -0.05, 0.12, 0, 0.1, 0.05);
        s.bezierCurveTo(0.085, 0.09, 0.03, 0.09, 0, 0.055);
        s.bezierCurveTo(-0.03, 0.09, -0.085, 0.09, -0.1, 0.05);
        s.bezierCurveTo(-0.12, 0, -0.03, -0.05, 0, -0.08);
        const geo = new THREE.ExtrudeGeometry(s, { depth: 0.04, bevelEnabled: true, bevelThickness: 0.008, bevelSize: 0.008, bevelSegments: 3, curveSegments: 24 });
        geo.rotateX(-Math.PI / 2);
        const box = mesh(geo, satin(it.color || '#8a1c2b'));
        box.position.y = 0.008;
        g.add(box);
        const b = bow(it.color2 || '#d4af37', 0.9);
        b.position.set(0, 0.065, 0.01);
        b.rotation.x = -Math.PI / 2 + 0.2;
        g.add(b);
        return done(g, 0.12, 0.08);
    },

    necklace(it) {
        const g = new THREE.Group();
        const box = mesh(new RoundedBoxGeometry(0.2, 0.03, 0.16, 3, 0.01), velvet(it.color || '#1d3557'));
        box.position.y = 0.015;
        g.add(box);
        const back = mesh(new RoundedBoxGeometry(0.2, 0.16, 0.03, 3, 0.01), velvet(it.color || '#1d3557'));
        back.position.set(0, 0.08, -0.08);
        back.rotation.x = -0.25;
        g.add(back);
        const chain = new THREE.Group();
        const bead = new THREE.SphereGeometry(0.008, 10, 8);
        const bm = gold(it.color2 || '#e6c26b');
        for (let i = 0; i < 28; i++) {
            const t = (i / 27) * Math.PI;
            const b = mesh(bead, bm, { cast: false });
            b.position.set(Math.cos(t) * 0.06, -Math.sin(t) * 0.07, 0);
            chain.add(b);
        }
        const pend = mesh(new THREE.OctahedronGeometry(0.016), mat('#c1121f', { physical: true, roughness: 0, transmission: 0.5, ior: 1.8 }));
        pend.position.set(0, -0.09, 0);
        chain.add(pend);
        chain.position.set(0, 0.13, -0.06);
        chain.rotation.x = -0.25;
        g.add(chain);
        return done(g, 0.13, 0.17);
    },

    shoes(it) {
        const g = new THREE.Group();
        const box = mesh(new RoundedBoxGeometry(0.26, 0.1, 0.16, 3, 0.01), satin(it.color || '#ffffff'));
        box.position.y = 0.05;
        g.add(box);
        const lid = mesh(new RoundedBoxGeometry(0.27, 0.03, 0.17, 3, 0.01), satin(it.color || '#ffffff'));
        lid.position.y = 0.105;
        g.add(lid);
        const band = mesh(new THREE.BoxGeometry(0.03, 0.122, 0.176), satin(it.color2 || '#d4af37'));
        band.position.y = 0.061;
        g.add(band);
        const b = bow(it.color2 || '#d4af37', 1);
        b.position.y = 0.13;
        b.rotation.x = -0.6;
        g.add(b);
        return done(g, 0.16, 0.16);
    },

    tea(it) {
        const g = new THREE.Group();
        const tin = mesh(new THREE.CylinderGeometry(0.045, 0.045, 0.13, 32), mat(it.color || '#1f6f5c', { metalness: 0.6, roughness: 0.3 }));
        tin.position.y = 0.065;
        g.add(tin);
        for (const y of [0.02, 0.11]) {
            const b = mesh(new THREE.CylinderGeometry(0.047, 0.047, 0.012, 32), gold(it.color2 || '#d4af37'));
            b.position.y = y;
            g.add(b);
        }
        const lid = mesh(new THREE.CylinderGeometry(0.047, 0.047, 0.02, 32), gold(it.color2 || '#d4af37'));
        lid.position.y = 0.14;
        g.add(lid);
        return done(g, 0.05, 0.15);
    },
};

/** Things that look best in the middle of the tray. */
export const CENTRAL = ['kelle_qend', 'cake', 'henna_bowl', 'flowers', 'mirror'];

export function makeItem(item) {
    const maker = makers[item.model] || makers.gift_box;
    const g = maker(item);
    // Items are drawn life-size against a 60 cm tray; this lifts them to
    // the size a real xonça shows them at.
    const s = (item.scale || 1) * 1.45;
    g.scale.multiplyScalar(s);
    g.userData.r *= s;
    g.userData.h *= s;
    return g;
}
