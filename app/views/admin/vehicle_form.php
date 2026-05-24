<?php
$isEdit    = !empty($vehicle);
$assignedDriverId = is_array($vehicle) ? ($vehicle['assigned_driver_id'] ?? null) : null;
$pageTitle = $isEdit ? 'Edit Vehicle' : 'Add Vehicle';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1><?= $isEdit ? 'Edit Vehicle' : 'Add Vehicle' ?></h1>
                <p><?= $isEdit ? 'Update vehicle details and status.' : 'Register a new vehicle to the fleet.' ?></p>
            </div>
        </section>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="panel active form-panel">
            <form method="POST"
                  action="<?= BASE_URL ?><?= $isEdit ? 'admin/vehicles/update' : 'admin/vehicles/store' ?>"
                  data-ajax-url="<?= BASE_URL ?><?= $isEdit ? 'api/vehicles/update' : 'api/vehicles/store' ?>"
                  id="vehicle-form" novalidate>
                <?= Controller::csrfField() ?>

                <?php if ($isEdit): ?>
                    <input type="hidden" name="vehicle_id" value="<?= (int)$vehicle['vehicle_id'] ?>"/>
                <?php endif; ?>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="plate_number">Plate Number <span class="required">*</span></label>
                        <input type="text" id="plate_number" name="plate_number"
                               value="<?= htmlspecialchars($vehicle['plate_number'] ?? '') ?>"
                               placeholder="e.g. USeP-0006" required/>
                    </div>

                    <div class="form-group">
                        <label for="make_model">Make / Model <span class="required">*</span></label>
                        <input type="text" id="make_model" name="make_model"
                               value="<?= htmlspecialchars($vehicle['make_model'] ?? '') ?>"
                               placeholder="e.g. Toyota HiAce Grandia" required/>
                    </div>

                    <div class="form-group">
                        <label for="vehicle_type">Vehicle Type</label>
                        <select id="vehicle_type" name="vehicle_type">
                            <?php foreach (['Van','Bus','SUV','Pickup','Sedan','Motorcycle'] as $t): ?>
                            <option value="<?= $t ?>"
                                <?= ($vehicle['vehicle_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Passenger Capacity <span class="required">*</span></label>
                        <input type="number" id="capacity" name="capacity"
                               min="1" max="100"
                               value="<?= (int)($vehicle['capacity'] ?? 1) ?>" required/>
                    </div>

                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year"
                               min="2000" max="<?= date('Y') + 1 ?>"
                               value="<?= htmlspecialchars($vehicle['year'] ?? date('Y')) ?>"/>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color"
                               value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>"
                               placeholder="e.g. White"/>
                    </div>

                    <div class="form-group">
                        <label for="assigned_driver_id">Assigned Driver</label>
                        <select id="assigned_driver_id" name="assigned_driver_id">
                            <option value="">— Unassigned —</option>
                            <?php foreach (($drivers ?? []) as $d): ?>
                            <option value="<?= (int)$d['driver_id'] ?>"
                                <?= (int)$assignedDriverId === (int)$d['driver_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['full_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (['available','in_use','maintenance','retired'] as $s): ?>
                            <option value="<?= $s ?>"
                                <?= ($vehicle['status'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_',' ',$s)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Condition remarks, maintenance history, etc."
                        ><?= htmlspecialchars($vehicle['notes'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>admin/vehicles" class="btn-outline">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <?= $isEdit ? 'Save Changes' : 'Add Vehicle' ?>
                    </button>
                </div>

            </form>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>
    const form = document.getElementById('vehicle-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await VRS.ajax.submitForm(form, {
                onSuccess: (data) => {
                    VRS.notify.success(data.message);
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 800);
                    }
                },
            });
        });
    }
</script>
