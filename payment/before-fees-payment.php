<?php
include_once("../includes/header-open.php");
echo "<title>Cofirm Payment - " . $school_name . "</title>";
include_once("../includes/header-close.php");

if ($websiteConfig['allow_online_payment'] !== 'yes') {
    echo '<div class="container mt-3"><div class="alert alert-danger">Online payment is not enabled. Please contact the school administration.</div></div>';
    include_once('../includes/body-close.php');
    exit();
}

// Get form data
if (
    !isset($_REQUEST['id']) || empty($_REQUEST['id']) || !is_numeric($_REQUEST['id'])
    || !isset($_REQUEST['type']) || empty($_REQUEST['type'])
) {
    die('Invalid request');
}

$unpaid_fee_id = trim($_REQUEST['id']);
$fee_type = trim($_REQUEST['type']);

if (!in_array($fee_type, ['admission-fee', 'monthly-fee'])) {
    die('Invalid payment request.');
}

$unpaid_fee_data = null;

if ($fee_type == "admission-fee") {
    // Fetch unpaid admission fees data
    $stmt = $pdo->prepare("
        SELECT student_admission_fees.*, students.name
        FROM student_admission_fees
        LEFT JOIN students ON students.student_id = student_admission_fees.student_id
        WHERE student_admission_fees.id = ? AND student_admission_fees.payment_status = 'unpaid' LIMIT 1
    ");
    $stmt->execute([$unpaid_fee_id]);
    $unpaid_fee_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($unpaid_fee_data)) {
        die('No unpaid fees found.');
    }
} else if ($fee_type == "monthly-fee") {
    // Fetch unpaid fees data
    $stmt = $pdo->prepare("
        SELECT student_unpaid_fees.*, students.*
        FROM student_unpaid_fees
        LEFT JOIN students ON students.student_id = student_unpaid_fees.student_id
        WHERE student_unpaid_fees.id = ? LIMIT 1
    ");
    $stmt->execute([$unpaid_fee_id]);
    $unpaid_fee_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($unpaid_fee_data)) {
        die('No unpaid fees found.');
    }
} else {
    die('Invalid request. No fee type provided.');
}

if (empty($unpaid_fee_data)) {
    die('<center><h1">No unpaid fees found.</h1></center>');
}

$amount = $unpaid_fee_data['unpaid_amount'];
$rzp_charge = $amount * $websiteConfig['razorpay_charge_percentage'] / 100;
$gst_charge = $rzp_charge * $websiteConfig['gst_on_razorpay_charge'] / 100;

$total_amount = $amount + $rzp_charge + $gst_charge;
?>

<style>
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .payment-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .school-header {
        text-align: center;
        margin-bottom: 2rem;
        animation: fadeInUp 0.6s ease-out;
    }

    .school-logo {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-bottom: 1rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        background: white;
        padding: 0.5rem;
    }

    .school-name {
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payment-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        animation: fadeInUp 0.8s ease-out 0.2s both;
        border: 1px solid var(--primary-light);
    }

    .payment-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        padding: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .payment-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: shimmer 3s ease-in-out infinite;
    }

    .payment-title {
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .payment-subtitle {
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .payment-body {
        padding: 2rem;
    }

    .plan-details {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, var(--light-color), #e9ecef);
        border-radius: 16px;
        border: 1px solid var(--primary-light);
    }

    .plan-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .plan-info h3 {
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--dark-color);
    }

    .plan-info p {
        color: var(--secondary-color);
        margin: 0;
        font-size: 0.9rem;
    }

    .month-display {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 2rem;
        text-align: center;
        padding: 1rem;
        background: var(--light-color);
        border-radius: 12px;
        border: 1px solid var(--primary-light);
    }

    .price-breakdown {
        margin-bottom: 2rem;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .price-row:last-child {
        border-bottom: none;
    }

    .price-label {
        display: flex;
        align-items: center;
        color: var(--secondary-color);
        font-size: 0.95rem;
    }

    .price-help {
        margin-left: 0.5rem;
        color: #94a3b8;
        cursor: help;
        transition: color 0.2s;
    }

    .price-help:hover {
        color: var(--primary-color);
    }

    .price-value {
        font-weight: 600;
        color: var(--dark-color);
    }

    .remark-value {
        font-weight: 500;
        color: var(--primary-color);
        font-size: 0.9rem;
        max-width: 200px;
        text-align: right;
        line-height: 1.3;
    }

    .total-row {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-radius: 16px;
        margin-top: 1rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .total-row .price-label,
    .total-row .price-value {
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .btn-pay {
        width: 100%;
        background: linear-gradient(135deg, #198754, #20c997);
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 1rem;
        border: none;
        border-radius: 16px;
        margin-top: 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 16px rgba(25, 135, 84, 0.3);
        position: relative;
        overflow: hidden;
    }

    .btn-pay::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(25, 135, 84, 0.4);
        background: linear-gradient(135deg, #157347, #1aa179);
    }

    .btn-pay:hover::before {
        left: 100%;
    }

    .btn-pay:active {
        transform: translateY(0);
    }

    .secure-payment {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1.5rem;
        color: var(--secondary-color);
        font-size: 0.9rem;
    }

    .secure-payment i {
        margin-right: 0.5rem;
        color: #198754;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer {

        0%,
        100% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        50% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 1rem 0;
        }

        .payment-header,
        .payment-body {
            padding: 1.5rem;
        }

        .school-logo {
            width: 60px;
            height: 60px;
        }

        .school-name {
            font-size: 1.3rem;
        }

        .plan-details {
            padding: 1rem;
        }

        .plan-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }
    }
</style>

<div class="payment-container">
    <div class="school-header">
        <img src="../uploads/school/logo-square.png" alt="Teghari Modern Scientific Islamic School Logo" class="school-logo">
        <h1 class="school-name"><?= $schoolInfo['name'] ?></h1>
    </div>

    <div class="payment-card">
        <div class="payment-header">
            <h1 class="payment-title">Confirm Your Payment</h1>
            <p class="payment-subtitle">Review your order details before payment</p>
        </div>

        <div class="payment-body">
            <div class="plan-details">
                <div class="plan-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="plan-info">
                    <h3><?= $unpaid_fee_data['name'] ?></h3>
                    <?php if ($fee_type == 'admission-fee'): ?>
                        <p>Admission Fee</p>
                    <?php else: ?>
                        <p>Monthly School Fee</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($fee_type == 'monthly-fee'): ?>
                <div class="month-display" id="monthDisplay"><?= $unpaid_fee_data['month_year'] ?></div>
            <?php else: ?>
                <p><?=date('M jS, Y', strtotime($unpaid_fee_data['admission_date']))?></p>
            <?php endif; ?>

            <div class="price-breakdown">
                <div class="price-row">
                    <div class="price-label">
                        <span>School Fee</span>
                        <i class="fas fa-info-circle price-help" title="Monthly tuition fee for your child"></i>
                    </div>
                    <div class="price-value">₹<?= $amount ?></div>
                </div>

                <div class="price-row">
                    <div class="price-label">
                        <span>Payment Gateway Fee (<?= $websiteConfig['razorpay_charge_percentage'] ?>%)</span>
                        <i class="fas fa-info-circle price-help" title="<?= $websiteConfig['razorpay_charge_percentage'] ?>% charge will be taken by payment gateway."></i>
                    </div>
                    <div class="price-value">₹<?= $rzp_charge ?></div>
                </div>

                <div class="price-row">
                    <div class="price-label">
                        <span>GST (<?= $websiteConfig['gst_on_razorpay_charge'] ?>%)</span>
                        <i class="fas fa-info-circle price-help" title="Goods and Services Tax"></i>
                    </div>
                    <div class="price-value">₹<?= $gst_charge ?></div>
                </div>

                <div class="price-row total-row">
                    <div class="price-label">Total Payable</div>
                    <div class="price-value">₹<?= $total_amount ?></div>
                </div>
            </div>

            <button class="btn-pay" id="payButton">
                <i class="fas fa-lock me-2"></i> Pay ₹<?= $total_amount ?>
            </button>

            <div class="secure-payment">
                <i class="fas fa-shield-alt"></i>
                <span>Secure payment encrypted with SSL</span>
            </div>

            <form id="payment-process-form" action="payment-process.php" method="POST">
                <input type="hidden" name="id" value="<?= $unpaid_fee_id?>">
                <input type="hidden" name="type" value="<?= $fee_type?>">
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#payButton').click(() => {
            $('#payment-process-form').submit();
        });
    });
</script>
<?php include_once('../includes/body-close.php'); ?>