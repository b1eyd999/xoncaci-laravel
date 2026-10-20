import{c as v}from"./spin-CxFcjfy2.js";const f=`
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
`,g=o=>new Promise(t=>setTimeout(t,o));function p(o){return new Promise(t=>o.toBlob(t,"image/webp",.86))}function u(o,t,i){const e=Math.min(1,1400/t),a=document.createElement("canvas");return a.width=Math.round(t*e),a.height=Math.round(i*e),a.getContext("2d").drawImage(o,0,0,a.width,a.height),a}class x extends HTMLElement{connectedCallback(){if(this.ready)return;this.ready=!0;const t=JSON.parse(this.dataset.existing||"[]");this.innerHTML=`<style>${f}</style>
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
        </div>`,this.$=i=>this.querySelector(i),this.blobs=[],t.length&&(this.showSpin(t),this.$("[data-info]").textContent=`Saytda indi ${t.length} kadrlıq 360° var.`),this.$("[data-pick]").onclick=()=>this.$("[data-file]").click(),this.$("[data-file]").onchange=i=>this.pick([...i.target.files]),this.$("[data-cut]").onclick=()=>this.cut(),this.$("[data-save]").onclick=()=>this.save(),this.$("[data-remove]")?.addEventListener("click",()=>this.remove())}showSpin(t){this.spin?.destroy(),this.spin=v(this.$("[data-stage]"),t)}progress(t){this.$("[data-progress]").style.width=`${Math.round(t*100)}%`}async pick(t){this.blobs=[],this.$("[data-save]").disabled=!0;const i=t.find(e=>e.type.startsWith("video/"));if(i){this.mode="video";const e=this.$("video");e.src=URL.createObjectURL(i),await new Promise(d=>e.onloadedmetadata=d),Number.isFinite(e.duration)||(e.currentTime=1e7,await new Promise(d=>e.ondurationchange=()=>Number.isFinite(e.duration)&&d()),e.currentTime=0);const a=e.duration,[s,n]=[this.$("[data-start]"),this.$("[data-end]")];s.max=n.max=a.toFixed(2),s.value=0,n.value=a.toFixed(2),this.$("[data-video]").hidden=!1;const h=async(d,r,m)=>{e.currentTime=Math.min(Number(d.value),a-.05),await new Promise(b=>e.onseeked=b),r.width=480,r.height=Math.round(480*e.videoHeight/e.videoWidth),r.getContext("2d").drawImage(e,0,0,r.width,r.height),this.$(m).textContent=Number(d.value).toFixed(1)};this.showStart=()=>h(s,this.$("[data-a]"),"[data-at]"),this.showEnd=()=>h(n,this.$("[data-b]"),"[data-bt]"),s.oninput=()=>this.showStart(),n.oninput=()=>this.showEnd(),await this.showStart(),await this.showEnd(),this.$("[data-info]").textContent=`Video: ${a.toFixed(1)} san.`}else this.mode="images",this.images=t.filter(e=>e.type.startsWith("image/")).sort((e,a)=>e.name.localeCompare(a.name,void 0,{numeric:!0})),this.$("[data-video]").hidden=!0,this.$("[data-info]").textContent=`${this.images.length} şəkil seçildi (adlarına görə sıralanıb).`;this.$("[data-cut]").disabled=!1}async cut(){if(this.$("[data-cut]").disabled=!0,this.blobs=[],this.mode==="video"){const t=this.$("video"),i=Number(this.$("[data-count]").value),e=Number(this.$("[data-start]").value),a=Number(this.$("[data-end]").value);for(let s=0;s<i;s++)t.currentTime=e+(a-e)*s/i,await new Promise(n=>t.onseeked=n),this.blobs.push(await p(u(t,t.videoWidth,t.videoHeight))),this.progress((s+1)/i)}else for(const[t,i]of this.images.entries()){const e=await createImageBitmap(i);this.blobs.push(await p(u(e,e.width,e.height))),this.progress((t+1)/this.images.length)}this.showSpin(this.blobs.map(t=>URL.createObjectURL(t))),this.$("[data-info]").textContent=`${this.blobs.length} kadr hazırdır. Barmaqla fırladıb yoxlayın, sonra yadda saxlayın.`,this.$("[data-cut]").disabled=!1,this.$("[data-save]").disabled=this.blobs.length<8}async save(){const t=this.$("[data-save]");t.disabled=!0;const i=Math.random().toString(36).slice(2,14).padEnd(10,"x"),e=document.querySelector("meta[name=csrf-token]")?.content,a=4;for(let s=0;s<this.blobs.length;s+=a){const n=new FormData;n.append("token",i),n.append("start",s),this.blobs.slice(s,s+a).forEach((d,r)=>n.append(`frames[${r}]`,d,`${s+r}.webp`)),s+a>=this.blobs.length&&n.append("finish","1");let h=!1;for(let d=0;d<3&&!h;d++)h=!!(await fetch(this.dataset.url,{method:"POST",body:n,headers:{"X-CSRF-TOKEN":e,Accept:"application/json"}}).catch(()=>null))?.ok,h||await g(1500);if(!h){this.$("[data-info]").textContent="Yükləmə alınmadı. İnterneti yoxlayıb yenidən cəhd edin.",t.disabled=!1;return}this.progress(Math.min(1,(s+a)/this.blobs.length))}this.$("[data-info]").textContent="Yadda saxlandı. 360° artıq saytdadır."}async remove(){const t=document.querySelector("meta[name=csrf-token]")?.content;await fetch(this.dataset.url,{method:"DELETE",headers:{"X-CSRF-TOKEN":t,Accept:"application/json"}}),this.spin?.destroy(),this.$("[data-info]").textContent="360° silindi.",this.$("[data-remove]")?.remove()}}customElements.get("xonca-spin-maker")||customElements.define("xonca-spin-maker",x);const w=`
.xv{font:14px/1.5 inherit;display:grid;gap:14px}
.xv .row{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.xv button{border-radius:8px;padding:8px 14px;font-weight:600;background:#8a1c2b;color:#fff}
.xv button.ghost{background:transparent;color:inherit;border:1px solid #8884}
.xv button:disabled{opacity:.4}
.xv video{width:100%;max-width:520px;aspect-ratio:4/5;object-fit:cover;background:#f3ece1;border-radius:12px}
.xv input[type=range]{width:100%;max-width:520px}
.xv .bar{height:6px;background:#8883;border-radius:3px;overflow:hidden;max-width:520px}.xv .bar i{display:block;height:100%;background:#8a1c2b;width:0}
.xv .muted{opacity:.7;font-size:13px}
`,c=1536*1024,y=300*1024*1024,$=o=>new Promise(t=>setTimeout(t,o)),l=o=>(o/1048576).toFixed(1);class k extends HTMLElement{connectedCallback(){if(this.ready)return;this.ready=!0;const{existing:t,poster:i}=this.dataset;this.innerHTML=`<style>${w}</style>
        <div class="xv">
            <div class="row">
                <label><button type="button" class="ghost" data-pick>Video seç</button>
                <input type="file" accept="video/mp4,video/quicktime,video/webm,.mp4,.mov,.webm" hidden data-file></label>
                <span class="muted" data-info></span>
            </div>
            <video muted loop playsinline controls preload="metadata" ${t?`src="${t}" poster="${i||""}"`:"hidden"}></video>
            <div data-cover hidden>
                <p class="muted">Video başlamamış görünən kadr (üz qabığı):</p>
                <input type="range" data-at step="0.01" min="0" value="0">
            </div>
            <div class="bar"><i data-progress></i></div>
            <div class="row">
                <button type="button" data-save disabled>Saytda yadda saxla</button>
                ${t?'<button type="button" class="ghost" data-remove>Videonu sil</button>':""}
            </div>
        </div>`,this.$=e=>this.querySelector(e),this.video=this.$("video"),t&&this.info("Saytda indi bu video var."),this.$("[data-pick]").onclick=()=>this.$("[data-file]").click(),this.$("[data-file]").onchange=e=>e.target.files[0]&&this.pick(e.target.files[0]),this.$("[data-at]").oninput=e=>this.video.currentTime=Number(e.target.value),this.$("[data-save]").onclick=()=>this.save(),this.$("[data-remove]")?.addEventListener("click",()=>this.remove())}info(t){this.$("[data-info]").textContent=t}progress(t){this.$("[data-progress]").style.width=`${Math.round(t*100)}%`}async pick(t){if(this.$("[data-save]").disabled=!0,this.progress(0),t.size>y)return this.info(`Fayl ${l(t.size)} MB-dır. 300 MB-dan kiçik olmalıdır: videonu qısaldın.`);this.file=t;const i=this.video;if(i.hidden=!1,i.removeAttribute("poster"),i.src=URL.createObjectURL(t),!await new Promise(s=>{i.onloadeddata=()=>s(i.videoWidth>0),i.onerror=()=>s(!1)}))return this.info("Bu brauzer videonu göstərə bilmir. iPhone: Parametrlər, Kamera, Formatlar, «Ən uyğun» (Most Compatible) seçib yenidən çəkin.");const a=this.$("[data-at]");a.max=i.duration.toFixed(2),a.value=Math.min(1,i.duration/2).toFixed(2),i.currentTime=Number(a.value),this.$("[data-cover]").hidden=!1,this.info(`${l(t.size)} MB, ${i.duration.toFixed(0)} san.`),this.$("[data-save]").disabled=!1}async poster(){const t=this.video;t.seeking&&await new Promise(a=>t.onseeked=a);const i=Math.min(1,1400/t.videoWidth),e=document.createElement("canvas");return e.width=Math.round(t.videoWidth*i),e.height=Math.round(t.videoHeight*i),e.getContext("2d").drawImage(t,0,0,e.width,e.height),new Promise(a=>e.toBlob(a,"image/webp",.86))}async send(t){const i=document.querySelector("meta[name=csrf-token]")?.content;for(let e=0;e<4;e++){const a=await fetch(this.dataset.url,{method:"POST",body:t,headers:{"X-CSRF-TOKEN":i,Accept:"application/json"}}).catch(()=>null);if(a?.ok)return a.json();if(a&&a.status<500&&a.status!==429)return null;await $(2e3*(e+1))}return null}async save(){const t=this.$("[data-save]");t.disabled=!0,this.video.pause();const i=await this.poster(),e=Math.random().toString(36).slice(2,14).padEnd(10,"x"),a=this.file.size;for(let s=0;s<a;s+=c){const n=new FormData;if(n.append("token",e),n.append("offset",s),n.append("chunk",this.file.slice(s,s+c),"part.bin"),s+c>=a&&(n.append("finish","1"),n.append("poster",i,"poster.webp")),!await this.send(n)){this.info("Yükləmə alınmadı. İnterneti yoxlayıb yenidən «Saytda yadda saxla» basın."),t.disabled=!1;return}this.progress(Math.min(1,(s+c)/a)),this.info(`Yüklənir: ${l(Math.min(a,s+c))} / ${l(a)} MB`)}this.info("Yadda saxlandı. Video artıq saytdadır.")}async remove(){const t=document.querySelector("meta[name=csrf-token]")?.content;await fetch(this.dataset.url,{method:"DELETE",headers:{"X-CSRF-TOKEN":t,Accept:"application/json"}}),this.video.removeAttribute("src"),this.video.hidden=!0,this.info("Video silindi."),this.$("[data-remove]")?.remove()}}customElements.get("xonca-video-maker")||customElements.define("xonca-video-maker",k);
