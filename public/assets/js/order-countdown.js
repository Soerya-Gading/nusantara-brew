// Hitung mundur tampilan. Batas waktu tetap ditegakkan server saat backend tersambung.
const el = document.getElementById('payment-countdown');
if (el) {
    const expiresAt = new Date(el.dataset.expiresAt).getTime();
    const render = () => {
        const remaining = Math.max(0, expiresAt - Date.now());
        if (remaining === 0) {
            el.textContent = '0 menit';
            el.classList.add('is-expired');
            return;
        }
        const totalSeconds = Math.floor(remaining / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        el.textContent = `${minutes} menit ${seconds} detik`;
    };
    render();
    setInterval(render, 1000);
}
