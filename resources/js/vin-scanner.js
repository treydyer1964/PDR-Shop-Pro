import { BrowserMultiFormatReader, NotFoundException } from '@zxing/library';

/**
 * VIN Scanner Alpine.js component.
 *
 * Strategy:
 *   1. If getUserMedia is available (HTTPS secure context) → live camera stream via ZXing
 *   2. Otherwise → photo capture via <input capture="environment"> → decode from image
 *
 * Emits 'vin-scanned' CustomEvent: { detail: { vin } }
 */
window.vinScanner = function () {
    return {
        scanning:      false,
        photoScanning: false,
        error:         null,
        reader:        null,

        get canLiveScan() {
            return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        },

        async startScan() {
            this.error = null;
            if (this.canLiveScan) {
                await this.startLiveScan();
            } else {
                // Trigger the hidden file input (opens native camera on iOS)
                this.$refs.photoInput.click();
            }
        },

        async startLiveScan() {
            this.scanning = true;
            try {
                this.reader = new BrowserMultiFormatReader();
                await this.reader.decodeFromConstraints(
                    { video: { facingMode: { ideal: 'environment' } } },
                    this.$refs.videoEl,
                    (result, err) => {
                        if (result) {
                            const raw = result.getText().toUpperCase().trim();
                            const vinMatch = raw.match(/[A-HJ-NPR-Z0-9]{17}/);
                            if (vinMatch) {
                                this.stopScan();
                                window.dispatchEvent(
                                    new CustomEvent('vin-scanned', { detail: { vin: vinMatch[0] } })
                                );
                            }
                        }
                        if (err && !(err instanceof NotFoundException)) {
                            console.warn('ZXing scan error:', err);
                        }
                    }
                );
            } catch (e) {
                this.scanning = false;
                if (e.name === 'NotAllowedError') {
                    this.error = 'Camera permission denied. Check Settings → Safari → Camera.';
                } else {
                    // Live scan unavailable — fall back to photo
                    this.$refs.photoInput.click();
                }
                console.error('VIN live scan error:', e.name, e.message);
            }
        },

        async scanFromPhoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.error         = null;
            this.photoScanning = true;

            try {
                // Layer 1: try ZXing barcode decode from the image
                let vin = await this.tryZxingFromFile(file);

                // Layer 2: ZXing failed — send to OpenAI Vision
                if (!vin) {
                    vin = await this.tryOpenAiVision(file);
                }

                if (vin) {
                    window.dispatchEvent(new CustomEvent('vin-scanned', { detail: { vin } }));
                } else {
                    this.error = 'No VIN found in photo. Try again or type the VIN manually.';
                }
            } catch (e) {
                this.error = 'Could not process photo. Try again or type the VIN manually.';
                console.error('VIN photo scan error:', e);
            } finally {
                this.photoScanning = false;
                event.target.value = '';
            }
        },

        async tryZxingFromFile(file) {
            try {
                const reader = new BrowserMultiFormatReader();
                const imgUrl = URL.createObjectURL(file);
                const result = await reader.decodeFromImageUrl(imgUrl);
                URL.revokeObjectURL(imgUrl);
                if (result) {
                    const raw      = result.getText().toUpperCase().trim();
                    const vinMatch = raw.match(/[A-HJ-NPR-Z0-9]{17}/);
                    if (vinMatch) return vinMatch[0];
                }
            } catch (e) {
                // NotFoundException or similar — barcode not found, fall through to OpenAI
            }
            return null;
        },

        /**
         * Draw the file to a Canvas so the browser applies EXIF orientation,
         * resize to max 1600px (reduces payload, stays plenty sharp for OCR),
         * then export as a correctly-rotated JPEG base64 string.
         */
        async normalizeOrientation(file) {
            return new Promise((resolve) => {
                const img = new Image();
                const url = URL.createObjectURL(file);

                img.onload = () => {
                    const MAX = 1600;
                    let w = img.naturalWidth;
                    let h = img.naturalHeight;

                    if (w > MAX || h > MAX) {
                        if (w > h) { h = Math.round(h * MAX / w); w = MAX; }
                        else       { w = Math.round(w * MAX / h); h = MAX; }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width  = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    URL.revokeObjectURL(url);
                    resolve(canvas.toDataURL('image/jpeg', 0.85).split(',')[1]);
                };

                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    const reader = new FileReader();
                    reader.onload  = () => resolve(reader.result.split(',')[1]);
                    reader.onerror = () => resolve(null);
                    reader.readAsDataURL(file);
                };

                img.src = url;
            });
        },

        async tryOpenAiVision(file) {
            // Normalize EXIF orientation via Canvas before sending
            const base64 = await this.normalizeOrientation(file);
            if (!base64) return null;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch('/vin/extract', {
                method:  'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'Accept':           'application/json',
                    'X-CSRF-TOKEN':     csrfToken || '',
                },
                body: JSON.stringify({
                    image:     base64,
                    mime_type: file.type || 'image/jpeg',
                }),
            });

            if (!response.ok) return null;
            const data = await response.json();
            return data.vin || null;
        },

        stopScan() {
            if (this.reader) {
                this.reader.reset();
                this.reader = null;
            }
            this.scanning = false;
            this.error    = null;
        },

        destroy() {
            this.stopScan();
        },
    };
};
