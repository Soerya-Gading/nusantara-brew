// Drawer sidebar untuk layar sempit. Tidak mengatur hak akses.
const toggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('dash-sidebar');
const backdrop = document.getElementById('sidebar-backdrop');

if (toggle && sidebar && backdrop) {
    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        backdrop.hidden = !open;
        backdrop.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
    };

    toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    backdrop.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });
}
