import './bootstrap';
import './vin-scanner';

// ── Phone number auto-formatter ───────────────────────────────────────────────
// Applies to all type="tel" inputs globally. Formats as 123-456-7890.
function formatPhone(raw) {
    const d = raw.replace(/\D/g, '').slice(0, 10);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}-${d.slice(3)}`;
    return `${d.slice(0, 3)}-${d.slice(3, 6)}-${d.slice(6)}`;
}

let _phoneFormatting = false;
document.addEventListener('input', function (e) {
    if (_phoneFormatting || e.target.type !== 'tel') return;
    const formatted = formatPhone(e.target.value);
    if (e.target.value !== formatted) {
        _phoneFormatting = true;
        e.target.value = formatted;
        // Re-dispatch so Livewire wire:model picks up the formatted value
        e.target.dispatchEvent(new Event('input', { bubbles: true }));
        _phoneFormatting = false;
    }
}, false);
