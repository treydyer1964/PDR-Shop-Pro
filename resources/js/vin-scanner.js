import { BrowserMultiFormatReader, NotFoundException } from '@zxing/library';

/**
 * VIN Scanner Alpine.js component.
 *
 * Usage:
 *   <div x-data="vinScanner()" ...>
 *
 * Emits a 'vin-scanned' CustomEvent on the window with { detail: { vin } }
 * so Livewire components can listen via wire:on or Alpine @window events.
 */
window.vinScanner = function () {
    return {
        scanning:  false,
        error:     null,
        reader:    null,
        stream:    null,

        async startScan() {
            this.error   = null;
            this.scanning = true;

            try {
                this.reader = new BrowserMultiFormatReader();

                // Prefer rear camera on mobile
                const devices = await BrowserMultiFormatReader.listVideoInputDevices();
                const rear = devices.find(d =>
                    /back|rear|environment/i.test(d.label)
                ) || devices[devices.length - 1];

                const deviceId = rear?.deviceId || undefined;

                await this.reader.decodeFromVideoDevice(
                    deviceId,
                    this.$refs.videoEl,
                    (result, err) => {
                        if (result) {
                            const raw = result.getText().toUpperCase().trim();
                            // A VIN is 17 alphanumeric chars — barcode may include extra chars
                            const vinMatch = raw.match(/[A-HJ-NPR-Z0-9]{17}/);
                            if (vinMatch) {
                                this.stopScan();
                                window.dispatchEvent(
                                    new CustomEvent('vin-scanned', { detail: { vin: vinMatch[0] } })
                                );
                            }
                        }
                        // NotFoundException is normal (no barcode in frame yet) — ignore
                        if (err && !(err instanceof NotFoundException)) {
                            console.warn('ZXing scan error:', err);
                        }
                    }
                );
            } catch (e) {
                this.error = 'Camera access denied or unavailable. Please check browser permissions.';
                this.scanning = false;
                console.error('VIN scanner error:', e);
            }
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
