@props([
    'name' => 'evidence_photos[]',
    'id' => null,
    'label' => 'Foto Bukti (Opsional)',
    'required' => false,
    'multiple' => true,
    'maxFiles' => 3,
    'maxDim' => 1280,
    'quality' => 0.8,
    'accept' => 'image/jpeg,image/png,image/webp,image/jpg',
    'helpText' => null,
])

@php
    $uniqueId = $id ?? 'comp_' . md5($name . rand());
@endphp

<div x-data="imageUploadCompressor({
        maxFiles: {{ $multiple ? $maxFiles : 1 }},
        maxDim: {{ $maxDim }},
        quality: {{ $quality }},
        multiple: {{ $multiple ? 'true' : 'false' }}
    })"
    class="space-y-2">

    <!-- Label & Counter -->
    <div class="flex items-center justify-between">
        <label for="{{ $uniqueId }}_picker" class="block text-xs font-semibold text-slate-700">
            {{ $label }}
            @if ($required)
                <span class="text-rose-600">*</span>
            @endif
        </label>
        @if ($multiple)
            <span class="text-[11px] font-medium text-slate-400">
                <span x-text="files.length" class="font-bold text-slate-700">0</span>/{{ $maxFiles }} foto
            </span>
        @endif
    </div>

    <!-- Hidden Real Form Input that receives compressed File objects -->
    <input type="file" 
           name="{{ $name }}" 
           x-ref="realInput"
           {{ $multiple ? 'multiple' : '' }}
           {{ $required ? 'required' : '' }}
           class="hidden" 
           tabindex="-1">

    <!-- Pickers Container -->
    <div class="space-y-2.5">
        <!-- Dropzone / Picker Box (Visible when slots available) -->
        <div x-show="!multiple ? files.length === 0 : files.length < maxFiles"
             class="relative border-2 border-dashed border-slate-200 hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/20 rounded-2xl p-4 text-center transition cursor-pointer group"
             @click="$refs.pickerInput.click()">
            
            <input type="file" 
                   id="{{ $uniqueId }}_picker"
                   x-ref="pickerInput"
                   @change="handleFileSelect($event)"
                   accept="{{ $accept }}"
                   {{ $multiple ? 'multiple' : '' }}
                   class="hidden">

            <div class="flex flex-col items-center justify-center space-y-1.5 py-2">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-500 group-hover:text-emerald-700 group-hover:border-emerald-300 shadow-2xs flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-800 transition block">
                        Pilih Foto dari Galeri atau Kamera
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">
                        Otomatis dikompresi agar hemat kuota dan cepat terunggah
                    </span>
                </div>
            </div>
        </div>

        <!-- Compressing Indicator -->
        <div x-show="isCompressing" 
             class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center space-x-2 text-xs text-emerald-800 font-semibold shadow-2xs"
             style="display: none;">
            <svg class="w-4 h-4 animate-spin text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Sedang mengompresi dan mengoptimalkan ukuran foto...</span>
        </div>

        <!-- Error Message -->
        <div x-show="errorMessage" 
             class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium"
             x-text="errorMessage"
             style="display: none;"></div>

        <!-- Preview Thumbnails Grid -->
        <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            <template x-for="(item, idx) in files" :key="idx">
                <div class="relative bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs group flex flex-col">
                    <!-- Image or File Preview -->
                    <div class="h-28 bg-slate-100 flex items-center justify-center overflow-hidden">
                        <template x-if="item.isImage && item.previewUrl !== 'file'">
                            <img :src="item.previewUrl" class="w-full h-full object-cover" :alt="item.name">
                        </template>
                        <template x-if="!item.isImage || item.previewUrl === 'file'">
                            <div class="p-3 text-center text-slate-500">
                                <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-[10px] font-mono block truncate mt-1" x-text="item.name"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Delete Button -->
                    <button type="button" 
                            @click="removeFile(idx)"
                            class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition shadow-xs cursor-pointer"
                            title="Hapus foto ini">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Compression Optimization Badge -->
                    <div class="p-2 bg-white flex flex-col space-y-0.5 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center space-x-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="'✓ ' + item.compressedSizeKb + ' KB'"></span>
                            </span>
                            <template x-if="item.savedPercent > 0">
                                <span class="text-[9px] font-semibold text-slate-400" x-text="'-' + item.savedPercent + '%'"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Help Text -->
    @if ($helpText)
        <p class="text-[11px] text-slate-400 mt-1">{{ $helpText }}</p>
    @else
        <p class="text-[10px] text-slate-400 mt-1">Maksimal {{ $multiple ? $maxFiles . ' foto' : '1 file' }} (format JPG, PNG, WEBP). Foto otomatis dikompresi agar ukuran tetap di bawah 2MB.</p>
    @endif
</div>

