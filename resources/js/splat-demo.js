/*
 * Gaussian splat viewer: a real object captured from a phone video, turned
 * by finger. The demo page shows a public sample scan until the owner's own
 * xonça video has been processed.
 */
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { SparkRenderer, SplatMesh } from '@sparkjsdev/spark';

export async function mountSplat(box) {
    const renderer = new THREE.WebGLRenderer({ antialias: false, alpha: true });
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    box.appendChild(renderer.domElement);
    renderer.domElement.style.cssText = 'display:block;width:100%;height:100%;touch-action:none';

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.01, 200);
    scene.add(new SparkRenderer({ renderer }));

    const splat = new SplatMesh({ url: box.dataset.splat });
    // .splat files from the original paper's tools come upside down.
    if (box.dataset.flip === '1') splat.quaternion.set(1, 0, 0, 0);
    scene.add(splat);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.enablePan = false;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.8;
    controls.addEventListener('start', () => (controls.autoRotate = false));

    const size = () => {
        const w = box.clientWidth, h = box.clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    new ResizeObserver(size).observe(box);
    size();

    const [x, y, z] = (box.dataset.target || '0,0,0').split(',').map(Number);
    const dist = Number(box.dataset.distance || 3);
    controls.target.set(x, y, z);
    camera.position.set(x, y + dist * 0.35, z + dist);
    controls.minDistance = dist * 0.35;
    controls.maxDistance = dist * 2.2;

    renderer.setAnimationLoop(() => {
        controls.update();
        renderer.render(scene, camera);
    });

    await splat.initialized;
    box.querySelector('[data-splat-loading]')?.remove();

    // Aim at the object itself, wherever the capture put it.
    if (!box.dataset.target) {
        splat.updateMatrixWorld(true);
        const bb = splat.getBoundingBox().clone().applyMatrix4(splat.matrixWorld);
        const c = bb.getCenter(new THREE.Vector3());
        const r = bb.getSize(new THREE.Vector3()).length() / 2;
        const d = (box.dataset.distance ? Number(box.dataset.distance) : 1) * r / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        controls.target.copy(c);
        camera.position.set(c.x, c.y + d * 0.3, c.z + d);
        controls.minDistance = d * 0.3;
        controls.maxDistance = d * 2;
    }
    return { splat, camera, controls };
}

document.querySelectorAll('[data-splat]').forEach((el) => mountSplat(el).then((v) => (window.__splat = v)));
