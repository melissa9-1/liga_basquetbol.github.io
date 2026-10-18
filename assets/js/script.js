document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (tabs.length && loginForm && registerForm) {
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.target;
                const isLogin = target === 'login-form';

                tabs.forEach((item) => item.classList.toggle('active', item === tab));
                loginForm.classList.toggle('hidden', !isLogin);
                registerForm.classList.toggle('hidden', isLogin);
            });
        });
    }
});
