import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { makeItem, CENTRAL, bow, mat, gold, satin } from './items.js';
import { trayEngraving, tulleMesh } from './textures.js';

/* ------------------------------------------------------------------ shapes */

/** The outline of a tray, in units where a round tray has radius 1. */
function outline(shape) {
    const s = new THREE.Shape();
    switch (shape) {
        case 'oval':
            s.absellipse(0, 0, 1.25, 0.85, 0, Math.PI * 2, false, 0);
            break;
        case 'rect': {
            const w = 1.2, h = 0.85, r = 0.22;
            s.moveTo(-w + r, -h);
            s.lineTo(w - r, -h);
            s.quadraticCurveTo(w, -h, w, -h + r);
            s.lineTo(w, h - r);
            s.quadraticCurveTo(w, h, w - r, h);
            s.lineTo(-w + r, h);
            s.quadraticCurveTo(-w, h, -w, h - r);
            s.lineTo(-w, -h + r);
            s.quadraticCurveTo(-w, -h, -w + r, -h);
            break;
        }
        case 'octagon':
            for (let i = 0; i <= 8; i++) {
                const a = (i / 8) * Math.PI * 2 + Math.PI / 8;
                const p = [Math.cos(a) * 1.05, Math.sin(a) * 1.05];
                i ? s.lineTo(...p) : s.moveTo(...p);
            }
            break;
        case 'heart': {
            // Point of the heart towards the viewer (shape y becomes -z).
            const k = 1.12;
            s.moveTo(0, -0.95 * k);
            s.bezierCurveTo(0.35 * k, -0.62 * k, 1.05 * k, -0.25 * k, 1.0 * k, 0.28 * k);
            s.bezierCurveTo(0.96 * k, 0.78 * k, 0.32 * k, 0.9 * k, 0, 0.5 * k);
            s.bezierCurveTo(-0.32 * k, 0.9 * k, -0.96 * k, 0.78 * k, -1.0 * k, 0.28 * k);
            s.bezierCurveTo(-1.05 * k, -0.25 * k, -0.35 * k, -0.62 * k, 0, -0.95 * k);
            break;
        }
        default:
            s.absarc(0, 0, 1, 0, Math.PI * 2, false);
    }
    return s;
}

function polygon(shape, n = 180) {
    const pts = shape.getSpacedPoints(n);
    if (pts[0].distanceTo(pts[pts.length - 1]) < 1e-6) pts.pop();
    return pts;
}

function inside(pts, x, y) {
    let c = false;
    for (let i = 0, j = pts.length - 1; i < pts.length; j = i++) {
        const a = pts[i], b = pts[j];
        if ((a.y > y) !== (b.y > y) && x < ((b.x - a.x) * (y - a.y)) / (b.y - a.y) + a.x) c = !c;
    }
    return c;
}

function distToEdge(pts, x, y) {
    let d = Infinity;
    for (let i = 0, j = pts.length - 1; i < pts.length; j = i++) {
        const a = pts[j], b = pts[i];
        const dx = b.x - a.x, dy = b.y - a.y;
        const t = Math.max(0, Math.min(1, ((x - a.x) * dx + (y - a.y) * dy) / (dx * dx + dy * dy)));
        d = Math.min(d, Math.hypot(a.x + t * dx - x, a.y + t * dy - y));
    }
    return d;
}

function scaled(pts, k) {
    return pts.map((p) => new THREE.Vector2(p.x * k, p.y * k));
}

/* -------------------------------------------------------------------- tray */

const TRAY_TOP = 0.14;
const TARGET_Y = 0.42;

function buildTray(cfg) {
    const g = new THREE.Group();
    const shape = outline(cfg.shape);
    const pts = polygon(shape);
    const metal = mat(cfg.trayColor, { metalness: 1, roughness: 0.28, bumpMap: trayEngraving(), bumpScale: 1.4 });
    const rim = gold(cfg.rimColor);

    // Plate: a bevelled slab whose top face carries the engraving.
    const plateGeo = new THREE.ExtrudeGeometry(shape, { depth: 0.04, bevelEnabled: true, bevelThickness: 0.02, bevelSize: 0.03, bevelSegments: 4, curveSegments: 96 });
    const uv = plateGeo.attributes.uv;
    for (let i = 0; i < uv.count; i++) uv.setXY(i, uv.getX(i) * 0.5 + 0.5, uv.getY(i) * 0.5 + 0.5);
    plateGeo.rotateX(-Math.PI / 2);
    const plate = new THREE.Mesh(plateGeo, metal);
    plate.position.y = TRAY_TOP - 0.06;
    plate.castShadow = plate.receiveShadow = true;
    g.add(plate);

    // Raised lip: the outline with a slightly smaller copy cut out of it.
    const lip = new THREE.Shape(scaled(pts, 1.0));
    lip.holes.push(new THREE.Path(scaled(pts, 0.93).reverse()));
    const lipGeo = new THREE.ExtrudeGeometry(lip, { depth: 0.07, bevelEnabled: true, bevelThickness: 0.012, bevelSize: 0.012, bevelSegments: 3 });
    lipGeo.rotateX(-Math.PI / 2);
    const lipMesh = new THREE.Mesh(lipGeo, rim);
    lipMesh.position.y = TRAY_TOP - 0.02;
    lipMesh.castShadow = true;
    g.add(lipMesh);

    // Beaded rope along the top of the lip.
    const curve = new THREE.CatmullRomCurve3(scaled(pts, 0.965).map((p) => new THREE.Vector3(p.x, TRAY_TOP + 0.065, -p.y)), true);
    const bead = new THREE.Mesh(new THREE.TubeGeometry(curve, 360, 0.022, 10, true), rim);
    bead.castShadow = true;
    g.add(bead);

    // Satin ribbon round the outside of the lip, in the chosen colour.
    const band = new THREE.CatmullRomCurve3(scaled(pts, 1.035).map((p) => new THREE.Vector3(p.x, TRAY_TOP + 0.015, -p.y)), true);
    const ribbon = new THREE.Mesh(new THREE.TubeGeometry(band, 360, 0.028, 4, true), satin(cfg.ribbonColor));
    ribbon.scale.y = 1;
    ribbon.userData.ribbon = true;
    g.add(ribbon);

    // A bow on the front of the tray.
    const front = pts.reduce((best, p) => (p.y < best.y ? p : best), pts[0]);
    const b = bow(cfg.ribbonColor, 1.5);
    b.position.set(front.x, TRAY_TOP + 0.02, -front.y * 1.06 + 0.02);
    b.userData.ribbon = true;
    g.add(b);

    // Feet.
    const footGeo = new THREE.LatheGeometry([[0, 0], [0.07, 0], [0.075, 0.02], [0.04, 0.05], [0.05, 0.075], [0, 0.08]].map(([x, y]) => new THREE.Vector2(x, y)), 24);
    const feet = cfg.shape === 'rect' || cfg.shape === 'oval' ? 6 : 4;
    const inner = scaled(pts, 0.7);
    for (let i = 0; i < feet; i++) {
        const p = inner[Math.floor(((i + 0.5) / feet) * inner.length)];
        const f = new THREE.Mesh(footGeo, rim);
        f.position.set(p.x, 0, -p.y);
        f.castShadow = true;
        g.add(f);
    }

    return { group: g, pts };
}

/* ------------------------------------------------------------------- cover */

function buildCover(cfg, pts) {
    if (cfg.coverType === 'none') return null;
    const g = new THREE.Group();
    const H = cfg.coverType === 'glass' ? 0.95 : 1.15;
    const rings = 28;
    const ring = polygon(new THREE.Shape(pts), 96);
    const n = ring.length;
    const positions = [];
    const uvs = [];
    const idx = [];
    for (let r = 0; r <= rings; r++) {
        const t = r / rings;
        let k, y;
        if (cfg.coverType === 'glass') {
            k = Math.cos((t * Math.PI) / 2) * 0.97;
            y = TRAY_TOP + 0.07 + Math.sin((t * Math.PI) / 2) * H;
        } else {
            // Tulle falls from a gathered knot at the top and flares at the rim.
            k = 1.02 * Math.pow(1 - t, 0.75) + 0.035 * t;
            y = TRAY_TOP + 0.05 + H * (1 - Math.pow(1 - t, 2.2));
        }
        for (let i = 0; i <= n; i++) {
            const p = ring[i % n];
            const fold = cfg.coverType === 'tulle' ? 1 + Math.sin((i / n) * Math.PI * 2 * 18) * 0.025 * (1 - t) : 1;
            positions.push(p.x * k * fold, y, -p.y * k * fold);
            uvs.push(i / n, t);
        }
    }
    for (let r = 0; r < rings; r++) {
        for (let i = 0; i < n; i++) {
            const a = r * (n + 1) + i, b = a + n + 1;
            idx.push(a, b, a + 1, b, b + 1, a + 1);
        }
    }
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(idx);
    geo.computeVertexNormals();

    const material = cfg.coverType === 'glass'
        ? new THREE.MeshPhysicalMaterial({ color: '#ffffff', roughness: 0.03, transmission: 1, thickness: 0.04, ior: 1.5, transparent: true, side: THREE.DoubleSide, envMapIntensity: 1.2 })
        : new THREE.MeshStandardMaterial({ color: cfg.coverColor, roughness: 1, metalness: 0, transparent: true, opacity: 0.8, alphaMap: tulleMesh(), side: THREE.DoubleSide, depthWrite: false, emissive: new THREE.Color(cfg.coverColor), emissiveIntensity: 0.18 });
    const dome = new THREE.Mesh(geo, material);
    dome.renderOrder = 2;
    g.add(dome);

    const topY = TRAY_TOP + 0.05 + H;
    if (cfg.coverType === 'glass') {
        const knob = new THREE.Mesh(new THREE.SphereGeometry(0.06, 24, 16), gold(cfg.rimColor));
        knob.position.y = topY + 0.1;
        g.add(knob);
        const b = bow(cfg.ribbonColor, 1.4);
        b.position.set(0, topY + 0.05, 0.05);
        b.userData.ribbon = true;
        g.add(b);
    } else {
        const knot = new THREE.Mesh(new THREE.SphereGeometry(0.05, 20, 14), satin(cfg.ribbonColor));
        knot.position.y = topY - 0.02;
        knot.userData.ribbon = true;
        g.add(knot);
        const b = bow(cfg.ribbonColor, 2.2);
        b.position.set(0, topY + 0.02, 0.04);
        b.userData.ribbon = true;
        g.add(b);
        // Ribbons falling from the knot down the tulle.
        const domeAt = (u) => [1.02 * Math.pow(1 - u, 0.75) + 0.035 * u, TRAY_TOP + 0.05 + H * (1 - Math.pow(1 - u, 2.2))];
        for (let i = 0; i < 4; i++) {
            const p = ring[Math.floor(((i + 0.5) / 4) * n)];
            const line = [];
            for (let s = 0; s <= 16; s++) {
                const u = 1 - (s / 16) * 0.6;
                const [k, y] = domeAt(u);
                line.push(new THREE.Vector3(p.x * k * 1.03, y + 0.01, -p.y * k * 1.03));
            }
            const tail = new THREE.Mesh(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(line), 32, 0.02, 6), satin(cfg.ribbonColor));
            tail.scale.set(1, 1, 1);
            tail.userData.ribbon = true;
            g.add(tail);
        }
    }
    g.userData.top = topY;
    return g;
}

/* ------------------------------------------------------------------ layout */

function rng(seed) {
    return () => {
        seed |= 0;
        seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/**
 * Places items on the tray: the tallest central piece in the middle, the
 * rest packed from the centre outwards without overlapping or hanging over
 * the lip. When they do not fit, everything is made a little smaller.
 */
function layout(entries, pts) {
    const usable = scaled(pts, 0.9);
    const ordered = [...entries].sort((a, b) => {
        const ca = CENTRAL.includes(a.model) ? 1 : 0, cb = CENTRAL.includes(b.model) ? 1 : 0;
        return cb - ca || b.r - a.r;
    });
    const candidates = [];
    for (let i = 0; i < 1400; i++) {
        const r = Math.sqrt(i / 1400) * 1.3;
        const a = i * 2.39996;
        const x = Math.cos(a) * r, y = Math.sin(a) * r;
        if (inside(usable, x, y)) candidates.push([x, y, distToEdge(usable, x, y), Math.hypot(x, y)]);
    }

    for (let scale = 1; scale > 0.35; scale *= 0.88) {
        const placed = [];
        let ok = true;
        for (const e of ordered) {
            const r = e.r * scale * 0.92;
            let best = null;
            for (const [x, y, edge, d] of candidates) {
                if (edge < r) continue;
                let free = true;
                for (const p of placed) {
                    if (Math.hypot(p.x - x, p.y - y) < p.r + r + 0.012) {
                        free = false;
                        break;
                    }
                }
                if (free) {
                    // Central pieces go to the middle; the rest prefer a
                    // ring around it, which reads better than a heap.
                    const score = CENTRAL.includes(e.model) && !placed.length ? d : Math.abs(d - 0.45) + d * 0.3;
                    if (!best || score < best.score) best = { x, y, score };
                }
            }
            if (!best) {
                ok = false;
                break;
            }
            placed.push({ key: e.key, x: best.x, y: best.y, r });
        }
        if (ok) {
            // Packing from the centre leaves the rim bare; a real xonça is
            // filled edge to edge. Push everything outwards as far as it
            // still stands on the tray — stretching never makes two items
            // touch that did not already.
            if (placed.length >= 4) {
                const fits = (k) => placed.every((p) => {
                    const x = p.x * k, y = p.y * k;
                    return inside(usable, x, y) && distToEdge(usable, x, y) >= p.r * 0.8;
                });
                let lo = 1, hi = Math.min(2.2, 1 + placed.length * 0.08);
                for (let i = 0; i < 14; i++) {
                    const mid = (lo + hi) / 2;
                    fits(mid) ? (lo = mid) : (hi = mid);
                }
                placed.forEach((p) => {
                    p.x *= lo;
                    p.y *= lo;
                });
            }
            return { scale, placed };
        }
    }
    return { scale: 0.35, placed: [] };
}

/* ------------------------------------------------------------------ viewer */

export function createViewer(container, options = {}) {
    const opts = { autoRotate: true, controls: true, shadows: true, ...options };
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: !!opts.preserve });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = opts.shadows;
    renderer.shadowMap.type = THREE.PCFShadowMap;
    container.appendChild(renderer.domElement);
    renderer.domElement.style.display = 'block';
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    scene.environmentIntensity = 0.9;

    const camera = new THREE.PerspectiveCamera(32, 1, 0.05, 50);
    camera.position.set(0, 2.2, 3.6);

    const key = new THREE.DirectionalLight('#fff3e0', 2.2);
    key.position.set(2.5, 5, 3);
    key.castShadow = true;
    key.shadow.mapSize.set(2048, 2048);
    key.shadow.camera.left = key.shadow.camera.bottom = -2;
    key.shadow.camera.right = key.shadow.camera.top = 2;
    key.shadow.bias = -0.0004;
    key.shadow.radius = 4;
    scene.add(key);
    const rimLight = new THREE.DirectionalLight('#ffd9e0', 1.2);
    rimLight.position.set(-3, 2.5, -3);
    scene.add(rimLight);
    scene.add(new THREE.HemisphereLight('#fff8f0', '#6b4a3a', 0.5));
    const candleLight = new THREE.PointLight('#ffb347', 0, 2.5, 2);
    candleLight.position.set(0, 0.8, 0);
    scene.add(candleLight);

    const ground = new THREE.Mesh(new THREE.CircleGeometry(4, 64), new THREE.ShadowMaterial({ opacity: 0.22 }));
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    const root = new THREE.Group();
    scene.add(root);

    let controls = null;
    if (opts.controls) {
        controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.enablePan = false;
        controls.minDistance = 1.6;
        controls.maxDistance = 7;
        controls.minPolarAngle = 0.15;
        controls.maxPolarAngle = Math.PI / 2.05;
        controls.autoRotate = opts.autoRotate;
        controls.autoRotateSpeed = 0.9;
        controls.target.set(0, TARGET_Y, 0);
        controls.addEventListener('start', () => {
            controls.autoRotate = false;
            controls.userMoved = true;
        });
    }

    let tray = null, cover = null, pts = null, cfg = null;
    let catalog = {};
    let fill = [];
    let coverVisible = opts.cover !== false;
    const itemMeshes = new Map();
    const itemsGroup = new THREE.Group();
    root.add(itemsGroup);

    function fit() {
        // Far enough that the whole tray and its cover fit the frame,
        // whatever the shape of the frame is.
        const wide = cfg && (cfg.shape === 'oval' || cfg.shape === 'rect') ? 1.22 : 1.05;
        const v = Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        const h = v * camera.aspect;
        const dist = Math.max((1.3 * wide) / h, 1.3 / v) * (opts.zoom || 1);
        const dir = new THREE.Vector3(0, 0.62, 1).normalize();
        camera.position.copy(dir.multiplyScalar(dist)).add(new THREE.Vector3(0, TARGET_Y, 0));
        if (controls) controls.target.set(0, TARGET_Y, 0);
        camera.lookAt(0, TARGET_Y, 0);
    }

    function disposeGroup(g) {
        g?.traverse((o) => o.geometry?.dispose());
        g?.removeFromParent();
    }

    function setXonca(next) {
        cfg = { shape: 'round', trayColor: '#c9a14a', rimColor: '#e8c77a', coverType: 'tulle', coverColor: '#fff4f4', ribbonColor: '#b3122e', ...next };
        disposeGroup(tray);
        disposeGroup(cover);
        const built = buildTray(cfg);
        tray = built.group;
        pts = built.pts;
        root.add(tray);
        cover = buildCover(cfg, pts);
        if (cover) {
            cover.visible = coverVisible;
            root.add(cover);
        }
        fit();
        relayout(true);
    }

    function setRibbon(color) {
        if (!cfg) return;
        cfg.ribbonColor = color;
        const m = satin(color);
        root.traverse((o) => {
            let p = o, isRibbon = false;
            while (p) {
                if (p.userData.ribbon) {
                    isRibbon = true;
                    break;
                }
                p = p.parent;
            }
            if (isRibbon && o.isMesh) o.material = m;
        });
    }

    function showCover(v) {
        coverVisible = v;
        if (cover) cover.visible = v;
    }

    /** @param list [{id, qty}] with ids from the catalogue */
    function setFill(list, items) {
        if (items) catalog = items;
        fill = list || [];
        relayout(false);
    }

    function relayout(instant) {
        if (!pts) return;
        const entries = [];
        for (const { id, qty } of fill) {
            const item = catalog[id];
            if (!item) continue;
            for (let n = 0; n < qty; n++) {
                const k = `${id}#${n}`;
                let m = itemMeshes.get(k);
                if (!m) {
                    m = makeItem(item);
                    m.userData.base = m.scale.x;
                    m.userData.model = item.model;
                    m.userData.grow = instant ? 1 : 0;
                    const rand = rng(id * 97 + n * 13);
                    m.rotation.y = (rand() - 0.5) * 1.2;
                    itemMeshes.set(k, m);
                    itemsGroup.add(m);
                }
                entries.push({ key: k, model: item.model, r: m.userData.r });
            }
        }
        const alive = new Set(entries.map((e) => e.key));
        for (const [k, m] of itemMeshes) {
            if (!alive.has(k)) {
                m.userData.dying = true;
                itemMeshes.delete(k);
            }
        }
        const { scale, placed } = layout(entries, pts);
        for (const p of placed) {
            const m = itemMeshes.get(p.key);
            m.userData.target = new THREE.Vector3(p.x, TRAY_TOP + 0.01, -p.y);
            m.userData.scale = scale * (m.userData.base ?? 1);
            if (instant || !m.userData.placed) {
                m.position.copy(m.userData.target);
                if (!instant) m.position.y += 0.6;
            }
            m.userData.placed = true;
            // The mirror looks at the customer.
            if (m.userData.h > 0.3 && entries.find((e) => e.key === p.key)?.model === 'mirror') m.rotation.y = 0;
        }
        const flames = [...itemMeshes.values()].some(hasFlame);
        candleLight.intensity = flames ? 1.2 : 0;
    }

    function hasFlame(m) {
        let f = false;
        m.traverse((o) => {
            if (o.userData.flame && o.parent?.visible !== false) f = true;
        });
        return f;
    }

    function resize() {
        const w = container.clientWidth || 300, h = container.clientHeight || 300;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        if (cfg && !(controls && controls.userMoved)) fit();
    }
    const ro = new ResizeObserver(resize);
    ro.observe(container);
    resize();

    let visible = true;
    const io = new IntersectionObserver(([e]) => (visible = e.isIntersecting));
    io.observe(container);

    let raf = 0;
    let last = performance.now();
    function tick(now) {
        raf = requestAnimationFrame(tick);
        if (!visible || document.hidden) return;
        const dt = Math.min(0.05, (now - last) / 1000);
        last = now;
        for (const m of itemsGroup.children.slice()) {
            const u = m.userData;
            if (u.dying) {
                m.scale.multiplyScalar(0.82);
                if (m.scale.x < 0.02) {
                    disposeGroup(m);
                }
                continue;
            }
            u.grow = Math.min(1, (u.grow ?? 1) + dt * 3.5);
            const g = Math.max(0, u.grow);
            const ease = 1 - Math.pow(1 - g, 3);
            const s = (u.scale ?? 1) * (0.05 + 0.95 * ease);
            m.scale.setScalar(s * (1 + Math.sin(g * Math.PI) * 0.08));
            if (u.target && u.grow > 0) m.position.lerp(u.target, 1 - Math.pow(0.0008, dt));
        }
        const t = now / 1000;
        itemsGroup.traverse((o) => {
            if (o.userData.flame) {
                o.scale.set(1 + Math.sin(t * 17 + o.id) * 0.08, 2.1 + Math.sin(t * 23 + o.id * 3) * 0.25, 1);
            }
        });
        if (candleLight.intensity) candleLight.intensity = 1.1 + Math.sin(t * 13) * 0.15;
        controls?.update();
        renderer.render(scene, camera);
    }
    raf = requestAnimationFrame(tick);

    function clearItems() {
        for (const m of [...itemsGroup.children]) disposeGroup(m);
        itemMeshes.clear();
    }

    /** A different xonça with its own filling, drawn from scratch. */
    function load(next, list, items, animate = false) {
        if (items) catalog = items;
        clearItems();
        fill = list || [];
        setXonca(next);
        if (animate) {
            let i = 0;
            for (const m of itemMeshes.values()) {
                m.userData.grow = -i++ * 0.12;
                m.position.y += 0.7;
            }
        }
    }

    /** One item alone, filling the frame — the builder's little pictures. */
    function showItem(item) {
        clearItems();
        disposeGroup(tray);
        disposeGroup(cover);
        tray = cover = pts = null;
        const m = makeItem(item);
        m.userData.scale = m.scale.x;
        m.userData.grow = 1;
        itemsGroup.add(m);
        const { h, r } = m.userData;
        const target = new THREE.Vector3(0, h * 0.45, 0);
        const size = Math.max(h, r * 2.1);
        const dist = (size * 0.62) / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        camera.position.copy(new THREE.Vector3(0, 0.55, 1).normalize().multiplyScalar(dist)).add(target);
        camera.lookAt(target);
        candleLight.intensity = 0;
    }

    return {
        showItem,
        load,
        setXonca,
        setFill,
        setRibbon,
        showCover,
        get cover() {
            return coverVisible;
        },
        resetView() {
            if (controls) controls.userMoved = false;
            fit();
            if (controls) controls.autoRotate = opts.autoRotate;
        },
        renderOnce() {
            controls?.update();
            for (const m of itemsGroup.children) {
                if (m.userData.target) m.position.copy(m.userData.target);
                m.scale.setScalar(m.userData.scale ?? 1);
            }
            renderer.render(scene, camera);
        },
        snapshot(type = 'image/webp') {
            this.renderOnce();
            return renderer.domElement.toDataURL(type, 0.9);
        },
        setAngle(azimuth, polar = 1.05) {
            const d = camera.position.distanceTo(new THREE.Vector3(0, TARGET_Y, 0));
            camera.position.setFromSphericalCoords(d, polar, azimuth).add(new THREE.Vector3(0, TARGET_Y, 0));
            camera.lookAt(0, TARGET_Y, 0);
        },
        dispose() {
            cancelAnimationFrame(raf);
            ro.disconnect();
            io.disconnect();
            controls?.dispose();
            renderer.dispose();
            renderer.domElement.remove();
        },
    };
}
