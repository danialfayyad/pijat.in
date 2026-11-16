@props([
    'code' => '',
    'name' => '',
    'class' => 'w-14 h-10',
])

@php
    static $svgCache = [];

    $c = strtolower(trim((string) $code));
    // Normalisasi kode VA dan varian (contoh: BCA_VA, BCA_SYR -> bca)
    $clean = preg_replace('/(_va|_syr)$/', '', $c);

    $logoMap = [
        'qris' => 'qris.svg',
        'bca' => 'bca.svg',
        'mandiri' => 'mandiri.svg',
        'bri' => 'bri.svg',
        'bni' => 'bni.svg',
        'bsi' => 'bsi.svg',
        'cimb' => 'cimb.svg',
        'cimb-niaga' => 'cimb.svg',
        'cimb_niaga' => 'cimb.svg',
        'permata' => 'permata.svg',
        'jago' => 'jago.svg',
        'seabank' => 'seabank.svg',
        'danamon' => 'danamon.svg',
        'btn' => 'btn.svg',
        'gopay' => 'gopay.svg',
        'dana' => 'dana.svg',
        'ovo' => 'ovo.svg',
        'shopeepay' => 'shopeepay.svg',
        'allo' => 'allo.svg',
        'maybank' => 'maybank.svg',
        'mega' => 'mega.svg',
        'ocbc' => 'ocbc.svg',
        'panin' => 'panin.svg',
        'sinarmas' => 'sinarmas.svg',
    ];

    $filename = $logoMap[$clean] ?? null;
    $rawSvg = null;

    if ($filename) {
        if (! array_key_exists($filename, $svgCache)) {
            $svgPath = public_path('images/payments/' . $filename);
            if (file_exists($svgPath)) {
                $svgCache[$filename] = file_get_contents($svgPath);
            } else {
                $svgCache[$filename] = false;
            }
        }
        $rawSvg = $svgCache[$filename] ?: null;
    }

    $inlineSvg = null;
    if ($rawSvg) {
        $uniq = uniqid('p_');
        // Pastikan atribut SVG standar & jadikan clip-path ID unik agar tidak konflik antar kartu di satu halaman
        $inlineSvg = preg_replace('/<svg/i', '<svg class="w-full h-full object-contain pointer-events-none select-none" xmlns="http://www.w3.org/2000/svg"', $rawSvg, 1);
        $inlineSvg = preg_replace('/url\(#([^)]+)\)/i', 'url(#$1_' . $uniq . ')', $inlineSvg);
        $inlineSvg = preg_replace('/<clipPath id=(["\'])([^"\']+)\1/i', '<clipPath id=$1$2_' . $uniq . '$1', $inlineSvg);
    }
@endphp

<div {{ $attributes->merge(['class' => 'shrink-0 rounded-xl bg-white border border-slate-200/90 p-1.5 flex items-center justify-center overflow-hidden shadow-2xs ' . $class]) }}>
    @if ($inlineSvg)
        {!! $inlineSvg !!}
    @else
        <!-- Fallback Monogram Bank Card -->
        <div class="w-full h-full rounded-lg bg-slate-800 text-white flex flex-col items-center justify-center p-0.5">
            <span class="text-[9px] font-black tracking-wider leading-none truncate max-w-full px-0.5">{{ strtoupper(substr($clean, 0, 4)) }}</span>
            <span class="text-[7px] text-slate-300 font-medium tracking-tight mt-0.5">BANK</span>
        </div>
    @endif
</div>
