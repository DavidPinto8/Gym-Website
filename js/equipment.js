document.addEventListener('DOMContentLoaded', () => {
    loadEquipment();

    document
        .getElementById('equipment-type')
        .addEventListener('change', loadEquipment);

    document
        .getElementById('equipment-search')
        .addEventListener('input', loadEquipment);
});

async function loadEquipment() {
    const type = document.getElementById('equipment-type').value;
    const search = document.getElementById('equipment-search').value;

    const params = new URLSearchParams({
        type,
        search
    });

    const response = await fetch(`api/equipment_status.php?${params}`);
    const equipment = await response.json();

    renderEquipment(equipment);
}

function renderEquipment(equipment) {
    const grid = document.getElementById('equipment-grid');

    grid.innerHTML = '';

    if (equipment.length === 0) {
        grid.innerHTML = '<p>No equipment found.</p>';
        return;
    }

    equipment.forEach(item => {
        const available = Number(item.available_units);
        const total = Number(item.total_units);

        let statusClass = 'available';
        let statusText = 'Available';

        if (available === 0) {
            statusClass = 'busy';
            statusText = 'Unavailable';
        } else if (available <= total / 3) {
            statusClass = 'low';
            statusText = 'Limited';
        }

        const percentage =
            total === 0
                ? 0
                : Math.round((available / total) * 100);

        grid.innerHTML += `
            <article class="equipment-card">
                <h3>${item.name}</h3>

                <p>
                    <strong>Type:</strong>
                    ${item.type}
                </p>

                <p>
                    <strong>Location:</strong>
                    ${item.location}
                </p>

                <p>
                    <strong>Availability:</strong>
                    ${available}/${total} units
                </p>

                <div class="equipment-availability-bar">
                    <div 
                        class="equipment-availability-fill"
                        style="width: ${percentage}%"
                    ></div>
                </div>

                <p class="${statusClass}">
                    ${statusText}
                </p>
            </article>
        `;
    });
}