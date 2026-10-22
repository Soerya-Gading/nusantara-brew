// Keranjang kasir sisi tampilan. Harga dan stok final tetap ditentukan server.
const list = document.querySelector('[data-pos-list]');
const placeholder = document.querySelector('[data-pos-placeholder]');
const totalEl = document.querySelector('[data-pos-total]');
const countEl = document.querySelector('[data-pos-count]');
const searchEl = document.querySelector('[data-pos-search]');
const emptyEl = document.querySelector('[data-pos-empty]');

const lines = new Map();

const rupiah = (value) => 'Rp ' + value.toLocaleString('id-ID');

function render() {
    list.querySelectorAll('.pos-line').forEach((node) => node.remove());

    let total = 0;
    let count = 0;

    lines.forEach((line, name) => {
        total += line.price * line.quantity;
        count += line.quantity;

        const item = document.createElement('li');
        item.className = 'pos-line';
        item.innerHTML = `
            <span class="pos-line-info">
                <strong></strong>
                <span class="pos-line-qty">
                    <button type="button" class="pos-qty-btn" data-dec aria-label="Kurangi">−</button>
                    <span data-qty></span>
                    <button type="button" class="pos-qty-btn" data-inc aria-label="Tambah">+</button>
                </span>
            </span>
            <span data-line-total></span>
        `;
        item.querySelector('strong').textContent = name;
        item.querySelector('[data-qty]').textContent = line.quantity;
        item.querySelector('[data-line-total]').textContent = rupiah(line.price * line.quantity);

        item.querySelector('[data-inc]').addEventListener('click', () => {
            if (line.quantity < line.stock) {
                line.quantity += 1;
                render();
            }
        });

        item.querySelector('[data-dec]').addEventListener('click', () => {
            line.quantity -= 1;
            if (line.quantity <= 0) {
                lines.delete(name);
            }
            render();
        });

        list.appendChild(item);
    });

    placeholder.hidden = lines.size > 0;
    totalEl.textContent = rupiah(total);
    countEl.textContent = `${count} item`;
}

document.querySelectorAll('[data-pos-item]').forEach((button) => {
    button.addEventListener('click', () => {
        if (button.hasAttribute('data-sold-out')) {
            return;
        }

        const name = button.dataset.name;
        const price = Number(button.dataset.price);
        const stock = Number(button.dataset.stock);
        const existing = lines.get(name);

        if (existing) {
            if (existing.quantity < stock) {
                existing.quantity += 1;
            }
        } else {
            lines.set(name, { price, stock, quantity: 1 });
        }

        render();
    });
});

if (searchEl) {
    searchEl.addEventListener('input', () => {
        const term = searchEl.value.trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('[data-pos-category]').forEach((group) => {
            let shown = 0;
            group.querySelectorAll('[data-pos-item]').forEach((card) => {
                const match = card.dataset.name.toLowerCase().includes(term);
                card.hidden = !match;
                if (match) shown += 1;
            });
            group.hidden = shown === 0;
            visible += shown;
        });

        emptyEl.hidden = visible > 0;
    });
}
