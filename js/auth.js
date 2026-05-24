document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.auth-form');

    forms.forEach((form) => {
        const passwordInput = form.querySelector('input[type="password"]');
        const submitButton = form.querySelector('button[type="submit"]');

        if (passwordInput) {
            addPasswordToggle(passwordInput);
        }

        form.addEventListener('submit', () => {
            if (!submitButton) return;

            submitButton.disabled = true;
            submitButton.textContent = 'Please wait...';
        });
    });
});

function addPasswordToggle(passwordInput) {
    const wrapper = document.createElement('div');
    wrapper.className = 'password-wrapper';

    passwordInput.parentNode.insertBefore(wrapper, passwordInput);
    wrapper.appendChild(passwordInput);

    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'password-toggle';
    toggleButton.textContent = 'SHOW';

    wrapper.appendChild(toggleButton);

    toggleButton.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';
        toggleButton.textContent = isHidden ? 'HIDE' : 'SHOW';
    });
}