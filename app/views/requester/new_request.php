<?php
$pageTitle = 'New Reservation';
include VIEW_PATH . '/layouts/header.php';

// Generate time options in 30-minute increments
function timeOptions(): array {
    $options = [];
    for ($h = 5; $h <= 22; $h++) {
        foreach (['00', '30'] as $m) {
            if ($h === 22 && $m === '30') continue;
            $val  = sprintf('%02d:%s', $h, $m);
            $hour = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
            $ampm = $h >= 12 ? 'PM' : 'AM';
            $label = sprintf('%d:%s %s', $hour, $m, $ampm);
            $options[] = ['value' => $val, 'label' => $label];
        }
    }
    return $options;
}
$timeSlots = timeOptions();
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>New Reservation Request</h1>
                <p>Fill in the details below to request a university vehicle.</p>
            </div>
        </section>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <section class="panel active form-panel-wide">
            <form method="POST" action="<?= BASE_URL ?>requester/store"
                  data-ajax-url="<?= BASE_URL ?>api/reservations/store"
                  id="reservation-form" novalidate>
                <?= Controller::csrfField() ?>

                <div class="form-grid">

                    <div class="form-group full-width">
                        <label for="purpose">Purpose / Reason for Travel <span class="required">*</span></label>
                        <textarea id="purpose" name="purpose" rows="3"
                                  placeholder="Briefly describe the purpose of this trip..."
                                  required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="destination">Destination <span class="required">*</span></label>
                        <input type="text" id="destination" name="destination"
                               placeholder="e.g., USeP Obrero Campus, Tagum City" required/>
                    </div>

                    <div class="form-group">
                        <label for="passengers">Number of Passengers <span class="required">*</span></label>
                        <input type="number" id="passengers" name="passengers"
                               min="1" max="50" value="1" required/>
                    </div>

                    <!-- Departure Date & Time -->
                    <div class="form-group">
                        <label for="departure_date">Departure Date <span class="required">*</span></label>
                        <input type="date" id="departure_date" name="departure_date" required/>
                    </div>

                    <div class="form-group">
                        <label for="departure_time">Departure Time <span class="required">*</span></label>
                        <select id="departure_time" name="departure_time" required>
                            <option value="">— Select time —</option>
                            <?php foreach ($timeSlots as $t): ?>
                            <option value="<?= $t['value'] ?>"><?= $t['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Return Date & Time -->
                    <div class="form-group">
                        <label for="return_date">Return Date <span class="required">*</span></label>
                        <input type="date" id="return_date" name="return_date" required/>
                    </div>

                    <div class="form-group">
                        <label for="return_time">Return Time <span class="required">*</span></label>
                        <select id="return_time" name="return_time" required>
                            <option value="">— Select time —</option>
                            <?php foreach ($timeSlots as $t): ?>
                            <option value="<?= $t['value'] ?>"><?= $t['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- Vehicle Selection Section (REQUIRED) -->
                <div class="vehicle-selection-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Select Vehicle <span class="required">*</span>
                    </h3>
                    <p class="section-desc">Choose a vehicle for this trip by filtering by type and choosing from the list.</p>

                    <div class="vehicle-filter-row">
                        <div class="form-group">
                            <label for="vehicle_type_filter">Filter by Type</label>
                            <select id="vehicle_type_filter">
                                <option value="all">All Types</option>
                                <?php foreach ($vehicleTypes as $vt): ?>
                                <option value="<?= htmlspecialchars($vt) ?>"><?= htmlspecialchars($vt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="vehicle_id">Choose Vehicle <span class="required">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" required>
                                <option value="">— Select vehicle —</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selected vehicle details card (appears when a vehicle is chosen) -->
                    <div id="selected-vehicle-details-card" class="selected-vehicle-details-card" style="display:none;">
                        <h4 class="details-card-title">Vehicle Specifications</h4>
                        <div class="details-card-grid">
                            <div class="details-card-item">
                                <span class="details-card-label">Model/Make</span>
                                <span class="details-card-value" id="details-make-model"></span>
                            </div>
                            <div class="details-card-item">
                                <span class="details-card-label">Plate Number</span>
                                <span class="details-card-value" id="details-plate"></span>
                            </div>
                            <div class="details-card-item">
                                <span class="details-card-label">Type</span>
                                <span class="details-card-value" id="details-type"></span>
                            </div>
                            <div class="details-card-item">
                                <span class="details-card-label">Color</span>
                                <span class="details-card-value" id="details-color"></span>
                            </div>
                            <div class="details-card-item">
                                <span class="details-card-label">Model Year</span>
                                <span class="details-card-value" id="details-year"></span>
                            </div>
                            <div class="details-card-item">
                                <span class="details-card-label">Max Capacity</span>
                                <span class="details-card-value" id="details-capacity"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>requester/my_requests" class="btn-outline">Cancel</a>
                    <button type="submit" class="btn-primary" id="submit-btn">Submit Request</button>
                </div>

            </form>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/reservation.js?v=<?= time() ?>"></script>
