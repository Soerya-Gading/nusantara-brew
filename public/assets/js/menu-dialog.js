// Dialog detail menu. Tombol tambah keranjang tetap nonaktif sampai backend siap.
const dialog = document.getElementById('menu-dialog');

if (dialog) {
    const nameEl = document.getElementById('menu-dialog-name');
    const descEl = document.getElementById('menu-dialog-description');
    const priceEl = document.getElementById('menu-dialog-price');
    const stockEl = document.getElementById('menu-dialog-stock');
    const noteEl = document.getElementById('menu-dialog-note');
    const addBtn = document.getElementById('menu-dialog-add');
    const closeBtn = document.getElementById('menu-dialog-close');

    document.querySelectorAll('[data-menu-dialog]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const data = trigger.dataset;
            const available = data.available === '1';

            nameEl.textContent = data.name;
            descEl.textContent = data.description;
            priceEl.textContent = data.price;
            stockEl.textContent = available ? `Stok: ${data.stock}` : 'Habis';
            noteEl.hidden = available;
            addBtn.disabled = true;

            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else {
                dialog.setAttribute('open', '');
            }
        });
    });

    closeBtn.addEventListener('click', () => dialog.close());

    // Klik area gelap di luar panel menutup dialog.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
}
