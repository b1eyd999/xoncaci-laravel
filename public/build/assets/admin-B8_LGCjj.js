import{c as b}from"./spin-CxFcjfy2.js";const m=`
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
`,g=h=>new Promise(t=>setTimeout(t,h));function l(h){return new Promise(t=>h.toBlob(t,"image/webp",.86))}function c(h,t,s){const a=Math.min(1,1400/t),e=document.createElement("canvas");return e.width=Math.round(t*a),e.height=Math.round(s*a),e.getContext("2d").drawImage(h,0,0,e.width,e.height),e}class f extends HTMLElement{connectedCallback(){if(this.ready)return;this.ready=!0;const t=JSON.parse(this.dataset.existing||"[]");this.innerHTML=`<style>${m}</style>
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
                ${t.length?'<button type="button" class="ghost" data-remove>360°-ni sil</button>':""}
            </div>
        </div>`,this.$=s=>this.querySelector(s),this.blobs=[],t.length&&(this.showSpin(t),this.$("[data-info]").textContent=`Saytda indi ${t.length} kadrlıq 360° var.`),this.$("[data-pick]").onclick=()=>this.$("[data-file]").click(),this.$("[data-file]").onchange=s=>this.pick([...s.target.files]),this.$("[data-cut]").onclick=()=>this.cut(),this.$("[data-save]").onclick=()=>this.save(),this.$("[data-remove]")?.addEventListener("click",()=>this.remove())}showSpin(t){this.spin?.destroy(),this.spin=b(this.$("[data-stage]"),t)}progress(t){this.$("[data-progress]").style.width=`${Math.round(t*100)}%`}async pick(t){this.blobs=[],this.$("[data-save]").disabled=!0;const s=t.find(a=>a.type.startsWith("video/"));if(s){this.mode="video";const a=this.$("video");a.src=URL.createObjectURL(s),await new Promise(o=>a.onloadedmetadata=o),Number.isFinite(a.duration)||(a.currentTime=1e7,await new Promise(o=>a.ondurationchange=()=>Number.isFinite(a.duration)&&o()),a.currentTime=0);const e=a.duration,[i,n]=[this.$("[data-start]"),this.$("[data-end]")];i.max=n.max=e.toFixed(2),i.value=0,n.value=e.toFixed(2),this.$("[data-video]").hidden=!1;const r=async(o,d,p)=>{a.currentTime=Math.min(Number(o.value),e-.05),await new Promise(u=>a.onseeked=u),d.width=480,d.height=Math.round(480*a.videoHeight/a.videoWidth),d.getContext("2d").drawImage(a,0,0,d.width,d.height),this.$(p).textContent=Number(o.value).toFixed(1)};this.showStart=()=>r(i,this.$("[data-a]"),"[data-at]"),this.showEnd=()=>r(n,this.$("[data-b]"),"[data-bt]"),i.oninput=()=>this.showStart(),n.oninput=()=>this.showEnd(),await this.showStart(),await this.showEnd(),this.$("[data-info]").textContent=`Video: ${e.toFixed(1)} san.`}else this.mode="images",this.images=t.filter(a=>a.type.startsWith("image/")).sort((a,e)=>a.name.localeCompare(e.name,void 0,{numeric:!0})),this.$("[data-video]").hidden=!0,this.$("[data-info]").textContent=`${this.images.length} şəkil seçildi (adlarına görə sıralanıb).`;this.$("[data-cut]").disabled=!1}async cut(){if(this.$("[data-cut]").disabled=!0,this.blobs=[],this.mode==="video"){const t=this.$("video"),s=Number(this.$("[data-count]").value),a=Number(this.$("[data-start]").value),e=Number(this.$("[data-end]").value);for(let i=0;i<s;i++)t.currentTime=a+(e-a)*i/s,await new Promise(n=>t.onseeked=n),this.blobs.push(await l(c(t,t.videoWidth,t.videoHeight))),this.progress((i+1)/s)}else for(const[t,s]of this.images.entries()){const a=await createImageBitmap(s);this.blobs.push(await l(c(a,a.width,a.height))),this.progress((t+1)/this.images.length)}this.showSpin(this.blobs.map(t=>URL.createObjectURL(t))),this.$("[data-info]").textContent=`${this.blobs.length} kadr hazırdır. Barmaqla fırladıb yoxlayın, sonra yadda saxlayın.`,this.$("[data-cut]").disabled=!1,this.$("[data-save]").disabled=this.blobs.length<8}async save(){const t=this.$("[data-save]");t.disabled=!0;const s=Math.random().toString(36).slice(2,14).padEnd(10,"x"),a=document.querySelector("meta[name=csrf-token]")?.content,e=4;for(let i=0;i<this.blobs.length;i+=e){const n=new FormData;n.append("token",s),n.append("start",i),this.blobs.slice(i,i+e).forEach((o,d)=>n.append(`frames[${d}]`,o,`${i+d}.webp`)),i+e>=this.blobs.length&&n.append("finish","1");let r=!1;for(let o=0;o<3&&!r;o++)r=!!(await fetch(this.dataset.url,{method:"POST",body:n,headers:{"X-CSRF-TOKEN":a,Accept:"application/json"}}).catch(()=>null))?.ok,r||await g(1500);if(!r){this.$("[data-info]").textContent="Yükləmə alınmadı. İnterneti yoxlayıb yenidən cəhd edin.",t.disabled=!1;return}this.progress(Math.min(1,(i+e)/this.blobs.length))}this.$("[data-info]").textContent="Yadda saxlandı. 360° artıq saytdadır."}async remove(){const t=document.querySelector("meta[name=csrf-token]")?.content;await fetch(this.dataset.url,{method:"DELETE",headers:{"X-CSRF-TOKEN":t,Accept:"application/json"}}),this.spin?.destroy(),this.$("[data-info]").textContent="360° silindi.",this.$("[data-remove]")?.remove()}}customElements.get("xonca-spin-maker")||customElements.define("xonca-spin-maker",f);
