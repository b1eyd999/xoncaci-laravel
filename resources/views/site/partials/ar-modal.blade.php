{{-- 3D / AR: on a phone the camera puts the object on the floor or a table at its real size;
     on a computer it can be turned with the mouse and a QR code carries it to the phone. --}}
<div data-ar-modal data-glb="{{ $glb }}" data-usdz="{{ $usdz }}" data-size="{{ $size }}" data-name="{{ $name }}" data-url="{{ $url }}"
     class="fixed inset-0 z-[60] hidden bg-onyx text-ivory" role="dialog" aria-modal="true" aria-label="{{ $name }} 3D">
    <div class="flex h-full flex-col lg:flex-row">
        <div class="relative min-h-0 flex-1" data-ar-stage>
            <div data-ar-loading class="caps absolute inset-0 grid place-items-center !text-[10px] text-ivory/40">{{ __('3D model yüklənir…') }}</div>
        </div>
        <aside class="flex shrink-0 flex-col justify-between bg-onyx-2 p-6 sm:p-8 lg:w-[26rem] lg:p-12">
            <div>
                <div class="caps !text-[10px] text-gold-soft">{{ __('Artırılmış reallıq') }}</div>
                <h2 class="mt-4 font-display text-4xl leading-tight">{{ $name }} <em class="text-gold-soft">{{ __('evinizdə') }}</em></h2>
                <p class="mt-5 text-sm leading-relaxed text-ivory/65 max-lg:hidden">{{ __('Telefonu bu koda tutun. Kamera açılacaq və xonçanı masanıza və ya döşəməyə real ölçüdə (:size sm) qoya biləcəksiniz. Ətrafında gəzin, yaxınlaşın, xonça yerində qalır.', ['size' => $size]) }}</p>
                <p class="mt-4 text-sm leading-relaxed text-ivory/65 lg:hidden">{{ __('Düyməni basın və telefonu yavaşca döşəməyə və ya masaya tutun. Xonça görünəndə barmağınızla yerini dəyişə, çevirə bilərsiniz. Ətrafında gəzin, xonça yerində qalır.') }}</p>
            </div>
            <div class="mt-8">
                <div class="hidden lg:block">
                    <div data-ar-qr class="size-44 bg-ivory p-3"></div>
                    <p class="caps mt-4 !text-[9px] text-ivory/45">{{ __('Telefonun kamerası ilə skan edin') }}</p>
                </div>
                <button type="button" data-ar-camera class="btn btn-gold w-full lg:hidden">{{ __('Kameranı aç') }}</button>
                <p data-ar-unsupported class="mt-4 hidden text-xs text-ivory/55 lg:!hidden">{{ __('Bu cihaz kameralı baxışı dəstəkləmir. 3D modeli barmaqla fırladıb baxa bilərsiniz.') }}</p>
                <button type="button" data-ar-close class="link-line mt-8 text-ivory/65">{{ __('Bağla') }}</button>
            </div>
        </aside>
    </div>
</div>
