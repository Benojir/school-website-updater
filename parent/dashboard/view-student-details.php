<?php
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Student Dashboard - " . $school_name . "</title>";
include_once("../../includes/header-close.php");

// Validate student id from get request
if (!isset($_GET['student_id'])) {
    die("Student ID not provided.");
}

$student_id = $_GET['student_id'];

// check admin permission + student permission
if (isLoggedIn()) { // check any admin logged in
    include_once("../../includes/dashboard-navbar.php");
    if (!hasPermission(PERM_MANAGE_EXAMS) && !hasPermission(PERM_MANAGE_STUDENTS) && !hasPermission(PERM_MANAGE_FEES)) {
        include_once("../../includes/permission-denied.php");
    }
} else {
    if (isParentAuthenticated()) {
        include_once("../../includes/parent-dashboard-navbar.php");
        if (!hasParentAccessPermission($student_id)) {
            include_once("../../includes/permission-denied.php");
        }
    } else {
        echo '<script>location.replace("/");</script>';
        exit;
    }
}

// Get student details
$stmt = $pdo->prepare("
    SELECT students.*, 
           classes.class_name, 
           sections.section_name
    FROM students
    LEFT JOIN classes ON students.class_id = classes.id
    LEFT JOIN sections ON students.section_id = sections.id
    WHERE students.student_id = :student_id
");

$stmt->execute([':student_id' => $student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Get unpaid fees
$unpaid_stmt = $pdo->prepare("
    SELECT * FROM student_unpaid_fees 
    WHERE student_id = :student_id
    ORDER BY STR_TO_DATE(CONCAT('01 ', month_year), '%d %M %Y') ASC
");
$unpaid_stmt->execute([':student_id' => $student_id]);
$unpaid_fees = $unpaid_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get full paid fees
$paid_stmt = $pdo->prepare("
    SELECT * FROM student_full_paid_fees 
    WHERE student_id = :student_id
    ORDER BY STR_TO_DATE(CONCAT('01 ', month_year), '%d %M %Y') ASC
");
$paid_stmt->execute([':student_id' => $student_id]);
$paid_fees = $paid_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get partial payments for unpaid fees
$partial_payments_for_unpaid_table = [];
foreach ($unpaid_fees as $unpaid_fee) {
    $stmt = $pdo->prepare("
        SELECT * FROM student_partial_payments 
        WHERE unpaid_fees_id = :unpaid_id
        ORDER BY STR_TO_DATE(CONCAT('01 ', month_year), '%d %M %Y') ASC
    ");
    $stmt->execute([':unpaid_id' => $unpaid_fee['id']]);
    $partial_payments_for_unpaid_table[$unpaid_fee['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get partial payments for paid fees
$partial_payments_for_paid_table = [];
foreach ($paid_fees as $paid_fee) {
    $stmt = $pdo->prepare("
        SELECT * FROM student_partial_payments 
        WHERE full_paid_fees_id = ?
        ORDER BY STR_TO_DATE(CONCAT('01 ', month_year), '%d %M %Y') ASC
    ");
    $stmt->execute([$paid_fee['id']]);
    $partial_payments_for_paid_table[$paid_fee['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate totals
$total_amount = 0;
$total_paid = 0;
$total_unpaid = 0;

foreach ($paid_fees as $paid_fee) {
    $total_amount += $paid_fee['actual_amount'];
    $total_paid += $paid_fee['total_paid_amount'];
}

foreach ($unpaid_fees as $paid_fee) {
    $total_amount += $paid_fee['actual_amount'];
    $total_unpaid += $paid_fee['unpaid_amount'];
}

$all_fees_paid = ($total_unpaid <= 0);

// Get wallet balance
$wallet_stmt = $pdo->prepare("SELECT * FROM student_wallet WHERE student_id = :student_id");
$wallet_stmt->execute([':student_id' => $student_id]);
$wallet = $wallet_stmt->fetch(PDO::FETCH_ASSOC);
$wallet_balance = $wallet ? $wallet['balance'] : 0;

// Get wallet transactions
$wallet_trans_stmt = $pdo->prepare("
    SELECT * FROM wallet_transactions 
    WHERE student_id = :student_id
    ORDER BY created_at DESC
    LIMIT 10
");
$wallet_trans_stmt->execute([':student_id' => $student_id]);
$wallet_transactions = $wallet_trans_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get admission fees
$stmt = $pdo->prepare("
    SELECT 
    student_admission_fees.*, 
    classes.class_name
    FROM student_admission_fees
    LEFT JOIN classes ON classes.id = student_admission_fees.class_id
    WHERE student_admission_fees.student_id = :student_id 
    AND student_admission_fees.payment_status = 'unpaid';
");
$stmt->execute([':student_id' => $student_id]);
$admission_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admission_fees as $admission_fee) {
    $total_unpaid += $admission_fee['unpaid_amount'];
}

// Get student permissions
$permissions_stmt = $pdo->prepare("
    SELECT 
        override_admit_check,
        allow_admit_card,
        override_marksheet_check,
        allow_marksheet
    FROM student_permissions
    WHERE student_id = :student_id
");
$permissions_stmt->execute([':student_id' => $student_id]);
$permissions = $permissions_stmt->fetch(PDO::FETCH_ASSOC);

// Determine download permissions
$can_download_admit = $all_fees_paid; // Default to fee status
$can_download_marksheet = $all_fees_paid; // Default to fee status

if ($permissions) {
    // Check admit card override
    if ($permissions['override_admit_check']) {
        $can_download_admit = $permissions['allow_admit_card'];
    }

    // Check marksheet override
    if ($permissions['override_marksheet_check']) {
        $can_download_marksheet = $permissions['allow_marksheet'];
    }
}

// Get total count of payment history records for pagination
$payment_history_count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM student_payment_history 
    WHERE student_id = :student_id
");
$payment_history_count_stmt->execute([':student_id' => $student_id]);
$payment_history_count = $payment_history_count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="container mt-5" id="student-report">
    <div class="card shadow mb-5">
        <!-- Student Header Section -->
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-user-graduate"></i> Student Profile</h3>
                <div class="d-print-none">
                    <button onclick="window.print()" class="btn btn-light">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['paid_all'])): ?>
            <div class="alert alert-success d-print-none m-3">
                <i class="fas fa-check-circle"></i> All unpaid fees have been paid.
            </div>
        <?php endif; ?>

        <!-- Student Information Section -->
        <div class="card-body">
            <div class="row">
                <!-- Student Photo Column -->
                <div class="col-md-3 text-center mb-4 mb-md-0">
                    <div class="student-photo-container">
                        <img src="../../uploads/students/<?= safe_htmlspecialchars($student['student_image']) ?>"
                            class="img-thumbnail rounded-circle shadow"
                            style="width: 200px; height: 200px; object-fit: cover;"
                            data-src="../../uploads/students/<?= safe_htmlspecialchars($student['student_image']) ?>"
                            data-fancybox=""
                            data-caption="<?= safe_htmlspecialchars($student['name']) ?>"
                            alt="Student Image">
                        <h4 class="mt-3"><?= safe_htmlspecialchars($student['name']) ?></h4>
                        <p class="text-muted">ID: <?= safe_htmlspecialchars($student['student_id']) ?></p>
                    </div>
                </div>

                <!-- Student Details Column -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <h5><i class="fas fa-id-card"></i> Basic Information</h5>
                                <hr>
                                <p><strong>Class:</strong> <?= safe_htmlspecialchars($student['class_name']) ?> - <?= safe_htmlspecialchars($student['section_name']) ?></p>
                                <p><strong>Roll No:</strong> <?= safe_htmlspecialchars($student['roll_no']) ?></p>
                                <p><strong>Admission Date:</strong> <?= safe_htmlspecialchars($student['admission_date']) ?></p>
                                <p><strong>Date of Birth:</strong> <?= safe_htmlspecialchars($student['date_of_birth']) ?></p>
                                <p><strong>Gender:</strong> <?= safe_htmlspecialchars($student['gender']) ?></p>
                                <p><strong>Blood Group:</strong> <?= safe_htmlspecialchars($student['blood_group']) ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-<?= $student['status'] === 'Active' ? 'success' : 'danger' ?>"><?= safe_htmlspecialchars($student['status']) ?></span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <h5><i class="fas fa-home"></i> Family Information</h5>
                                <hr>
                                <p><strong>Father's Name:</strong> <?= safe_htmlspecialchars($student['father_name']) ?></p>
                                <p><strong>Mother's Name:</strong> <?= safe_htmlspecialchars($student['mother_name']) ?></p>
                                <p><strong>Father's Occupation:</strong> <?= safe_htmlspecialchars($student['father_occupation']) ?></p>
                                <p><strong>Mother's Occupation:</strong> <?= safe_htmlspecialchars($student['mother_occupation']) ?></p>
                                <p><strong>Phone:</strong> <?= safe_htmlspecialchars($student['phone_number']) ?></p>
                                <p><strong>Alternate Phone:</strong> <?= safe_htmlspecialchars($student['alternate_phone_number']) ?></p>
                                <p><strong>Address:</strong> <?= safe_htmlspecialchars($student['address']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Summary Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-money-bill-wave"></i> Fee Summary
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 rounded bg-light">
                                <h5>Total Unpaid</h5>
                                <p class="fs-4 fw-bold text-danger">₹<?= number_format($total_unpaid, 2) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <h5>Wallet Balance</h5>
                                <p class="fs-4 fw-bold text-success">₹<?= number_format($wallet_balance, 2) ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($total_unpaid > 0): ?>
                        <div class="text-center mt-4">
                            <form action="../../management/action/pay-all-fees.php" method="POST" onsubmit="return confirm('Are you sure you want to mark all unpaid fees as paid?');">
                                <input type="hidden" name="student_id" value="<?= safe_htmlspecialchars($student_id) ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-money-check-alt"></i> Pay All Unpaid Fees
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wallet Transactions Section -->
            <?php if (!empty($wallet_transactions)): ?>
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-wallet"></i> Recent Wallet Transactions
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wallet_transactions as $transaction): ?>
                                        <tr>
                                            <td><?= date('d M Y h:i A', strtotime($transaction['created_at'])) ?></td>
                                            <td>
                                                <?php
                                                $type_class = '';
                                                $type_text = '';
                                                if ($transaction['transaction_type'] === 'deposit') {
                                                    $type_class = 'success';
                                                    $type_text = 'Deposit';
                                                } elseif ($transaction['transaction_type'] === 'deduct') {
                                                    $type_class = 'danger';
                                                    $type_text = 'Deduct';
                                                } else {
                                                    $type_class = 'info';
                                                    $type_text = ucfirst(str_replace('_', ' ', $transaction['transaction_type']));
                                                }
                                                ?>
                                                <span class="badge bg-<?= $type_class ?>"><?= $type_text ?></span>
                                            </td>
                                            <td class="<?= $transaction['transaction_type'] === 'deposit' ? 'text-success' : 'text-danger' ?>">
                                                <?= ($transaction['transaction_type'] === 'deposit' ? '+' : '-') ?>
                                                ₹<?= number_format($transaction['amount'], 2) ?>
                                            </td>
                                            <td><?= safe_htmlspecialchars($transaction['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($wallet_transactions) >= 10): ?>
                            <div class="text-center mt-3">
                                <a href="wallet-transactions.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-outline-secondary">
                                    View All Transactions
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Download Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-download"></i> Download Documents
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- ID Card (Always available) -->
                        <div class="col-md-4 mb-3">
                            <div class="download-card p-3 text-center border rounded">
                                <i class="fas fa-id-card fa-3x text-primary mb-3"></i>
                                <h4>Student ID Card</h4>
                                <p>Download student's official ID card</p>
                                <a href="../../management/action/download-bulk-id-card.php?student_ids=<?= urlencode($student_id) ?>" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download ID Card
                                </a>
                            </div>
                        </div>

                        <?php if ($can_download_admit || $can_download_marksheet): ?>
                            <!-- Admit Card -->
                            <div class="col-md-4 mb-3">
                                <div class="download-card p-3 text-center border rounded">
                                    <i class="fas fa-id-card fa-3x text-warning mb-3"></i>
                                    <h4>Admit Card</h4>

                                    <?php if ($permissions && $permissions['override_admit_check'] && !$all_fees_paid): ?>
                                        <div class="alert alert-info p-2 mb-2">
                                            <small><i class="fas fa-info-circle"></i> Allowed by admin override</small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($can_download_admit): ?>
                                        <p>Download student's examination admit card</p>
                                        <a href="student-admit-cards.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-warning text-white">
                                            <i class="fas fa-download"></i> Download Admit
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">Admit card not available</p>
                                        <button class="btn btn-warning text-white" disabled>
                                            <i class="fas fa-ban"></i> Not Available
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Marksheet -->
                            <div class="col-md-4">
                                <div class="download-card p-3 text-center border rounded">
                                    <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                                    <h4>Marksheet</h4>

                                    <?php if ($permissions && $permissions['override_marksheet_check'] && !$all_fees_paid): ?>
                                        <div class="alert alert-info p-2 mb-2">
                                            <small><i class="fas fa-info-circle"></i> Allowed by admin override</small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($can_download_marksheet): ?>
                                        <p>Download student's examination marksheet</p>
                                        <a href="student-marksheets.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-info text-white">
                                            <i class="fas fa-download"></i> Download Marksheet
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">Marksheet not available</p>
                                        <button class="btn btn-info text-white" disabled>
                                            <i class="fas fa-ban"></i> Not Available
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Fee Payment Reminder or Permission Info -->
                            <div class="col-md-8">
                                <div class="alert alert-warning h-100 d-flex align-items-center">
                                    <div>
                                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                    </div>
                                    <div>
                                        <h5>Download Restrictions</h5>
                                        <p class="mb-0">
                                            <?php if (!$all_fees_paid): ?>
                                                All fees must be paid to download admit cards and marksheets.
                                            <?php endif; ?>
                                            <?php if ($permissions && $permissions['override_admit_check'] && !$permissions['allow_admit_card']): ?>
                                                <br>Admit card download has been specifically disabled by admin.
                                            <?php endif; ?>
                                            <?php if ($permissions && $permissions['override_marksheet_check'] && !$permissions['allow_marksheet']): ?>
                                                <br>Marksheet download has been specifically disabled by admin.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Fees History Section -->
            <div class="card mb-4 border border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-history"></i> Payments History
                </div>
                <div class="card-body bg-info bg-opacity-10">
                    <div class="table-responsive">
                        <table class="table table-hover text-center align-middle" id="paymentsHistoryTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                        <div id="paymentHistoryPagination" class="d-flex justify-content-center mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Unpaid Admission Fees Section -->
            <?php if (count($admission_fees) > 0): ?>
                <div class="card mb-4 border border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-circle"></i> Unpaid Admission Fees
                    </div>
                    <div class="card-body bg-danger bg-opacity-10">
                        <div class="table-responsive">
                            <table class="table table-hover text-center align-middle" id="unpaidAdmissionFeesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Admission Class</th>
                                        <th>Admission Fee</th>
                                        <th>Unpaid Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Remark</th>
                                        <th class="d-print-none">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admission_fees as $admission_fee): ?>
                                        <tr class="bg-white">
                                            <td><?= safe_htmlspecialchars($admission_fee['class_name']) ?></td>
                                            <td>₹<?= number_format($admission_fee['admission_fee'], 2) ?></td>
                                            <td class="text-danger fw-bold">₹<?= number_format($admission_fee['unpaid_amount'], 2) ?></td>
                                            <td><?= safe_htmlspecialchars($admission_fee['admission_date']) ?></td>
                                            <td><span class="badge text-bg-danger"><?= safe_htmlspecialchars(ucwords($admission_fee['payment_status']))?></span></td>
                                            <td width="20%"><?= safe_htmlspecialchars($admission_fee['remark']) ?></td>
                                            <td class="d-print-none">
                                                <a href="../ajax/pay-fee.php?id=<?= urlencode($admission_fee['id'])?>&type=admission-fee"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-money-bill-wave"></i> Pay Now
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Unpaid Fees Section -->
            <div class="card mb-4 border border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-circle"></i> Unpaid Fees
                </div>
                <div class="card-body bg-danger bg-opacity-10">
                    <div class="table-responsive">
                        <table class="table table-hover text-center align-middle" id="unpaidFeesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th>Month/Year</th>
                                    <th>Total Amount</th>
                                    <th>Offer Amount</th>
                                    <th>Discount</th>
                                    <th>Unpaid Amount</th>
                                    <th>Remark</th>
                                    <th class="d-print-none">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($unpaid_fees) > 0): ?>
                                    <?php foreach ($unpaid_fees as $unpaid_fee): ?>
                                        <tr class="bg-white">
                                            <td class="details-control"></td>
                                            <td><?= safe_htmlspecialchars($unpaid_fee['month_year']) ?></td>
                                            <td>₹<?= number_format($unpaid_fee['actual_amount'], 2) ?></td>
                                            <td class="text-danger fw-bold">₹<?= number_format($unpaid_fee['actual_amount'] - $unpaid_fee['discount_amount'], 2) ?></td>
                                            <td>₹<?= number_format($unpaid_fee['discount_amount'], 2) ?></td>
                                            <td class="text-danger fw-bold">₹<?= number_format($unpaid_fee['unpaid_amount'], 2) ?></td>
                                            <td width="20%"><?= safe_htmlspecialchars($unpaid_fee['remark']) ?></td>
                                            <td class="d-print-none">
                                                <a href="../ajax/pay-fee.php?id=<?= urlencode($unpaid_fee['id'])?>&type=monthly-fee"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-money-bill-wave"></i> Pay Now
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                            No unpaid fees found for this student.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paid Fees Section -->
            <div class="card mb-4 border border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle"></i> Paid Fees
                </div>
                <div class="card-body bg-success bg-opacity-10">
                    <div class="table-responsive">
                        <table class="table table-hover text-center align-middle" id="paidFeesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th>Month/Year</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Discount</th>
                                    <th>Remark</th>
                                    <th>Payment Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($paid_fees) > 0): ?>
                                    <?php foreach ($paid_fees as $paid_fee): ?>
                                        <tr class="bg-white">
                                            <td class="details-control"></td>
                                            <td><?= safe_htmlspecialchars($paid_fee['month_year']) ?></td>
                                            <td>₹<?= number_format($paid_fee['actual_amount'], 2) ?></td>
                                            <td class="text-success fw-bold">₹<?= number_format($paid_fee['total_paid_amount'], 2) ?></td>
                                            <td>₹<?= number_format($paid_fee['discount_amount'], 2) ?></td>
                                            <td width="20%"><?= safe_htmlspecialchars($paid_fee['remark']) ?></td>
                                            <td><?= date('d M Y', strtotime($paid_fee['created_at'])) ?></td>
                                            <td><button onclick="downloadReciept(<?= $paid_fee['id'] ?>)" class="btn btn-sm btn-primary"><i class="fas fa-file"></i> Reciept</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                            No paid fees found for this student.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript code after the tables -->
<script>
    function downloadReciept(paid_id) {
        window.open("../ajax/download-paid-reciept.php?id=" + paid_id, "_blank");
    }

    $(document).ready(function() {
        // Format function for child rows
        function format(d, partialPayments) {
            if (!partialPayments || partialPayments.length === 0) {
                return '<div class="p-3 bg-warning bg-opacity-10">No partial payments found.</div>';
            }

            let html = `
        <div class="p-3 border border-warning bg-warning bg-opacity-10">
            <h6 class="mb-3 text-start fw-bold"><i class="fas fa-receipt"></i> Partial Payment Details:</h6>
            <table class="table table-sm table-bordered mb-0 text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="25%" class="text-center">Date</th>
                        <th width="25%" class="text-center">Partial Paid Amount</th>
                        <th width="20%" class="text-center">Method</th>
                        <th width="30%" class="text-center">Remark</th>
                    </tr>
                </thead>
                <tbody>`;

            partialPayments.forEach(payment => {
                html += `
            <tr>
                <td>${formatDate(payment.created_at)}</td>
                <td class="text-success fw-bold">₹${parseFloat(payment.partial_paid_amount).toFixed(2)}</td>
                <td>${payment.method ? payment.method.charAt(0).toUpperCase() + payment.method.slice(1) : '-'}</td>
                <td>${escapeHtml(payment.remark || '-')}</td>
            </tr>`;
            });

            html += `</tbody></table></div>`;
            return html;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Check if tables have data before initializing DataTables
        const hasUnpaidData = <?= count($unpaid_fees) > 0 ? 'true' : 'false' ?>;
        const hasPaidData = <?= count($paid_fees) > 0 ? 'true' : 'false' ?>;

        let unpaidTable, paidTable;

        // Initialize DataTables for unpaid fees table only if there's data
        if (hasUnpaidData) {
            unpaidTable = $('#unpaidFeesTable').DataTable({
                "responsive": true,
                "columnDefs": [{
                        "orderable": false,
                        "targets": [0, 6] // Column 0 (details) and 6 (action) are not orderable
                    },
                    {
                        "className": "dt-center",
                        "targets": "_all"
                    }
                ]
            });
        }

        // Initialize DataTables for paid fees table only if there's data
        if (hasPaidData) {
            paidTable = $('#paidFeesTable').DataTable({
                "responsive": true,
                "columnDefs": [{
                        "orderable": false,
                        "targets": [0] // Only column 0 (details) is not orderable
                    },
                    {
                        "className": "dt-center",
                        "targets": "_all"
                    }
                ]
            });
        }

        // Add event listener for opening and closing details on unpaid fees table
        if (hasUnpaidData && unpaidTable) {
            $('#unpaidFeesTable tbody').on('click', 'td.details-control', function() {
                const tr = $(this).closest('tr');
                const row = unpaidTable.row(tr);
                const monthYear = row.data()[1];

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Find the corresponding fee to get partial payments
                    const fee = <?= json_encode($unpaid_fees) ?>.find(f => f.month_year === monthYear);
                    const partialPayments = fee ? <?= json_encode($partial_payments_for_unpaid_table) ?>[fee.id] || [] : [];

                    row.child(format(row.data(), partialPayments)).show();
                    tr.addClass('shown');
                }
            });
        }

        // Add event listener for opening and closing details on paid fees table
        if (hasPaidData && paidTable) {
            $('#paidFeesTable tbody').on('click', 'td.details-control', function() {
                const tr = $(this).closest('tr');
                const row = paidTable.row(tr);
                const monthYear = row.data()[1];

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Find the corresponding fee to get partial payments
                    const fee = <?= json_encode($paid_fees) ?>.find(f => f.month_year === monthYear);
                    const partialPayments = fee ? <?= json_encode($partial_payments_for_paid_table) ?>[fee.id] || [] : [];

                    row.child(format(row.data(), partialPayments)).show();
                    tr.addClass('shown');
                }
            });
        }
    });

    // Get payment history
    // Payment History AJAX Pagination
    $(document).ready(function() {
        const studentId = '<?= $student_id ?>';
        const perPage = 10; // Items per page
        let currentPage = 1;
        const totalItems = <?= $payment_history_count ?>;
        const totalPages = Math.ceil(totalItems / perPage);

        // Function to format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Function to load payment history data
        function loadPaymentHistory(page) {
            $.ajax({
                url: '../../management/ajax/get-payment-history.php',
                type: 'GET',
                data: {
                    student_id: studentId,
                    page: page,
                    per_page: perPage
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#paymentsHistoryTable tbody').html(
                        '<tr><td colspan="4" class="text-center py-4">' +
                        '<div class="spinner-border text-primary" role="status">' +
                        '<span class="visually-hidden">Loading...</span></div></td></tr>'
                    );
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function(payment) {
                            html += `
                            <tr>
                                <td width="26%">${formatDate(payment.payment_date)}</td>
                                <td width="24%" class="text-success fw-bold">₹${parseFloat(payment.payment_amount).toFixed(2)}</td>
                                <td width="10%" class="text-capitalize">${payment.method || '-'}</td>
                                <td width="40%">${payment.remark || '-'}</td>
                            </tr>
                        `;
                        });
                        $('#paymentsHistoryTable tbody').html(html);
                        renderPagination(response.current_page, response.total_pages);
                    } else {
                        $('#paymentsHistoryTable tbody').html(
                            '<tr><td colspan="4" class="text-center text-muted py-4">' +
                            '<i class="fas fa-info-circle fa-2x mb-2"></i><br>' +
                            'No payment history found for this student.</td></tr>'
                        );
                        $('#paymentHistoryPagination').empty();
                    }
                },
                error: function() {
                    $('#paymentsHistoryTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-danger py-4">' +
                        '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>' +
                        'Failed to load payment history. Please try again.</td></tr>'
                    );
                }
            });
        }

        // Function to render pagination controls
        function renderPagination(currentPage, totalPages) {
            let paginationHtml = '<nav><ul class="pagination">';

            // Previous button
            paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>`;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            // Next button
            paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>`;

            paginationHtml += '</ul></nav>';
            $('#paymentHistoryPagination').html(paginationHtml);
        }

        // Handle pagination clicks
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                loadPaymentHistory(currentPage);
            }
        });

        // Initial load
        if (totalItems > 0) {
            loadPaymentHistory(1);
        } else {
            $('#paymentsHistoryTable tbody').html(
                '<tr><td colspan="4" class="text-center text-muted py-4">' +
                '<i class="fas fa-info-circle fa-2x mb-2"></i><br>' +
                'No payment history found for this student.</td></tr>'
            );
        }
    });
</script>

<style>
    @media print {
        body {
            background: white;
            font-size: 12pt;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .d-print-none {
            display: none !important;
        }

        .badge {
            color: white !important;
            border: 1px solid #000;
        }

        .student-photo-container {
            text-align: left !important;
            margin-bottom: 20px;
        }

        .img-thumbnail {
            border: none !important;
            max-width: 150px !important;
            max-height: 150px !important;
        }
    }

    .info-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
    }

    .download-card {
        transition: all 0.3s ease;
        height: 100%;
    }

    .download-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .student-photo-container {
        position: relative;
    }

    .student-photo-container h4 {
        color: #333;
        font-weight: 600;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    td.details-control {
        background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }

    tr.shown td.details-control {
        background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1em !important;
    }

    #paymentHistoryPagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    #paymentHistoryPagination .page-link {
        color: #FFFFFF;
    }

    #paymentHistoryPagination .page-item.disabled .page-link {
        color: #555555;
    }

    #paymentHistoryPagination .page-link:hover {
        color: #FFFFFF;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
</style>

<?php include_once("../../includes/body-close.php"); ?>