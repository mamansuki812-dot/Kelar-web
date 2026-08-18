<style>
.kelar-frame {
    position: absolute; inset: 0; z-index: 5; pointer-events: none;
    border: 2px solid rgba(14, 131, 136, .7); border-radius: 12px;
    animation: kelarPulse 2s ease-in-out infinite;
}
.kelar-scanline {
    position: absolute; left: 4%; right: 4%; height: 3px; z-index: 6; border-radius: 3px; pointer-events: none;
    background: linear-gradient(90deg, transparent, #0E8388, transparent);
    box-shadow: 0 0 12px rgba(14, 131, 136, .9);
    animation: kelarScanline 2.6s ease-in-out infinite;
}
.kelar-flash {
    position: absolute; inset: 0; z-index: 7; background: #10B981; opacity: 0; pointer-events: none;
}
.kelar-flash.flash-on { animation: kelarFlash .45s ease-out; }
@keyframes kelarScanline { 0%, 100% { top: 8%; } 50% { top: 90%; } }
@keyframes kelarPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(14,131,136,.4); } 50% { box-shadow: 0 0 0 12px rgba(14,131,136,0); } }
@keyframes kelarFlash { 0% { opacity: 0; } 15% { opacity: .55; } 100% { opacity: 0; } }
</style>

