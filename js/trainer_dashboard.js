document.addEventListener('DOMContentLoaded', () => {
    const rosterButtons = document.querySelectorAll('.js-toggle-roster');

    rosterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const rosterId = button.dataset.rosterTarget;
            const roster = document.getElementById(rosterId);

            if (!roster) {
                return;
            }

            const isHidden = roster.classList.contains('roster-hidden');

            roster.classList.toggle('roster-hidden', !isHidden);
            roster.classList.toggle('roster-visible', isHidden);

            button.textContent = isHidden
                ? button.textContent.replace('View Roster', 'Hide Roster')
                : button.textContent.replace('Hide Roster', 'View Roster');
        });
    });
});