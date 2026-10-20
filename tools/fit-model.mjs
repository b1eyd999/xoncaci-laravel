// node fit.mjs in.glb out.glb <height_m>
// Real-world size and a floor pivot, baked into the file itself, so every
// AR viewer (Scene Viewer, WebXR, Quick Look) stands it on the surface.
import { NodeIO } from '@gltf-transform/core';
import { ALL_EXTENSIONS } from '@gltf-transform/extensions';
import { center, getBounds } from '@gltf-transform/functions';
const [inp, out, h] = process.argv.slice(2);
const io = new NodeIO().registerExtensions(ALL_EXTENSIONS);
const doc = await io.read(inp);
const scene = doc.getRoot().getDefaultScene() || doc.getRoot().listScenes()[0];
let b = getBounds(scene);
const k = Number(h) / (b.max[1] - b.min[1]);
const wrap = doc.createNode('fit').setScale([k, k, k]);
for (const n of scene.listChildren()) { scene.removeChild(n); wrap.addChild(n); }
scene.addChild(wrap);
await doc.transform(center({ pivot: 'below' }));
b = getBounds(scene);
console.log('size m:', (b.max[0]-b.min[0]).toFixed(3), (b.max[1]-b.min[1]).toFixed(3), (b.max[2]-b.min[2]).toFixed(3), 'min y', b.min[1].toFixed(4));
await io.write(out, doc);
