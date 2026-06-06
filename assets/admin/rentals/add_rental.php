<?php
// rentals/add_rental.php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Fetch all available products for booking
$products_res = $conn->query("SELECT id, name, category, size, color, rental_price, security_deposit FROM products WHERE status = 'Available' ORDER BY id DESC");
$products = [];
if ($products_res && $products_res->num_rows > 0) {
    while($row = $products_res->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch all customers/grooms
$customers_res = $conn->query("SELECT id, name, phone FROM customers ORDER BY name ASC");
$customers = [];
if ($customers_res && $customers_res->num_rows > 0) {
    while($row = $customers_res->fetch_assoc()) {
        $customers[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $customer_id = intval($_POST['customer_id']);
    $trial_date = $_POST['trial_date'];
    $event_date = $_POST['event_date'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    
    $rental_price = floatval($_POST['rental_price']);
    $security_deposit_paid = floatval($_POST['security_deposit_paid']);
    $advance_paid = floatval($_POST['advance_paid']);
    $balance_pending = floatval($_POST['balance_pending']);
    
    $alteration_notes = trim($_POST['alteration_notes']);
    
    if ($product_id <= 0 || $customer_id <= 0 || empty($trial_date) || empty($event_date) || empty($pickup_date) || empty($return_date) || $rental_price <= 0) {
        $error = "Please fill in all mandatory fields with valid information.";
    } else {
        // Start Transaction
        $conn->begin_transaction();
        
        try {
            // 1. Insert into rentals
            $ins_stmt = $conn->prepare("INSERT INTO rentals (product_id, customer_id, trial_date, event_date, pickup_date, return_date, rental_price, security_deposit_paid, advance_paid, balance_pending, alteration_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Booked')");
            if (!$ins_stmt) {
                throw new Exception("Failed to prepare rental insertion query.");
            }
            
            $ins_stmt->bind_param("iisssddddds", $product_id, $customer_id, $trial_date, $event_date, $pickup_date, $return_date, $rental_price, $security_deposit_paid, $advance_paid, $balance_pending, $alteration_notes);
            if (!$ins_stmt->execute()) {
                throw new Exception("Failed to book rental: " . $ins_stmt->error);
            }
            $ins_stmt->close();
            
            // 2. Update product status to Rented
            $upd_stmt = $conn->prepare("UPDATE products SET status = 'Rented' WHERE id = ?");
            if (!$upd_stmt) {
                throw new Exception("Failed to prepare product update query.");
            }
            $upd_stmt->bind_param("i", $product_id);
            if (!$upd_stmt->execute()) {
                throw new Exception("Failed to update apparel availability.");
            }
            $upd_stmt->close();
            
            // Commit transaction
            $conn->commit();
            $success = "Wedding trousseau successfully booked and scheduled!";
            
            // Reload available products list
            $products_res = $conn->query("SELECT id, name, category, size, color, rental_price, security_deposit FROM products WHERE status = 'Available' ORDER BY id DESC");
            $products = [];
            if ($products_res && $products_res->num_rows > 0) {
                while($row = $products_res->fetch_assoc()) {
                    $products[] = $row;
                }
            }
            
            $_POST = array();
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Book Wedding Trousseau</h1>
        <p>Schedule fittings, trial dates, lock in security deposits, and note tailor alterations.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/rentals/view_rentals.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> View Bookings
    </a>
</div>

<div class="glass-panel" style="max-width: 900px; margin: 0 auto; padding: 3rem;">
    <h2 style="color: var(--accent-color); font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <i class="fa-solid fa-calendar-check text-gold" style="margin-right: 0.5rem;"></i> Wedding Booking Ledger
    </h2>

    <?php if($success): ?>
        <div style="background: var(--success-bg); color: var(--success); padding: 1rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(46, 125, 50, 0.2); font-weight: 600;">
            <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div style="background: var(--danger-bg); color: var(--danger); padding: 1rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(211, 47, 47, 0.2); font-weight: 600;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" id="bookingForm">
        
        <div class="form-grid-2">
            <!-- Customer Select -->
            <div class="form-group">
                <label for="customer_id">Select Groom Client *</label>
                <select id="customer_id" name="customer_id" class="form-control" required style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1.2rem center; background-size: 1.2em;">
                    <option value="">-- Select Registered Groom --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']) . " (" . htmlspecialchars($c['phone']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.3rem;">
                    Groom not registered? <a href="<?php echo $base_url; ?>/admin/customers/add_customer.php" style="color: var(--gold-color); text-decoration: none; font-weight: 700;">Add profile first</a>.
                </p>
            </div>

            <!-- Product Select -->
            <div class="form-group">
                <label for="product_id">Select Available Apparel *</label>
                <select id="product_id" name="product_id" class="form-control" required style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1.2rem center; background-size: 1.2em;">
                    <option value="">-- Choose In-Stock Premium Outfit --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>" 
                                data-price="<?php echo $p['rental_price']; ?>" 
                                data-deposit="<?php echo $p['security_deposit']; ?>"
                                <?php echo (isset($_POST['product_id']) && $_POST['product_id'] == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars("[{$p['category']}] {$p['name']} (Size {$p['size']}) - ₹" . number_format($p['rental_price'], 0)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-grid-2" style="background: #fffcf8; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
            <!-- Dates Setup -->
            <div class="form-group">
                <label for="trial_date"><i class="fa-solid fa-scissors text-gold"></i> Fit Trial / Alteration Date *</label>
                <input type="date" id="trial_date" name="trial_date" class="form-control" required value="<?php echo isset($_POST['trial_date']) ? $_POST['trial_date'] : ''; ?>">
                <span style="font-size: 0.75rem; color: var(--text-secondary);">Scheduled trial to confirm fit before wedding pickup.</span>
            </div>

            <div class="form-group">
                <label for="event_date"><i class="fa-regular fa-bell text-gold"></i> Wedding / Event Date *</label>
                <input type="date" id="event_date" name="event_date" class="form-control" required value="<?php echo isset($_POST['event_date']) ? $_POST['event_date'] : ''; ?>">
                <span style="font-size: 0.75rem; color: var(--text-secondary);">The actual day of the marriage event.</span>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="pickup_date"><i class="fa-solid fa-truck-ramp-box"></i> Scheduled Pickup Date *</label>
                <input type="date" id="pickup_date" name="pickup_date" class="form-control" required value="<?php echo isset($_POST['pickup_date']) ? $_POST['pickup_date'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="return_date"><i class="fa-solid fa-rotate-left"></i> Scheduled Return Date *</label>
                <input type="date" id="return_date" name="return_date" class="form-control" required value="<?php echo isset($_POST['return_date']) ? $_POST['return_date'] : ''; ?>">
            </div>
        </div>

        <!-- Alteration instruction boxes (Groom wear custom feature!) -->
        <div class="form-group">
            <label for="alteration_notes"><i class="fa-solid fa-pencil text-gold"></i> Bespoke Alteration Instructions</label>
            <textarea id="alteration_notes" name="alteration_notes" class="form-control" placeholder="Specify tailor instructions e.g. Sleeve shorten by 0.5 inch, Waist waist adjustment loose 1 inch, Safa fitting 22.5 inches..."><?php echo isset($_POST['alteration_notes']) ? htmlspecialchars($_POST['alteration_notes']) : ''; ?></textarea>
        </div>

        <!-- Financial breakdown panels (with live calculation) -->
        <h3 style="color: var(--accent-color); font-size: 1.25rem; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1.5px solid #ebdccb; padding-bottom: 0.5rem;">
            <i class="fa-solid fa-wallet text-gold" style="margin-right: 0.4rem;"></i> Billing Split (Live Auto-calculation)
        </h3>

        <div class="form-grid-2" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            <div class="form-group">
                <label for="rental_price">Rental Fee (₹) *</label>
                <input type="number" step="0.01" min="0" id="rental_price" name="rental_price" class="form-control" required value="<?php echo isset($_POST['rental_price']) ? $_POST['rental_price'] : '0.00'; ?>">
            </div>

            <div class="form-group">
                <label for="security_deposit_paid">Security Deposit (₹) *</label>
                <input type="number" step="0.01" min="0" id="security_deposit_paid" name="security_deposit_paid" class="form-control" required value="<?php echo isset($_POST['security_deposit_paid']) ? $_POST['security_deposit_paid'] : '0.00'; ?>">
            </div>

            <div class="form-group">
                <label for="advance_paid">Advance Paid (₹) *</label>
                <input type="number" step="0.01" min="0" id="advance_paid" name="advance_paid" class="form-control" required value="<?php echo isset($_POST['advance_paid']) ? $_POST['advance_paid'] : '0.00'; ?>">
            </div>

            <div class="form-group" style="background: #faf6f0; padding: 0.2rem 0.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                <label for="balance_pending" style="color: var(--accent-color);">Balance Pending (₹)</label>
                <input type="number" step="0.01" id="balance_pending" name="balance_pending" class="form-control" readonly style="font-weight: 700; color: var(--accent-color); background: #eee5d8;" value="<?php echo isset($_POST['balance_pending']) ? $_POST['balance_pending'] : '0.00'; ?>">
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="reset" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-file-invoice-dollar"></i> Book Trousseau & Generate Invoice
            </button>
        </div>
    </form>
</div>

<!-- Embedded JS to dynamically populate price and calculate balance splits -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const rentalPriceInput = document.getElementById('rental_price');
    const securityDepositInput = document.getElementById('security_deposit_paid');
    const advancePaidInput = document.getElementById('advance_paid');
    const balancePendingInput = document.getElementById('balance_pending');
    
    // Autofill financial details upon product selection
    productSelect.addEventListener('change', function() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption && selectedOption.value !== "") {
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const deposit = parseFloat(selectedOption.getAttribute('data-deposit')) || 0;
            
            rentalPriceInput.value = price.toFixed(2);
            securityDepositInput.value = deposit.toFixed(2);
            
            // Set default advance as standard rental fee
            advancePaidInput.value = price.toFixed(2);
            
            recalculateBalance();
        }
    });
    
    // Financial balance calculation
    function recalculateBalance() {
        const rentalPrice = parseFloat(rentalPriceInput.value) || 0;
        const securityDeposit = parseFloat(securityDepositInput.value) || 0;
        const advancePaid = parseFloat(advancePaidInput.value) || 0;
        
        // Balance = Rental Fee + Deposit - Advance Paid
        const balancePending = (rentalPrice + securityDeposit) - advancePaid;
        balancePendingInput.value = balancePending.toFixed(2);
    }
    
    rentalPriceInput.addEventListener('input', recalculateBalance);
    securityDepositInput.addEventListener('input', recalculateBalance);
    advancePaidInput.addEventListener('input', recalculateBalance);
    
    // Automatically set default pickup/return relative to wedding event date
    const eventDateInput = document.getElementById('event_date');
    const trialDateInput = document.getElementById('trial_date');
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    
    eventDateInput.addEventListener('change', function() {
        const eventVal = eventDateInput.value;
        if(eventVal) {
            const eventDate = new Date(eventVal);
            
            // Pickup date = 2 days before event
            const pickupDate = new Date(eventDate);
            pickupDate.setDate(eventDate.getDate() - 2);
            pickupDateInput.value = formatDate(pickupDate);
            
            // Return date = 2 days after event
            const returnDate = new Date(eventDate);
            returnDate.setDate(eventDate.getDate() + 2);
            returnDateInput.value = formatDate(returnDate);
            
            // Trial date = 7 days before event (for fittings and tailored adjustments)
            const trialDate = new Date(eventDate);
            trialDate.setDate(eventDate.getDate() - 7);
            trialDateInput.value = formatDate(trialDate);
        }
    });
    
    function formatDate(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
