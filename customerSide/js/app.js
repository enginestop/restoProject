document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.hamburger');
    const nav = document.getElementById('site-nav');
    const account = document.querySelector('.dropbtn');
    const accountLinks = document.getElementById('account-links');
    const mobile = window.matchMedia('(max-width: 1100px)');
    const setNavigation = open => {
        nav?.classList.toggle('is-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        if (nav) nav.inert = mobile.matches && !open;
    };
    const setAccount = open => {
        if (accountLinks) accountLinks.hidden = !open;
        account?.setAttribute('aria-expanded', String(open));
    };
    toggle?.addEventListener('click', () => setNavigation(toggle.getAttribute('aria-expanded') !== 'true'));
    account?.addEventListener('click', () => setAccount(account.getAttribute('aria-expanded') !== 'true'));
    nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => { setNavigation(false); setAccount(false); }));
    document.addEventListener('click', event => { if (!event.target.closest('.account-menu')) setAccount(false); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') { setNavigation(false); setAccount(false); } });
    mobile.addEventListener('change', () => { setNavigation(false); setAccount(false); });
    const select = document.getElementById('menu-category');
    const filter = () => document.querySelectorAll('.menu-group').forEach(group => { group.hidden = select.value !== 'blue' && group.dataset.category !== select.value; });
    select?.addEventListener('change', filter);
    if (select) filter();
    setNavigation(false);
});
