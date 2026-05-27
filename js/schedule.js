document.addEventListener('DOMContentLoaded', () => {
    loadClasses();

    const filterForm = document.querySelector('.filter-form');

    if (filterForm) {
        filterForm.addEventListener('submit', event => {
            event.preventDefault();
            loadClasses();
        });
    }
});

async function loadClasses() {
    const type = document.getElementById('class-type').value;
    const day = document.getElementById('day-select').value;
    const trainer = document.getElementById('trainer-select').value;

    const params = new URLSearchParams({
        type,
        day,
        trainer
    });

    const container = document.querySelector('.classes-grid');

    container.classList.add('is-loading');

    try {
        const response = await fetch(`api/classes.php?${params}`);

        if (!response.ok) {
            throw new Error('Failed to load classes.');
        }

        const classes = await response.json();

        renderClasses(classes);
    } catch (error) {
        container.innerHTML = `
            <p class="empty-classes-message error-classes-message">
                Could not load classes right now.
            </p>
        `;
    } finally {
        container.classList.remove('is-loading');
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderClasses(classes) {
    const container = document.querySelector('.classes-grid');

    container.innerHTML = '';

    if (!Array.isArray(classes) || classes.length === 0) {
        container.innerHTML = `
            <p class="empty-classes-message">
                No classes found.
            </p>
        `;
        return;
    }

    const cards = classes.map(cls => {
        const date = new Date(cls.start_time);

        const day = date.toLocaleDateString('en-US', {
            weekday: 'long'
        });

        const time = date.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        const capacity = Number(cls.capacity);
        const enrolledCount = Number(cls.enrolled_count);
        const spots = capacity - enrolledCount;

        let actionHtml = '';

        if (!isLoggedIn) {
            actionHtml = `
                <a href="login.php" class="btn-outline btn-block text-center login-book-btn">
                    Login to Book
                </a>
            `;
        } else if (userRole !== 'member') {
            actionHtml = `
                <p class="member-warning">
                    Only members can book classes.
                </p>
            `;
        } else if (cls.user_booking_status === 'enrolled') {
            actionHtml = `
                <form action="actions/action_cancel_booking.php" method="POST">
                    <input type="hidden" name="csrf_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="class_id" value="${escapeHtml(cls.id)}">

                    <button type="submit" class="btn-outline btn-block cancel-btn">
                        Cancel Booking
                    </button>
                </form>
            `;
        } else if (cls.user_booking_status === 'waitlist') {
            actionHtml = `
                <div class="waitlist-position-badge">
                    <span>
                        🔥 Position <strong>#${escapeHtml(cls.waitlist_position ?? '1')}</strong> on Waitlist
                    </span>
                </div>

                <form action="actions/action_cancel_booking.php" method="POST">
                    <input type="hidden" name="csrf_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="class_id" value="${escapeHtml(cls.id)}">

                    <button type="submit" class="btn-outline btn-block leave-waitlist-btn">
                        Leave Waitlist
                    </button>
                </form>
            `;
        } else {
            actionHtml = `
                <form action="actions/action_book_class.php" method="POST">
                    <input type="hidden" name="csrf_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="class_id" value="${escapeHtml(cls.id)}">

                    <button type="submit" class="btn-primary btn-block">
                        ${spots <= 0 ? 'Join Waitlist' : 'Book Spot'}
                    </button>
                </form>
            `;
        }

        const ratingHtml = Number(cls.review_count) > 0
            ? `<p class="class-rating">★ ${Number(cls.average_rating).toFixed(1)} (${escapeHtml(cls.review_count)} reviews)</p>`
            : '<p class="class-rating">No reviews yet</p>';

        const bookingClass = cls.user_booking_status === 'enrolled'
            ? 'enrolled'
            : cls.user_booking_status === 'waitlist'
                ? 'waitlisted'
                : '';

        return `
            <article class="class-card ${escapeHtml(cls.type)} ${bookingClass}">
                <div class="card-overlay"></div>

                <div class="card-content">
                    <span class="class-tag">
                        ${escapeHtml(cls.type)}
                    </span>

                    <h3>
                        ${escapeHtml(cls.name)}
                    </h3>

                    <p class="class-info">
                        ${escapeHtml(day)} • ${escapeHtml(time)}
                    </p>

                    <div class="class-trainer-card">
                        <img
                            src="${escapeHtml(cls.trainer_avatar)}"
                            alt="${escapeHtml(cls.trainer_name ?? 'Trainer')}"
                        >

                        <span>
                            <small>Trainer</small>
                            ${escapeHtml(cls.trainer_name ?? 'TBA')}
                        </span>
                    </div>

                    <p class="class-capacity">
                        ${escapeHtml(cls.enrolled_count)}/${escapeHtml(cls.capacity)} spots booked
                    </p>

                    ${ratingHtml}

                    <a href="class_details.php?id=${escapeHtml(cls.id)}" class="btn-outline btn-block details-btn">
                        View Details
                    </a>

                    ${actionHtml}
                </div>
            </article>
        `;
    });

    container.innerHTML = cards.join('');
}