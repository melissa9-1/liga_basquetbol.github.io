document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const adminFab = document.querySelector('.admin-fab');
    const adminPanel = document.getElementById('admin-panel');
    const closeAdminPanel = document.querySelector('.admin-panel__close');

    const activateTab = (target) => {
        const isLogin = target === 'login-form';
        tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.target === target));
        if (loginForm) loginForm.classList.toggle('hidden', !isLogin);
        if (registerForm) registerForm.classList.toggle('hidden', isLogin);
    };

    if (tabs.length && loginForm && registerForm) {
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateTab(tab.dataset.target));
        });
    }

    if (adminFab && adminPanel) {
        adminFab.addEventListener('click', () => {
            adminPanel.classList.toggle('visible');
            const isOpen = adminPanel.classList.contains('visible');
            adminFab.setAttribute('aria-expanded', String(isOpen));
            if (isOpen) {
                activateTab('login-form');
            }
        });
    }

    if (closeAdminPanel && adminPanel) {
        closeAdminPanel.addEventListener('click', () => {
            adminPanel.classList.remove('visible');
            adminFab?.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && adminPanel?.classList.contains('visible')) {
            adminPanel.classList.remove('visible');
            adminFab?.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('click', (event) => {
        if (
            adminPanel?.classList.contains('visible')
            && !adminPanel.contains(event.target)
            && !adminFab?.contains(event.target)
        ) {
            adminPanel.classList.remove('visible');
            adminFab?.setAttribute('aria-expanded', 'false');
        }
    });
});
