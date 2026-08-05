export function initializeSidebar() {
    const sidebar = document.querySelector('#app-sidebar');
    const backdrop = document.querySelector('#app-sidebar-backdrop');

    if (!sidebar || !backdrop) {
        return;
    }

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-sidebar-open]').forEach((button) => button.addEventListener('click', open));
    document.querySelectorAll('[data-sidebar-close]').forEach((button) => button.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}
