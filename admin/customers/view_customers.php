<?php
// customers/view_customers.php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    
    // Check if customer has active rentals
    $check_stmt = $conn->prepare("SELECT id FROM rentals WHERE customer_id = ? AND status IN ('Booked', 'Picked Up') LIMIT 1");
    if ($check_stmt) {
        $check_stmt->bind_param("i", $del_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Cannot delete this groom! He currently has an active wedding booking outstanding.";
        } else {
            // Delete customer
            $del_stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
            if ($del_stmt) {
                $del_stmt->bind_param("i", $del_id);
                if ($del_stmt->execute()) {
                    $success = "Groom profile successfully removed from registry.";
                } else {
                    $error = "Failed to delete groom profile. Please try again.";
                }
                $del_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Build Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query_str = "SELECT c.*, COUNT(r.id) as booking_count 
              FROM customers c 
              LEFT JOIN rentals r ON c.id = r.customer_id ";

if (!empty($search)) {
    $query_str .= " WHERE c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ? ";
}

$query_str .= " GROUP BY c.id ORDER BY c.id DESC";

$stmt = $conn->prepare($query_str);
if ($stmt && !empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query_str);
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Grooms Registry</h1>
        <p>Browse registered wedding couples, verify contact lines, and view booking history.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/customers/add_customer.php" class="btn btn-gold">
        <i class="fa-solid fa-user-plus"></i> Register Groom
    </a>
</div>

<!-- Success and Error Alerts -->
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

<!-- Filters and Search panel -->
<div class="glass-panel" style="padding: 1.5rem 2rem; margin-bottom: 2rem; max-width: 600px;">
    <form action="" method="GET" style="display: flex; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label for="search" style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fa-solid fa-magnifying-glass"></i> Find Groom</label>
            <input type="text" id="search" name="search" class="form-control" style="padding: 0.8rem 1rem;" placeholder="Search name, phone, or email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.5rem;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        <?php if(!empty($search)): ?>
            <a href="view_customers.php" class="btn btn-outline" style="padding: 0.8rem 1rem;"><i class="fa-solid fa-rotate-right"></i></a>
        <?php endif; ?>
    </form>
</div>

<!-- Grooms List Table -->
<div class="glass-panel table-container">
    <table>
        <thead>
            <tr>
                <th>Groom Details</th>
                <th>Primary Contact</th>
                <th>Alternate Contact</th>
                <th>Email Address</th>
                <th>Home Address</th>
                <th style="text-align: center;">Lifetime Bookings</th>
                <th class="no-print" style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $email_display = !empty($row['email']) ? $row['email'] : "<span style='color: var(--text-secondary); font-style: italic;'>N/A</span>";
                    $alt_phone_display = !empty($row['alt_phone']) ? $row['alt_phone'] : "<span style='color: var(--text-secondary); font-style: italic;'>N/A</span>";
                    $address_display = !empty($row['address']) ? htmlspecialchars($row['address']) : "<span style='color: var(--text-secondary); font-style: italic;'>N/A</span>";
                    
                    echo "<tr>
                            <td style='font-weight: 700; color: var(--accent-color);'>{$row['name']}</td>
                            <td><i class='fa-solid fa-phone text-gold' style='margin-right: 0.4rem; font-size: 0.85rem;'></i> <strong>{$row['phone']}</strong></td>
                            <td>{$alt_phone_display}</td>
                            <td>{$email_display}</td>
                            <td style='font-size: 0.9rem; max-width: 250px;'>{$address_display}</td>
                            <td style='text-align: center;'>
                                <span style='background: var(--accent-color); color: #fff; padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem;'>
                                    {$row['booking_count']} Bookings
                                </span>
                            </td>
                            <td class='no-print' style='text-align: center;'>
                                <a href='view_customers.php?action=delete&id={$row['id']}' class='btn btn-danger' style='padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px;' onclick='return confirm(\"Are you sure you want to delete this customer? This will clear all reference history if allowed.\");'>
                                    <i class='fa-solid fa-trash-can'></i> Delete
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center text-secondary' style='padding: 3rem;'>No registered grooms found matching your query.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
