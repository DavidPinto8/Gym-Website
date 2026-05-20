document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-menu');

    if (!button || !menu) {
        return;
    }

    button.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('open');

        button.classList.toggle('open', isOpen);
        button.setAttribute('aria-expanded', String(isOpen));
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('open');
            button.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
        });
    });
});