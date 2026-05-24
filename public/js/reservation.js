/* reservation.js — New reservation form: validation, vehicle selection (REQUIRED) & AJAX submit */

(function () {
    'use strict';

    const form        = document.getElementById('reservation-form');
    const submitBtn   = document.getElementById('submit-btn');
    const depDateEl   = document.getElementById('departure_date');
    const retDateEl   = document.getElementById('return_date');
    const passengersEl = document.getElementById('passengers');
    const occupancyWarning = document.getElementById('occupancy-warning');
    const occupancyRemarksGroup = document.getElementById('occupancy-remarks-group');
    const requesterRemarksEl = document.getElementById('requester_remarks');

    if (!form) return;

    // ── Date constraints ─────────────────────────────────────────────
    const today = new Date().toISOString().split('T')[0];
    if (depDateEl) depDateEl.min = today;
    if (retDateEl) retDateEl.min = today;

    if (depDateEl && retDateEl) {
        depDateEl.addEventListener('change', () => {
            retDateEl.min = depDateEl.value;
            if (retDateEl.value && retDateEl.value < depDateEl.value) {
                retDateEl.value = depDateEl.value;
            }
        });
    }

    // ── Vehicle Selection (REQUIRED Dropdown) ────────────────────────
    const typeFilter   = document.getElementById('vehicle_type_filter');
    const vehicleSelect = document.getElementById('vehicle_id');
    const detailsCard   = document.getElementById('selected-vehicle-details-card');

    let vehiclesData = [];

    if (typeFilter) typeFilter.addEventListener('change', fetchVehicles);
    if (passengersEl) passengersEl.addEventListener('input', handlePassengerChange);
    if (vehicleSelect) vehicleSelect.addEventListener('change', handleVehicleChange);

    // Initial fetch
    fetchVehicles();

    function fetchVehicles() {
        if (!vehicleSelect) return;

        const type = typeFilter ? typeFilter.value : 'all';
        const pax = passengersEl ? Math.max(1, parseInt(passengersEl.value || '1', 10)) : 1;

        // Clear select options and disable it while loading
        vehicleSelect.innerHTML = '<option value="">Searching vehicles...</option>';
        vehicleSelect.disabled = true;
        hideDetails();

        const baseMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl  = baseMeta ? baseMeta.getAttribute('content') : '/';
        const url = `${baseUrl}api/vehicles/available?type=${encodeURIComponent(type)}&capacity=${encodeURIComponent(pax)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => {
            if (!r.ok) {
                console.error('Vehicle API error:', r.status, r.statusText);
                throw new Error('API returned ' + r.status);
            }
            return r.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Vehicle API returned non-JSON:', text.substring(0, 200));
                throw e;
            }

            vehicleSelect.innerHTML = '';
            
            if (!data.success || !data.vehicles || data.vehicles.length === 0) {
                vehiclesData = [];
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No available vehicles of this type';
                vehicleSelect.appendChild(opt);
                vehicleSelect.disabled = true;
                return;
            }

            vehiclesData = data.vehicles;
            vehicleSelect.disabled = false;

            // Add placeholder option
            const placeholderOpt = document.createElement('option');
            placeholderOpt.value = '';
            placeholderOpt.textContent = '— Select vehicle —';
            vehicleSelect.appendChild(placeholderOpt);

            data.vehicles.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.vehicle_id;
                opt.textContent = `${v.make_model} — ${v.capacity} pax`;
                vehicleSelect.appendChild(opt);
            });
        })
        .catch((err) => {
            console.error('Vehicle fetch failed:', err);
            vehicleSelect.innerHTML = '<option value="">Failed to load vehicles</option>';
            vehicleSelect.disabled = true;
            vehiclesData = [];
        });
    }

    function handleVehicleChange() {
        if (!vehicleSelect) return;
        const val = vehicleSelect.value;
        
        // Reset border highlight
        vehicleSelect.style.borderColor = '';

        if (!val) {
            hideDetails();
            updateOccupancy(null);
            return;
        }

        const vehicle = vehiclesData.find(v => String(v.vehicle_id) === String(val));
        if (vehicle) {
            showDetails(vehicle);
            updateOccupancy(vehicle);
        } else {
            hideDetails();
            updateOccupancy(null);
        }
    }

    function handlePassengerChange() {
        fetchVehicles();
        const vehicle = getSelectedVehicle();
        if (vehicle && passengersEl) {
            const pax = parseInt(passengersEl.value || '0', 10) || 0;
            if (pax > 0 && pax > vehicle.capacity) {
                vehicleSelect.value = '';
                hideDetails();
                VRS.notify.warning('Selected vehicle capacity is lower than passenger count. Please choose another vehicle.');
            }
        }
        updateOccupancy(vehicle);
    }

    function getSelectedVehicle() {
        if (!vehicleSelect || !vehicleSelect.value) return null;
        return vehiclesData.find(v => String(v.vehicle_id) === String(vehicleSelect.value)) || null;
    }

    function updateOccupancy(vehicle) {
        if (!passengersEl) return;
        const pax = parseInt(passengersEl.value || '0', 10) || 0;
        const capacity = vehicle ? parseInt(vehicle.capacity || '0', 10) || 0 : 0;
        if (pax > 0 && capacity > 0) {
            const rate = (pax / capacity) * 100;
            const below = rate < 50;
            if (occupancyWarning) occupancyWarning.style.display = below ? 'block' : 'none';
            if (occupancyRemarksGroup) occupancyRemarksGroup.style.display = below ? 'block' : 'none';
        } else {
            if (occupancyWarning) occupancyWarning.style.display = 'none';
            if (occupancyRemarksGroup) occupancyRemarksGroup.style.display = 'none';
        }
    }

    function showDetails(v) {
        if (!detailsCard) return;
        
        const modelEl = document.getElementById('details-make-model');
        const plateEl = document.getElementById('details-plate');
        const typeEl  = document.getElementById('details-type');
        const colorEl = document.getElementById('details-color');
        const yearEl  = document.getElementById('details-year');
        const capEl   = document.getElementById('details-capacity');

        if (modelEl) modelEl.textContent = v.make_model;
        if (plateEl) plateEl.textContent = v.plate_number;
        if (typeEl)  typeEl.textContent  = v.vehicle_type;
        if (colorEl) colorEl.textContent = v.color || 'N/A';
        if (yearEl)  yearEl.textContent  = v.year || 'N/A';
        if (capEl)   capEl.textContent   = v.capacity + ' passengers max';

        detailsCard.style.display = 'block';
    }

    function hideDetails() {
        if (detailsCard) detailsCard.style.display = 'none';
    }

    // ── Form submission with validation ──────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const required = form.querySelectorAll('[required]');
        let valid = true;

        required.forEach(field => {
            field.style.borderColor = '';
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                valid = false;
            }
        });

        if (!valid) {
            VRS.notify.warning('Please fill in all required fields.');
            return;
        }

        // Vehicle selection is required
        if (!vehicleSelect || !vehicleSelect.value) {
            VRS.notify.warning('Please select a vehicle for this trip.');
            const section = document.querySelector('.vehicle-selection-section');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                section.classList.add('highlight-shake');
                setTimeout(() => section.classList.remove('highlight-shake'), 600);
            }
            if (vehicleSelect) vehicleSelect.style.borderColor = 'var(--danger)';
            return;
        }

        const selectedVehicle = getSelectedVehicle();
        if (selectedVehicle && passengersEl) {
            const pax = parseInt(passengersEl.value || '0', 10) || 0;
            if (pax > 0 && pax > selectedVehicle.capacity) {
                VRS.notify.warning('Passenger count exceeds vehicle capacity.');
                return;
            }

            const rate = (pax / selectedVehicle.capacity) * 100;
            if (rate < 50) {
                const remarks = requesterRemarksEl ? requesterRemarksEl.value.trim() : '';
                if (!remarks) {
                    VRS.notify.warning('Please provide a justification for low occupancy.');
                    if (occupancyRemarksGroup) {
                        occupancyRemarksGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
            }
        }

        if (depDateEl && depDateEl.value < today) {
            VRS.notify.warning('Departure date cannot be in the past.');
            depDateEl.style.borderColor = 'var(--danger)';
            return;
        }

        if (depDateEl && retDateEl && retDateEl.value < depDateEl.value) {
            VRS.notify.warning('Return date must be on or after departure date.');
            retDateEl.style.borderColor = 'var(--danger)';
            return;
        }

        await VRS.ajax.submitForm(form, {
            submitBtn: submitBtn,
            onSuccess: (data) => {
                VRS.notify.success(data.message || 'Reservation submitted successfully!');
                if (data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 1000);
                }
            },
        });
    });

})();
