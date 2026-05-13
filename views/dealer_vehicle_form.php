<?php
/**
 * Dealer Vehicle Add/Edit Form - uses same structure as admin but scoped to dealer
 * @var array $data
 */
$v = $data['vehicle'] ?? null;
$isEdit = ($data['mode'] ?? 'create') === 'edit';
?>
<div class="page-header">
    <div>
        <a href="index.php?page=dealer&action=my_vehicles" class="btn btn-ghost btn-xs"><i class="fas fa-arrow-left"></i> Back</a>
        <h2><?= $isEdit ? 'Edit Vehicle' : 'List New Vehicle' ?></h2>
    </div>
</div>

<form method="POST" action="index.php?page=dealer&action=<?= $isEdit ? 'update_vehicle' : 'store_vehicle' ?>">
    <?= Security::csrfField() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><?php endif; ?>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-car"></i> Vehicle Details</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Make <span class="required">*</span></label>
                        <select name="make" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach (VEHICLE_MAKES as $make): ?>
                            <option value="<?= $make ?>" <?= ($v['make'] ?? '') === $make ? 'selected' : '' ?>><?= $make ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Model <span class="required">*</span></label>
                        <input type="text" name="model" class="form-input" value="<?= htmlspecialchars($v['model'] ?? '') ?>" required placeholder="e.g. Swift, City">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Variant</label>
                        <input type="text" name="variant" class="form-input" value="<?= htmlspecialchars($v['variant'] ?? '') ?>" placeholder="e.g. VXI, Asta">
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= ($v['year'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Registration No.</label>
                        <input type="text" name="registration_no" class="form-input" value="<?= htmlspecialchars($v['registration_no'] ?? '') ?>" style="text-transform:uppercase">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-input" value="<?= htmlspecialchars($v['color'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Photo URL</label>
                    <input type="url" name="photo_url" class="form-input" value="<?= htmlspecialchars($v['photo_url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-cogs"></i> Specs & Pricing</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Fuel</label>
                        <select name="fuel_type" class="form-select">
                            <?php foreach (FUEL_TYPES as $ft): ?>
                            <option value="<?= $ft ?>" <?= ($v['fuel_type'] ?? 'Petrol') === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transmission</label>
                        <select name="transmission" class="form-select">
                            <option value="Manual" <?= ($v['transmission'] ?? 'Manual') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="Automatic" <?= ($v['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>KM Driven</label>
                        <input type="number" name="km_driven" class="form-input" value="<?= $v['km_driven'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Owner</label>
                        <select name="owner_count" class="form-select">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= ($v['owner_count'] ?? 1) == $i ? 'selected' : '' ?>><?= $i ?> Owner</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Asking Price (₹) <span class="required">*</span></label>
                        <input type="number" name="asking_price" class="form-input" value="<?= $v['asking_price'] ?? '' ?>" step="1000" required>
                    </div>
                    <div class="form-group">
                        <label>Body Type</label>
                        <select name="body_type" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Hatchback','Sedan','SUV','MUV','Pickup','Van','Bike','Scooter','Commercial'] as $bt): ?>
                            <option value="<?= $bt ?>" <?= ($v['body_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($v['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <a href="index.php?page=dealer&action=my_vehicles" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'List Vehicle' ?></button>
    </div>
</form>
