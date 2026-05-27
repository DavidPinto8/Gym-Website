document.addEventListener('DOMContentLoaded', () => {
    const modalButtons = document.querySelectorAll('.js-open-trainer-modal');
    const closeButtons = document.querySelectorAll('.js-close-trainer-modal');
    const modals = document.querySelectorAll('.trainer-modal-overlay');

    let activeModal = null;

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = false;

        requestAnimationFrame(() => {
            modal.classList.add('active');
        });

        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        activeModal = modal;
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        activeModal = null;

        modal.addEventListener(
            'transitionend',
            () => {
                if (!modal.classList.contains('active')) {
                    modal.hidden = true;
                }
            },
            { once: true }
        );
    }

    modalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modalTarget;
            const modal = document.getElementById(modalId);

            openModal(modal);
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            closeModal(button.closest('.trainer-modal-overlay'));
        });
    });

    modals.forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && activeModal) {
            closeModal(activeModal);
        }
    });
});