document.addEventListener('DOMContentLoaded', () => {
    const progressBars = document.querySelectorAll('.js-progress-fill');
    const confirmForms = document.querySelectorAll('.js-confirm-form');

    progressBars.forEach(bar => {
        const rawValue = Number.parseFloat(bar.dataset.progress);
        const safeValue = Number.isNaN(rawValue)
            ? 0
            : Math.min(Math.max(rawValue, 0), 100);

        bar.style.width = `${safeValue}%`;
    });

    confirmForms.forEach(form => {
        form.addEventListener('submit', event => {
            const message = form.dataset.confirmMessage || 'Are you sure?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});