document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('equipment-type');
    const searchInput = document.getElementById('equipment-search');

    loadEquipment();

    typeSelect.addEventListener('change', loadEquipment);
    searchInput.addEventListener('input', loadEquipment);
});

async function loadEquipment() {
    const type = document.getElementById('equipment-type').value;
    const search = document.getElementById('equipment-search').value;

    const params = new URLSearchParams({
        type,
        search
    });

    const grid = document.getElementById('equipment-grid');

    grid.innerHTML = `
        <p class="equipment-empty-state">
            Loading equipment...
        </p>
    `;

    try {
        const response = await fetch(`api/equipment_status.php?${params}`);

        if (!response.ok) {
            throw new Error('Failed to fetch equipment.');
        }

        const equipment = await response.json();

        renderEquipment(equipment);
        renderEquipmentSummary(equipment);
    } catch (error) {
        grid.innerHTML = `
            <p class="equipment-empty-state equipment-error-state">
                Could not load equipment right now.
            </p>
        `;

        renderEquipmentSummary([]);
    }
}

function escapeHTML(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getEquipmentStatus(available, total) {
    if (available === 0) {
        return {
            className: 'busy',
            label: 'Unavailable'
        };
    }

    if (total > 0 && available <= total / 3) {
        return {
            className: 'low',
            label: 'Limited'
        };
    }

    return {
        className: 'available',
        label: 'Available'
    };
}

function renderEquipmentSummary(equipment) {
    const totalCount = document.getElementById('equipment-total-count');
    const availableCount = document.getElementById('equipment-available-count');
    const criticalCount = document.getElementById('equipment-critical-count');

    const totals = equipment.reduce(
        (summary, item) => {
            const available = Number(item.available_units);
            const total = Number(item.total_units);

            summary.availableUnits += Number.isNaN(available) ? 0 : available;

            if (!Number.isNaN(total) && total > 0 && available <= total / 3) {
                summary.criticalItems += 1;
            }

            return summary;
        },
        {
            availableUnits: 0,
            criticalItems: 0
        }
    );

    totalCount.textContent = equipment.length;
    availableCount.textContent = totals.availableUnits;
    criticalCount.textContent = totals.criticalItems;
}

function renderEquipment(equipment) {
    const grid = document.getElementById('equipment-grid');

    grid.innerHTML = '';

    if (!Array.isArray(equipment) || equipment.length === 0) {
        grid.innerHTML = `
            <p class="equipment-empty-state">
                No equipment found.
            </p>
        `;
        return;
    }

    const cards = equipment.map(item => {
        const available = Number(item.available_units);
        const total = Number(item.total_units);

        const safeAvailable = Number.isNaN(available) ? 0 : available;
        const safeTotal = Number.isNaN(total) ? 0 : total;

        const percentage = safeTotal === 0
            ? 0
            : Math.round((safeAvailable / safeTotal) * 100);

        const status = getEquipmentStatus(safeAvailable, safeTotal);

        return `
            <article class="equipment-card">
                <div class="equipment-card-header">
                    <div>
                        <span class="equipment-type-badge">
                            ${escapeHTML(item.type)}
                        </span>

                        <h3>${escapeHTML(item.name)}</h3>
                    </div>

                    <span class="equipment-status ${status.className}">
                        ${status.label}
                    </span>
                </div>

                <div class="equipment-meta">
                    <p>
                        <strong>Location</strong>
                        <span>${escapeHTML(item.location)}</span>
                    </p>

                    <p>
                        <strong>Availability</strong>
                        <span>${safeAvailable}/${safeTotal} units</span>
                    </p>
                </div>

                <div class="equipment-availability-bar" aria-hidden="true">
                    <div
                        class="equipment-availability-fill ${status.className}"
                        data-progress="${percentage}"
                    ></div>
                </div>

                <p class="equipment-percentage">
                    ${percentage}% currently available
                </p>
            </article>
        `;
    });

    grid.innerHTML = cards.join('');

    document
        .querySelectorAll('.equipment-availability-fill')
        .forEach(fill => {
            const progress = Number.parseFloat(fill.dataset.progress);
            const safeProgress = Number.isNaN(progress)
                ? 0
                : Math.min(Math.max(progress, 0), 100);

            fill.style.width = `${safeProgress}%`;
        });
}