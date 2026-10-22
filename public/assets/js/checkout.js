// Interaksi tampilan saja; validasi dan transaksi tetap menjadi tugas backend.
const form = document.getElementById('checkout-form');
if (form) {
    const field = document.getElementById('table-field');
    const input = document.getElementById('table-label');
    const updateFulfillment = () => {
        const dineIn = form.elements.fulfillment_type.value === 'dine_in';
        field.hidden = !dineIn;
        input.disabled = !dineIn;
        input.required = dineIn;
        if (!dineIn) input.value = '';
    };
    form.querySelectorAll('[name="fulfillment_type"]').forEach(radio => {
        radio.addEventListener('change', updateFulfillment);
    });
    updateFulfillment();
    form.addEventListener('submit', event => event.preventDefault());
}
