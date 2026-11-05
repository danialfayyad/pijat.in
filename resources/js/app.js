import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

/**
 * Universal client-side image compression utility using HTML5 Canvas.
 * Compresses camera photos (e.g. 5-15MB) down to ~150-250KB before upload.
 */
window.compressImageFile = function (file, maxDim = 1280, quality = 0.8) {
    return new Promise((resolve) => {
        // If file is not an image (e.g. PDF), return original
        if (!file || !file.type || !file.type.startsWith('image/')) {
            return resolve(file);
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.onload = function () {
                let width = img.width;
                let height = img.height;

                if (width > maxDim || height > maxDim) {
                    if (width >= height) {
                        height = Math.round((height / width) * maxDim);
                        width = maxDim;
                    } else {
                        width = Math.round((width / height) * maxDim);
                        height = maxDim;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        return resolve(file);
                    }
                    const cleanName = (file.name || 'foto').replace(/\.[^/.]+$/, "") + ".jpg";
                    const compressedFile = new File([blob], cleanName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
};

/**
 * Alpine.js component for image upload with auto-compression, preview thumbnails, and multi-file support.
 */
Alpine.data('imageUploadCompressor', (config = {}) => ({
    maxFiles: config.maxFiles || 3,
    maxDim: config.maxDim || 1280,
    quality: config.quality || 0.8,
    multiple: config.multiple !== false,
    files: [],
    isCompressing: false,
    errorMessage: '',

    async handleFileSelect(event) {
        const selectedFiles = Array.from(event.target.files || []);
        if (!selectedFiles.length) return;

        this.errorMessage = '';
        const currentCount = this.multiple ? this.files.length : 0;
        const remainingSlots = this.maxFiles - currentCount;

        if (remainingSlots <= 0) {
            this.errorMessage = `Maksimal ${this.maxFiles} foto yang diperbolehkan.`;
            event.target.value = '';
            return;
        }

        const filesToProcess = selectedFiles.slice(0, remainingSlots);
        if (selectedFiles.length > remainingSlots) {
            this.errorMessage = `Hanya ${remainingSlots} foto yang ditambahkan (batas maksimal ${this.maxFiles} foto).`;
        }

        this.isCompressing = true;

        try {
            for (const rawFile of filesToProcess) {
                const originalKb = Math.round(rawFile.size / 1024);
                let processedFile = rawFile;
                let previewUrl = '';

                if (rawFile.type.startsWith('image/')) {
                    processedFile = await window.compressImageFile(rawFile, this.maxDim, this.quality);
                    previewUrl = URL.createObjectURL(processedFile);
                } else {
                    previewUrl = 'file';
                }

                const compressedKb = Math.round(processedFile.size / 1024);
                const savedPercent = originalKb > 0 ? Math.max(0, Math.round(((originalKb - compressedKb) / originalKb) * 100)) : 0;

                const fileItem = {
                    file: processedFile,
                    name: processedFile.name,
                    previewUrl: previewUrl,
                    originalSizeKb: originalKb,
                    compressedSizeKb: compressedKb,
                    savedPercent: savedPercent,
                    isImage: rawFile.type.startsWith('image/')
                };

                if (!this.multiple) {
                    this.files = [fileItem];
                } else {
                    this.files.push(fileItem);
                }
            }

            this.syncToInput();
        } catch (err) {
            console.error('Compression error:', err);
            this.errorMessage = 'Gagal memproses dan mengompresi foto.';
        } finally {
            this.isCompressing = false;
            event.target.value = '';
        }
    },

    removeFile(index) {
        if (this.files[index] && this.files[index].previewUrl && this.files[index].previewUrl !== 'file') {
            URL.revokeObjectURL(this.files[index].previewUrl);
        }
        this.files.splice(index, 1);
        this.syncToInput();
        this.errorMessage = '';
    },

    syncToInput() {
        const input = this.$refs.realInput;
        if (!input) return;

        const dt = new DataTransfer();
        this.files.forEach(item => {
            dt.items.add(item.file);
        });
        input.files = dt.files;
    }
}));

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.start();
