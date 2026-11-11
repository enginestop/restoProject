document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('layoutSidenav_nav');
    if (!toggle || !sidebar) return;
    const mobile = window.matchMedia('(max-width: 991px)');
    const update = () => {
        const toggled = document.body.classList.contains('sb-sidenav-toggled');
        toggle.setAttribute('aria-expanded', String(mobile.matches ? toggled : !toggled));
        sidebar.inert = mobile.matches ? !toggled : toggled;
    };
    const close = () => { document.body.classList.remove('sb-sidenav-toggled'); update(); };
    toggle.addEventListener('click', () => { document.body.classList.toggle('sb-sidenav-toggled'); update(); });
    document.querySelector('.staff-backdrop')?.addEventListener('click', close);
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && mobile.matches) { close(); toggle.focus(); } });
    mobile.addEventListener('change', () => { document.body.classList.remove('sb-sidenav-toggled'); update(); });
    update();
});
