document.addEventListener(
   'DOMContentLoaded',
   () => {

      loadClasses();

      document
         .querySelector('.filter-form')
         .addEventListener(
            'submit',
            event => {

               event.preventDefault();

               loadClasses();

            });

   });

async function loadClasses() {

   const type =
      document
      .getElementById(
         'class-type'
      ).value;

   const day =
      document
      .getElementById(
         'day-select'
      ).value;

   const params =
      new URLSearchParams({
         type,
         day
      });

   const response =
      await fetch(
         `api/classes.php?${params}`
      );

   const classes =
      await response.json();

   renderClasses(classes);

}

function renderClasses(classes) {

   const container =
      document
      .querySelector(
         '.classes-grid'
      );

   container.innerHTML = '';

   if (classes.length === 0) {

      container.innerHTML = `
<p>
No classes found.
</p>
`;

      return;

   }

   classes.forEach(cls => {

      const date =
         new Date(cls.start_time);

      const day =
         date.toLocaleDateString(
            'en-US', {
               weekday: 'long'
            }
         );

      const time =
         date.toLocaleTimeString(
            [], {
               hour: '2-digit',
               minute: '2-digit'
            }
         );

      const spots =
         cls.capacity -
         cls.enrolled_count;

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
                <input type="hidden" name="class_id" value="${cls.id}">
                <button type="submit" class="btn-outline btn-block cancel-btn">
                    Cancel Booking
                </button>
            </form>
        `;
        } else if (cls.user_booking_status === 'waitlist') {
        actionHtml = `
            <form action="actions/action_cancel_booking.php" method="POST">
                <input type="hidden" name="class_id" value="${cls.id}">
                <button type="submit" class="btn-outline btn-block waitlist-btn">
                    Leave Waitlist
                </button>
            </form>
        `;
        } else {
        actionHtml = `
            <form action="actions/action_book_class.php" method="POST">
                <input type="hidden" name="class_id" value="${cls.id}">
                <button type="submit" class="btn-primary btn-block">
                    ${spots <= 0 ? 'Join Waitlist' : 'Book Spot'}
                </button>
            </form>
        `;
        }

      container.innerHTML += `

<article
class="
class-card
${cls.type}
"
>

<div
class="
card-content
"
>

<span
class="
class-tag
"
>

${cls.type}

</span>

<h3>

${cls.name}

</h3>

<p
class="
class-info
"
>

${day}
•
${time}

</p>

<p
class="
class-trainer
"
>

Trainer:
${cls.trainer_name ?? 'TBA'}

</p>

<p
class="
class-capacity
"
>

${cls.enrolled_count}
/
${cls.capacity}
spots booked

</p>

${actionHtml}

</div>

</article>

`;

   });

}